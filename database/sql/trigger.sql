DELIMITER $$

CREATE TABLE IF NOT EXISTS log_aktivitas (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    tabel VARCHAR(30) NOT NULL,
    aksi VARCHAR(10) NOT NULL,
    kode_data VARCHAR(50) NULL,
    nasabah_id BIGINT UNSIGNED NULL,
    keterangan VARCHAR(255) NULL,
    waktu TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_log_waktu (waktu)
)$$

DROP TRIGGER IF EXISTS tr_transaksi_bi_validasi$$
CREATE TRIGGER tr_transaksi_bi_validasi BEFORE INSERT ON transaksi
FOR EACH ROW
BEGIN
    IF NEW.berat_kg < 0 OR NEW.total < 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Berat dan total transaksi tidak boleh negatif';
    END IF;
END$$

DROP TRIGGER IF EXISTS tr_transaksi_ai_log$$
CREATE TRIGGER tr_transaksi_ai_log AFTER INSERT ON transaksi
FOR EACH ROW
INSERT INTO log_aktivitas (tabel, aksi, kode_data, nasabah_id, keterangan)
VALUES ('transaksi', 'INSERT', NEW.kode, NEW.nasabah_id,
        CONCAT('Setoran ', NEW.berat_kg, ' kg, total Rp ', FORMAT(NEW.total, 0), ' (', NEW.status, ')'))$$

DROP TRIGGER IF EXISTS tr_transaksi_au_status$$
CREATE TRIGGER tr_transaksi_au_status AFTER UPDATE ON transaksi
FOR EACH ROW
BEGIN
    IF OLD.status <> NEW.status THEN
        INSERT INTO log_aktivitas (tabel, aksi, kode_data, nasabah_id, keterangan)
        VALUES ('transaksi', 'UPDATE', NEW.kode, NEW.nasabah_id,
                CONCAT('Status ', OLD.status, ' menjadi ', NEW.status));
    END IF;
END$$

DROP TRIGGER IF EXISTS tr_transaksi_ad_log$$
CREATE TRIGGER tr_transaksi_ad_log AFTER DELETE ON transaksi
FOR EACH ROW
INSERT INTO log_aktivitas (tabel, aksi, kode_data, nasabah_id, keterangan)
VALUES ('transaksi', 'DELETE', OLD.kode, OLD.nasabah_id,
        CONCAT('Transaksi dihapus: ', OLD.berat_kg, ' kg, total Rp ', FORMAT(OLD.total, 0)))$$

DELIMITER ;