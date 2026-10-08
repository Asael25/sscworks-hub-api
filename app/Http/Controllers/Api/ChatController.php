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
        if (Conversation::count() === 0) {
            $this->seedInitialChat();
        }

        $query = Conversation::query();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where('name', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%")
                  ->orWhere('unit', 'like', "%{$q}%");
        }

        $conversations = $query->orderBy('updated_at', 'desc')->get();

        $result = $conversations->map(function ($c) {
            $lastMsg = ChatMessage::where('conversation_id', $c->id)
                                  ->where('deleted', false)
                                  ->orderBy('sent_at', 'desc')
                                  ->first();

            $data = $c->toFrontendSummary();
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
        $messages = ChatMessage::where('conversation_id', $conversationId)
                                ->orderBy('sent_at', 'asc')
                                ->get();

        return response()->json($messages->map(fn($m) => $m->toFrontendFormat()));
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'senderId' => 'nullable|string',
            'attachments' => 'nullable|array',
            'replyTo' => 'nullable|array',
        ]);

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
            'attachments' => $validated['attachments'] ?? [],
            'reply_to' => $validated['replyTo'] ?? null,
            'status' => 'sent',
            'deleted' => false,
            'sent_at' => Carbon::now(),
        ]);

        // Increment unread count and touch conversation
        $conversation->increment('unread_count');
        $conversation->touch();

        $msgFormat = $message->toFrontendFormat();

        // Broadcast real-time message via Reverb WebSocket
        try {
            event(new \App\Events\MessageSent($msgFormat));
            event(new \App\Events\ConversationUpdated($conversation->toFrontendSummary()));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed in sendMessage: ' . $e->getMessage());
        }

        return response()->json($msgFormat, 201);
    }

    public function markRead($conversationId)
    {
        $conversation = Conversation::find($conversationId);
        if ($conversation) {
            $conversation->update(['unread_count' => 0]);
            try {
                event(new \App\Events\ConversationUpdated($conversation->toFrontendSummary()));
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
                'sent_at' => Carbon::now()->subMinutes(60),
                'body' => 'Draft klausul pembayaran untuk PT Sinar Abadi sudah saya revisi di versi 3.',
                'attachments' => [
                    ['id' => 'a-1', 'name' => 'Draft-Agreement-v3.docx', 'size' => '412 KB', 'type' => 'DOCX']
                ],
                'status' => 'read',
            ],
            [
                'id' => 'MSG-0002',
                'conversation_id' => 'c-legal-contract',
                'sender_id' => 'u-andi',
                'sent_at' => Carbon::now()->subMinutes(55),
                'body' => 'Noted. Saya cek pasal denda keterlambatan dulu sebelum kirim ke unit.',
                'attachments' => [],
                'status' => 'read',
            ],
            [
                'id' => 'MSG-0003',
                'conversation_id' => 'c-legal-contract',
                'sender_id' => 'u-bintang',
                'sent_at' => Carbon::now()->subMinutes(40),
                'body' => 'Unit Palu butuh salinan final sebelum Jumat untuk kickoff internal.',
                'attachments' => [],
                'status' => 'delivered',
            ],
            [
                'id' => 'MSG-0004',
                'conversation_id' => 'c-legal-contract',
                'sender_id' => 'u-virga',
                'sent_at' => Carbon::now()->subMinutes(25),
                'body' => 'Lampiran perhitungan cashflow terkait termin pembayaran saya attach ya.',
                'attachments' => [
                    ['id' => 'a-2', 'name' => 'Termin-Cashflow.xlsx', 'size' => '88 KB', 'type' => 'XLSX'],
                    ['id' => 'a-3', 'name' => 'Rekap-Pembayaran.pdf', 'size' => '1.2 MB', 'type' => 'PDF'],
                ],
                'status' => 'delivered',
            ],
            [
                'id' => 'MSG-0020',
                'conversation_id' => 'c-budget',
                'sender_id' => 'u-virga',
                'sent_at' => Carbon::now()->subHours(5),
                'body' => 'Budget tambahan perbaikan fasilitas sudah masuk tahap review unit.',
                'attachments' => [],
                'status' => 'delivered',
            ],
            [
                'id' => 'MSG-0021',
                'conversation_id' => 'c-budget',
                'sender_id' => 'u-andi',
                'sent_at' => Carbon::now()->subHours(4),
                'body' => 'Bagus, segera informasikan jika butuh disposisi cepat dari HO.',
                'attachments' => [],
                'status' => 'read',
            ],
        ];

        foreach ($msgs as $m) {
            ChatMessage::create($m);
        }
    }
}
