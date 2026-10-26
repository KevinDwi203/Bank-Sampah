<?php

if (!function_exists('formatRupiah')) {
    function formatRupiah($num): string
    {
        return 'Rp ' . number_format($num, 0, ',', '.');
    }
}

if (!function_exists('formatDate')) {
    function formatDate($dateStr): string
    {
        $d = new DateTime($dateStr);
        return $d->format('d F Y');
    }
}

if (!function_exists('formatShortDate')) {
    function formatShortDate($dateStr): string
    {
        $d = new DateTime($dateStr);
        return $d->format('d M Y');
    }
}