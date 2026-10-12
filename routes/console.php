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
    $cutoff = Carbon::now()->subMinutes(5);
    $conversations = \App\Models\Conversation::all();
    $deletedMsgCount = 0;

    foreach ($conversations as $conv) {
        $totalMessages = \App\Models\ChatMessage::where('conversation_id', $conv->id)->count();

        // Syarat: hanya hapus jika percakapan memiliki minimal 5 pesan.
        // Jika di bawah 5 pesan (< 5), pesan TIDAK dihapus.
        if ($totalMessages < 5) {
            continue;
        }

        $deleted = \App\Models\ChatMessage::where('conversation_id', $conv->id)
            ->where('sent_at', '<=', $cutoff)
            ->delete();

        $deletedMsgCount += $deleted;
    }

    $this->info("Berhasil membersihkan {$deletedMsgCount} pesan yang berusia lebih dari 5 menit (hanya pada percakapan dengan minimal 5 pesan). Kontak tetap tersimpan.");
})->purpose('Hapus pesan yang berusia lebih dari 5 menit jika percakapan memiliki minimal 5 pesan');

// Jalankan otomatis setiap 5 menit
Schedule::command('chat:purge-expired')->everyFiveMinutes();
