<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email? : Email tujuan pengujian}', function ($email = null) {
    $recipient = $email ?: 'saelelite@gmail.com';
    $this->info("=== DIAGNOSIS PENGIRIMAN EMAIL SSCWORKS HUB ===");
    $this->line("Mailer   : " . config('mail.default'));
    $this->line("Host     : " . config('mail.mailers.smtp.host') . ":" . config('mail.mailers.smtp.port'));
    $this->line("Username : " . config('mail.mailers.smtp.username'));
    $this->line("From     : " . config('mail.from.address') . " (" . config('mail.from.name') . ")");
    $this->line("Tujuan   : " . $recipient);
    $this->newLine();

    try {
        Illuminate\Support\Facades\Mail::raw("Halo!\n\nIni adalah email uji coba dari server SSCWorks Hub untuk memverifikasi bahwa konfigurasi SMTP Gmail berhasil dan aktif.", function ($message) use ($recipient) {
            $message->to($recipient)
                ->subject("[SSCWorks Hub] Tes Verifikasi Pengiriman Email Server");
        });
        $this->info("BERHASIL! Email uji coba berhasil dikirimkan ke: {$recipient}");
    } catch (\Throwable $e) {
        $this->error("GAGAL MENGIRIM EMAIL: " . $e->getMessage());
    }
})->purpose('Uji koneksi dan pengiriman email SMTP server');

