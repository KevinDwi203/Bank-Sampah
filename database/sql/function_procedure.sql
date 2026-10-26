-- =====================================================================
-- FUNCTION & STORED PROCEDURE - Bank Sampah
-- Cara pakai (phpMyAdmin / HeidiSQL / mysql CLI): pilih database, jalankan seluruh file ini.
-- Atau lewat Laravel:  php artisan migrate   (migration sudah disediakan)
-- =====================================================================

DROP FUNCTION  IF EXISTS fn_hitung_poin;
DROP FUNCTION  IF EXISTS fn_kode_berikutnya;
DROP FUNCTION  IF EXISTS fn_total_setoran_nasabah;
DROP PROCEDURE IF EXISTS sp_setor_sampah;
DROP PROCEDURE IF EXISTS sp_tarik_saldo;
DROP PROCEDURE IF EXISTS sp_laporan_bulanan;

DELIMITER $$

-- ---------------------------------------------------------------------
-- FUNCTION 1: hitung poin dari berat. Sama dengan floor($poin_kg * $berat) di TransaksiController
-- ---------------------------------------------------------------------
CREATE FUNCTION fn_hitung_poin(p_berat DECIMAL(10,3), p_poin_kg INT)
RETURNS INT
DETERMINISTIC
BEGIN
    RETURN FLOOR(p_poin_kg * p_berat);
END$$

-- ---------------------------------------------------------------------
-- FUNCTION 2: kode berikutnya: 'TR' -> TR003, 'WD' -> WD002, 'NK' -> NK.009
-- Memakai MAX nomor (bukan COUNT) sehingga tidak bentrok walau ada data yang dihapus.
-- ---------------------------------------------------------------------
CREATE FUNCTION fn_kode_berikutnya(p_prefix VARCHAR(2))
RETURNS VARCHAR(10)
READS SQL DATA
BEGIN
    DECLARE v_maks INT DEFAULT 0;

    IF p_prefix = 'TR' THEN
        SELECT COALESCE(MAX(CAST(SUBSTRING(kode, 3) AS UNSIGNED)), 0) INTO v_maks FROM transaksi WHERE kode LIKE 'TR%';
        RETURN CONCAT('TR', LPAD(v_maks + 1, 3, '0'));
    ELSEIF p_prefix = 'WD' THEN
        SELECT COALESCE(MAX(CAST(SUBSTRING(kode, 3) AS UNSIGNED)), 0) INTO v_maks FROM penarikan WHERE kode LIKE 'WD%';
        RETURN CONCAT('WD', LPAD(v_maks + 1, 3, '0'));
    ELSEIF p_prefix = 'NK' THEN
        SELECT COALESCE(MAX(CAST(SUBSTRING(kode, 4) AS UNSIGNED)), 0) INTO v_maks FROM nasabah WHERE kode LIKE 'NK.%';
        RETURN CONCAT('NK.', LPAD(v_maks + 1, 3, '0'));
    END IF;

    RETURN NULL;
END$$

-- ---------------------------------------------------------------------
-- FUNCTION 3: total rupiah setoran (status Selesai) milik satu nasabah. Dipakai di SELECT / laporan
-- ---------------------------------------------------------------------
CREATE FUNCTION fn_total_setoran_nasabah(p_nasabah_id BIGINT UNSIGNED)
RETURNS DECIMAL(15,2)
READS SQL DATA
BEGIN
    DECLARE v_total DECIMAL(15,2);
    SELECT COALESCE(SUM(total), 0) INTO v_total
    FROM transaksi
    WHERE nasabah_id = p_nasabah_id AND status = 'Selesai';
    RETURN v_total;
END$$

-- ---------------------------------------------------------------------
-- PROCEDURE 1: setor sampah (atomik: insert transaksi + tambah saldo + poin + tanggal setor terakhir)
-- Semua berhasil atau semua dibatalkan (ROLLBACK).
-- ---------------------------------------------------------------------
CREATE PROCEDURE sp_setor_sampah(
    IN  p_nasabah_id BIGINT UNSIGNED,
    IN  p_jenis_id   BIGINT UNSIGNED,
    IN  p_berat_kg   DECIMAL(10,3),
    IN  p_tanggal    DATE,
    OUT p_kode       VARCHAR(10)
)
BEGIN
    DECLARE v_harga_asli DECIMAL(12,2);
    DECLARE v_harga_warga DECIMAL(12,2);
    DECLARE v_poin_kg INT;
    DECLARE v_total DECIMAL(15,2);
    DECLARE v_poin INT;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    IF p_berat_kg IS NULL OR p_berat_kg <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Berat harus lebih dari 0';
    END IF;

    START TRANSACTION;

    -- kunci baris nasabah supaya dua setoran bersamaan tidak saling menimpa saldo
    IF NOT EXISTS (SELECT 1 FROM nasabah WHERE id = p_nasabah_id AND status = 'Aktif' FOR UPDATE) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nasabah tidak ditemukan atau tidak aktif';
    END IF;

    SELECT harga_asli_kg, harga_warga_kg, poin_kg
      INTO v_harga_asli, v_harga_warga, v_poin_kg
      FROM jenis_sampah WHERE id = p_jenis_id AND aktif = 1;

    IF v_harga_warga IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Jenis sampah tidak ditemukan atau tidak aktif';
    END IF;

    SET v_total = p_berat_kg * v_harga_warga;
    SET v_poin  = fn_hitung_poin(p_berat_kg, v_poin_kg);
    SET p_kode  = fn_kode_berikutnya('TR');

    INSERT INTO transaksi (kode, nasabah_id, jenis_id, berat_kg, harga_asli_kg, harga_warga_kg,
                           total, poin_dapat, status, tanggal, created_at, updated_at)
    VALUES (p_kode, p_nasabah_id, p_jenis_id, p_berat_kg, v_harga_asli, v_harga_warga,
            v_total, v_poin, 'Selesai', p_tanggal, NOW(), NOW());

    UPDATE nasabah
       SET saldo = saldo + v_total,
           poin = poin + v_poin,
           last_deposit_at = p_tanggal,
           updated_at = NOW()
     WHERE id = p_nasabah_id;

    COMMIT;
END$$

-- ---------------------------------------------------------------------
-- PROCEDURE 2: penarikan saldo (cek saldo + insert penarikan + kurangi saldo, atomik)
-- ---------------------------------------------------------------------
CREATE PROCEDURE sp_tarik_saldo(
    IN  p_nasabah_id BIGINT UNSIGNED,
    IN  p_jumlah     DECIMAL(15,2),
    IN  p_metode     VARCHAR(50),
    IN  p_tanggal    DATE,
    OUT p_kode       VARCHAR(10)
)
BEGIN
    DECLARE v_saldo DECIMAL(15,2);

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    IF p_jumlah IS NULL OR p_jumlah <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Jumlah penarikan harus lebih dari 0';
    END IF;

    START TRANSACTION;

    SELECT saldo INTO v_saldo FROM nasabah WHERE id = p_nasabah_id FOR UPDATE;

    IF v_saldo IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Nasabah tidak ditemukan';
    END IF;

    IF v_saldo < p_jumlah THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Saldo tidak mencukupi';
    END IF;

    SET p_kode = fn_kode_berikutnya('WD');

    INSERT INTO penarikan (kode, nasabah_id, jumlah, metode, status, tanggal, created_at, updated_at)
    VALUES (p_kode, p_nasabah_id, p_jumlah, p_metode, 'Selesai', p_tanggal, NOW(), NOW());

    UPDATE nasabah SET saldo = saldo - p_jumlah, updated_at = NOW() WHERE id = p_nasabah_id;

    COMMIT;
END$$

-- ---------------------------------------------------------------------
-- PROCEDURE 3: rekap bulanan per jenis sampah (hasilnya berupa tabel, untuk laporan)
-- ---------------------------------------------------------------------
CREATE PROCEDURE sp_laporan_bulanan(IN p_bulan INT, IN p_tahun INT)
BEGIN
    SELECT js.nama                        AS jenis_sampah,
           js.kategori                    AS kategori,
           COUNT(t.id)                    AS jumlah_transaksi,
           COALESCE(SUM(t.berat_kg), 0)   AS total_kg,
           COALESCE(SUM(t.total), 0)      AS total_nilai,
           COALESCE(SUM(t.poin_dapat), 0) AS total_poin
      FROM jenis_sampah js
      LEFT JOIN transaksi t
             ON t.jenis_id = js.id
            AND t.status = 'Selesai'
            AND MONTH(t.tanggal) = p_bulan
            AND YEAR(t.tanggal)  = p_tahun
     GROUP BY js.id, js.nama, js.kategori
     ORDER BY total_kg DESC;
END$$

DELIMITER ;
