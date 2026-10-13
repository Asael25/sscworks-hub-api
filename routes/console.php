<?php

use App\Models\ChatMessage;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('chat:purge-expired', function () {
    $ttlMinutes = (int) env('CHAT_RETENTION_MINUTES', 5);
    $cutoff = Carbon::now()->subMinutes($ttlMinutes);

    $deletedMsgCount = \App\Models\ChatMessage::where('sent_at', '<=', $cutoff)->delete();

    $this->info("Model 1: Berhasil membersihkan {$deletedMsgCount} pesan teks yang berusia lebih dari {$ttlMinutes} menit. Kontak & ruang percakapan tetap tersimpan.");
})->purpose('Model 1: Hapus pesan teks yang berusia lebih dari batas retensi menit');

Artisan::command('chat:clear-all', function () {
    \App\Models\ChatMessage::query()->delete();
    \App\Models\Conversation::query()->delete();
    try {
        \Illuminate\Support\Facades\Cache::put('ssc_chat_seeded_v1', true, now()->addYears(5));
    } catch (\Throwable $e) {}
    $this->info("Semua riwayat kontak percakapan dan pesan di database berhasil dikosongkan.");
})->purpose('Kosongkan semua kontak percakapan dan riwayat pesan di database');

// Auto-delete pesan dinonaktifkan: pesan tersimpan permanen
// Schedule::command('chat:purge-expired')->everyFiveMinutes();
