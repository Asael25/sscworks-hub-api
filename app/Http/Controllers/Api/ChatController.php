<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Conversation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $this->purgeExpiredConversations();
        $this->purgeExpiredMessages();

        $alreadySeeded = false;
        try {
            $alreadySeeded = \Illuminate\Support\Facades\Cache::has('ssc_chat_seeded_v1');
        } catch (\Throwable $e) {}

        if (Conversation::count() === 0 && !$alreadySeeded) {
            $this->seedInitialChat();
            try {
                \Illuminate\Support\Facades\Cache::put('ssc_chat_seeded_v1', true, now()->addDays(7));
            } catch (\Throwable $e) {}
        }

        $query = Conversation::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where('name', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%")
                  ->orWhere('unit', 'like', "%{$q}%");
        }

        $conversations = $query->orderBy('updated_at', 'desc')->get();
        $userId = $request->query('userId') ?: $request->header('X-User-Id');

        $result = $conversations->map(function ($c) use ($userId) {
            $lastMsg = ChatMessage::where('conversation_id', $c->id)
                                  ->where('deleted', false)
                                  ->orderBy('sent_at', 'desc')
                                  ->first();

            $data = $c->toFrontendSummary($userId);
            $data['lastMessage'] = $lastMsg ? $lastMsg->toFrontendFormat() : null;
            return $data;
        });

        return response()->json($result);
    }

    public function getUsers()
    {
        $users = \App\Models\User::all()->map(function ($u) {
            $parts = explode(' ', $u->name);
            $initials = (isset($parts[0][0]) ? $parts[0][0] : '') . (isset($parts[1][0]) ? $parts[1][0] : '');
            $uname = strtolower($u->username);
            $id = ($uname === 'sultan' || $uname === 'andi') ? 'u-andi' : 'u-' . $uname;
            return [
                'id' => $id,
                'name' => $u->name,
                'role' => $u->role,
                'unit' => $u->primary_unit ?: 'Head Office',
                'initials' => strtoupper($initials) ?: 'US',
                'online' => true,
            ];
        });

        return response()->json($users);
    }

    public function storeConversation(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string|in:direct,group',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit' => 'nullable|string',
            'ownerId' => 'nullable|string',
            'memberIds' => 'nullable|array',
            'relatedRequestIds' => 'nullable|array',
        ]);

        $members = $validated['memberIds'] ?? ['u-virga', 'u-andi'];

        // If direct conversation, check if already exists between the same 2 members
        if ($validated['type'] === 'direct' && count($members) >= 2) {
            $mSorted = $members;
            sort($mSorted);
            $existing = Conversation::where('type', 'direct')->get()->first(function ($c) use ($mSorted) {
                $cMembers = $c->member_ids ?? [];
                sort($cMembers);
                return $cMembers === $mSorted;
            });

            if ($existing) {
                return response()->json($existing->toFrontendSummary());
            }
        }

        $id = 'c-' . strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $validated['name'])) . '-' . rand(100, 999);

        $conversation = Conversation::create([
            'id' => $id,
            'type' => $validated['type'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'unit' => $validated['unit'] ?? 'Head Office',
            'owner_id' => $validated['ownerId'] ?? 'u-virga',
            'member_ids' => $members,
            'related_request_ids' => $validated['relatedRequestIds'] ?? [],
            'unread_count' => 0,
        ]);

        $summary = $conversation->toFrontendSummary();

        try {
            event(new \App\Events\ConversationUpdated($summary));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed in storeConversation: ' . $e->getMessage());
        }

        return response()->json($summary, 201);
    }

    public function getMessages(Request $request, $conversationId)
    {
        $this->purgeExpiredConversations();
        $this->purgeExpiredMessages();

        $messages = ChatMessage::where('conversation_id', $conversationId)
                                ->orderBy('sent_at', 'asc')
                                ->get();

        return response()->json($messages->map(fn($m) => $m->toFrontendFormat()));
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:10',
            'senderId' => 'nullable|string',
            'attachments' => 'nullable|array',
            'replyTo' => 'nullable|array',
        ], [
            'body.required' => 'Pesan teks wajib diisi.',
            'body.max' => 'Pesan chat maksimal 10 karakter.',
        ]);

        if (!empty($request->input('attachments'))) {
            return response()->json([
                'message' => 'Lampiran file dan gambar dilarang. Hanya pesan teks maksimal 10 karakter yang diperbolehkan.',
            ], 422);
        }

        $conversation = Conversation::find($conversationId);
        if (!$conversation) {
            // Auto create conversation if it doesn't exist
            $conversation = Conversation::create([
                'id' => $conversationId,
                'type' => 'group',
                'name' => 'Diskusi ' . $conversationId,
                'owner_id' => $validated['senderId'] ?? 'u-virga',
                'member_ids' => ['u-virga', 'u-andi'],
                'related_request_ids' => [],
                'unread_count' => 0,
            ]);
        }

        $msgId = 'MSG-' . time() . '-' . rand(100, 999);

        $message = ChatMessage::create([
            'id' => $msgId,
            'conversation_id' => $conversationId,
            'sender_id' => $validated['senderId'] ?? 'u-virga',
            'body' => $validated['body'],
            'attachments' => [],
            'reply_to' => $validated['replyTo'] ?? null,
            'status' => 'sent',
            'deleted' => false,
            'sent_at' => Carbon::now(),
        ]);

        $senderId = $validated['senderId'] ?? 'u-virga';

        // Increment unread count for recipients only (sender stays 0)
        $conversation->incrementUnreadForRecipients($senderId);
        $conversation->touch();

        $msgFormat = $message->toFrontendFormat();

        // Broadcast real-time message via Reverb WebSocket
        try {
            event(new \App\Events\MessageSent($msgFormat));
            event(new \App\Events\ConversationUpdated($conversation->toFrontendSummary()));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed in sendMessage: ' . $e->getMessage());
        }

        // Push notifikasi email dinonaktifkan sementara (dapat diaktifkan via CHAT_EMAIL_NOTIFICATIONS_ENABLED=true)
        if (env('CHAT_EMAIL_NOTIFICATIONS_ENABLED', false)) {
            $this->sendUnreadEmailNotification($conversation, $message, $senderId);
        }

        return response()->json($msgFormat, 201);
    }

    public function previewEmail(Request $request)
    {
        $recipientName = $request->query('recipient_name', 'Andi Sultan');
        $senderName = $request->query('sender_name', 'Virga Damayanti');
        $conversationName = $request->query('room_name', 'Legal — Contract Review');
        $messageBody = $request->query('body', 'Draft klausul pembayaran untuk PT Sinar Abadi sudah kami perbarui.');
        $sentAt = Carbon::now()->format('d M Y, H:i');
        $hasAttachments = $request->boolean('has_attachments', false);
        $attachmentCount = (int) $request->query('attachment_count', 1);
        $chatUrl = env('FRONTEND_URL', 'http://localhost:5173/chat');

        return view('emails.unread-chat-message', compact(
            'recipientName',
            'senderName',
            'conversationName',
            'messageBody',
            'sentAt',
            'hasAttachments',
            'attachmentCount',
            'chatUrl'
        ));
    }

    public function testEmail(Request $request)
    {
        if (!env('CHAT_EMAIL_NOTIFICATIONS_ENABLED', false)) {
            return response()->json([
                'success' => false,
                'message' => 'Push notifikasi ke email saat ini dinonaktifkan.',
            ], 403);
        }

        $validated = $request->validate([
            'email' => 'required|email',
            'recipient_name' => 'nullable|string',
            'sender_name' => 'nullable|string',
            'conversation_name' => 'nullable|string',
            'body' => 'nullable|string',
        ]);

        $recipientEmail = $validated['email'];
        $recipientName = $validated['recipient_name'] ?? 'Pengguna SSC';
        $senderName = $validated['sender_name'] ?? 'Virga Damayanti';
        $conversationName = $validated['conversation_name'] ?? 'Diskusi Pengadaan & Kontrak';
        $messageBody = $validated['body'] ?? 'Halo, ini adalah pesan uji coba notifikasi chat dari SSCWorks Hub.';
        $sentAt = Carbon::now()->format('d M Y, H:i');
        $chatUrl = env('FRONTEND_URL', 'http://localhost:5173/chat');

        try {
            \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(
                new \App\Mail\UnreadChatMessageMail(
                    recipientName: $recipientName,
                    senderName: $senderName,
                    conversationName: $conversationName,
                    messageBody: $messageBody,
                    sentAt: $sentAt,
                    hasAttachments: false,
                    attachmentCount: 0,
                    chatUrl: $chatUrl
                )
            );

            return response()->json([
                'success' => true,
                'message' => "Email notifikasi berhasil dikirim ke {$recipientEmail}!",
                'driver' => config('mail.default'),
                'details' => [
                    'recipient' => $recipientEmail,
                    'subject' => "[SSCWorks Hub] Pesan Baru dari {$senderName} ({$conversationName})",
                ]
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function findUserByChatId(string $chatId)
    {
        $clean = strtolower(str_replace('u-', '', $chatId));
        if ($clean === 'andi') {
            $clean = 'sultan';
        }

        return \App\Models\User::where('username', 'like', $clean)
            ->orWhere('identifier', 'like', "%{$clean}%")
            ->first();
    }

    private function sendUnreadEmailNotification($conversation, $message, $senderId)
    {
        // Jangan kirim email jika fitur notifikasi email dinonaktifkan
        if (!env('CHAT_EMAIL_NOTIFICATIONS_ENABLED', false)) {
            return;
        }

        try {
            $members = $conversation->member_ids ?? [];
            $senderUser = $this->findUserByChatId($senderId);
            $senderName = $senderUser ? $senderUser->name : 'Rekan SSC';

            foreach ($members as $memberId) {
                if ($memberId === $senderId) {
                    continue;
                }

                $recipient = $this->findUserByChatId($memberId);
                if (!$recipient || empty($recipient->identifier)) {
                    continue;
                }

                $recipientEmail = $recipient->identifier;
                $recipientName = $recipient->name ?: $recipient->username;

                $attachments = $message->attachments ?? [];
                $hasAttachments = !empty($attachments);
                $attachmentCount = is_array($attachments) ? count($attachments) : 0;

                \Illuminate\Support\Facades\Mail::to($recipientEmail)->send(
                    new \App\Mail\UnreadChatMessageMail(
                        recipientName: $recipientName,
                        senderName: $senderName,
                        conversationName: $conversation->name ?? 'Chat Room',
                        messageBody: $message->body,
                        sentAt: Carbon::parse($message->sent_at)->format('d M Y, H:i'),
                        hasAttachments: $hasAttachments,
                        attachmentCount: $attachmentCount,
                        chatUrl: env('FRONTEND_URL', 'http://localhost:5173/chat')
                    )
                );
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Email notification failed: ' . $e->getMessage());
        }
    }

    public function markRead(Request $request, $conversationId)
    {
        $conversation = Conversation::find($conversationId);
        $userId = $request->input('userId') ?: $request->query('userId') ?: $request->header('X-User-Id');
        if ($conversation) {
            if ($userId) {
                $conversation->markAsReadForUser($userId);
            } else {
                $conversation->update(['unread_count' => 0, 'unread_counts' => []]);
            }
            try {
                event(new \App\Events\ConversationUpdated($conversation->toFrontendSummary($userId)));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Broadcast failed in markRead: ' . $e->getMessage());
            }
        }

        return response()->json(['success' => true]);
    }

    public function deleteMessage($id)
    {
        $message = ChatMessage::find($id);
        if (!$message) {
            return response()->json(['message' => 'Message not found'], 404);
        }

        $message->update(['deleted' => true]);
        $msgFormat = $message->toFrontendFormat();

        try {
            event(new \App\Events\MessageDeleted($message->id, $message->conversation_id));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed in deleteMessage: ' . $e->getMessage());
        }

        return response()->json($msgFormat);
    }

    public function purgeExpiredConversations(): int
    {
        // Kontak/ruang percakapan dipertahankan (tidak dihapus).
        // Penghapusan hanya berlaku untuk isi pesan jika jumlah pesan >= 5.
        return 0;
    }

    public function purgeExpiredMessages(): int
    {
        try {
            $cutoff = Carbon::now()->subMinutes(5);
            $deletedTotal = 0;

            $conversations = Conversation::all();

            foreach ($conversations as $conv) {
                $totalCount = ChatMessage::where('conversation_id', $conv->id)->count();

                // Syarat: hanya hapus jika percakapan memiliki minimal 5 pesan.
                // Jika di bawah 5 pesan (< 5), pesan TIDAK dihapus.
                if ($totalCount < 5) {
                    continue;
                }

                $expiredMessages = ChatMessage::where('conversation_id', $conv->id)
                    ->where('sent_at', '<=', $cutoff)
                    ->get();

                if ($expiredMessages->isNotEmpty()) {
                    $count = ChatMessage::where('conversation_id', $conv->id)
                        ->where('sent_at', '<=', $cutoff)
                        ->delete();

                    $deletedTotal += $count;

                    try {
                        event(new \App\Events\ConversationUpdated($conv->toFrontendSummary()));
                    } catch (\Throwable $e) {
                        // Ignore broadcast failure
                    }
                }
            }

            return $deletedTotal;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Purge expired chat messages failed: ' . $e->getMessage());
            return 0;
        }
    }

    public function purgeExpired(Request $request)
    {
        $deletedMsgs = $this->purgeExpiredMessages();

        return response()->json([
            'success' => true,
            'deletedConversations' => 0,
            'deletedMessages' => $deletedMsgs,
            'ttlMinutes' => 5,
            'minMessageThreshold' => 5,
            'cutoff' => Carbon::now()->subMinutes(5)->toIso8601String(),
            'message' => "Pesan berusia lebih dari 5 menit pada percakapan dengan minimal 5 pesan berhasil dibersihkan ({$deletedMsgs} pesan). Kontak tetap tersimpan.",
        ]);
    }

    public function attachRequest(Request $request, $conversationId)
    {
        $request->validate([
            'requestId' => 'required|string',
        ]);

        $conversation = Conversation::findOrFail($conversationId);
        $current = $conversation->related_request_ids ?? [];
        if (!in_array($request->requestId, $current)) {
            $current[] = $request->requestId;
            $conversation->update(['related_request_ids' => $current]);
        }

        $summary = $conversation->toFrontendSummary();

        try {
            event(new \App\Events\ConversationUpdated($summary));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed in attachRequest: ' . $e->getMessage());
        }

        return response()->json($summary);
    }

    private function seedInitialChat()
    {
        $convs = [
            [
                'id' => 'c-legal-contract',
                'type' => 'group',
                'name' => 'Legal — Contract Review',
                'description' => 'Coordination room for contract drafting, review and signing.',
                'unit' => 'Head Office',
                'owner_id' => 'u-andi',
                'member_ids' => ['u-andi', 'u-virga', 'u-rudi', 'u-bintang'],
                'related_request_ids' => ['rr-1', 'rr-2'],
                'unread_count' => 0,
            ],
            [
                'id' => 'c-virga',
                'type' => 'direct',
                'name' => 'Virga Damayanti',
                'description' => null,
                'unit' => 'Head Office',
                'owner_id' => 'u-andi',
                'member_ids' => ['u-andi', 'u-virga'],
                'related_request_ids' => ['rr-1'],
                'unread_count' => 0,
            ],
            [
                'id' => 'c-budget',
                'type' => 'group',
                'name' => 'GA Budget — Palu',
                'description' => 'Budget request follow-up for facility repair works.',
                'unit' => 'Branch Palu',
                'owner_id' => 'u-eka',
                'member_ids' => ['u-andi', 'u-virga', 'u-eka'],
                'related_request_ids' => ['rr-5'],
                'unread_count' => 0,
            ],
        ];

        foreach ($convs as $c) {
            Conversation::create($c);
        }

        $msgs = [
            [
                'id' => 'MSG-0001',
                'conversation_id' => 'c-legal-contract',
                'sender_id' => 'u-virga',
                'sent_at' => Carbon::now()->subMinutes(3),
                'body' => 'Klausul OK',
                'attachments' => [],
                'status' => 'read',
            ],
            [
                'id' => 'MSG-0002',
                'conversation_id' => 'c-legal-contract',
                'sender_id' => 'u-andi',
                'sent_at' => Carbon::now()->subMinutes(1),
                'body' => 'Siap cek!',
                'attachments' => [],
                'status' => 'read',
            ],
            [
                'id' => 'MSG-0020',
                'conversation_id' => 'c-budget',
                'sender_id' => 'u-virga',
                'sent_at' => Carbon::now()->subMinutes(2),
                'body' => 'Budget OK',
                'attachments' => [],
                'status' => 'delivered',
            ],
        ];

        foreach ($msgs as $m) {
            ChatMessage::create($m);
        }
    }
}
