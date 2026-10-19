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
        $userId = $request->query('userId') ?: $request->header('X-User-Id');

        $query = Conversation::query();

        // Filter percakapan hanya yang melibatkan user terkait (sebagai anggota atau pembuat)
        if ($userId) {
            $cleanUser = strtolower($userId);
            $altUser = str_starts_with($cleanUser, 'u-') ? substr($cleanUser, 2) : 'u-' . $cleanUser;
            $userVariants = array_values(array_unique([$cleanUser, $altUser, $userId]));

            $query->where(function ($q) use ($userVariants) {
                foreach ($userVariants as $uv) {
                    $q->orWhereJsonContains('member_ids', $uv)
                      ->orWhere('owner_id', $uv);
                }
            });
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%")
                  ->orWhere('unit', 'like', "%{$q}%");
            });
        }

        $conversations = $query->orderBy('updated_at', 'desc')->get();

        // Filter tambahan pada collection untuk memastikan isolasi antar pengguna 100% terjaga
        if ($userId) {
            $conversations = $conversations->filter(function ($c) use ($userId) {
                $members = is_array($c->member_ids) ? $c->member_ids : [];
                $owner = $c->owner_id;
                $cleanUser = strtolower($userId);
                $altUser = str_starts_with($cleanUser, 'u-') ? substr($cleanUser, 2) : 'u-' . $cleanUser;
                $allowed = array_unique([$cleanUser, $altUser, $userId]);

                foreach ($allowed as $a) {
                    if (in_array($a, $members) || $owner === $a) {
                        return true;
                    }
                }
                return false;
            })->values();
        }

        $threshold = Carbon::now()->subSeconds(\App\Models\User::ONLINE_THRESHOLD_SECONDS);
        $activeUsers = \App\Models\User::where('last_active_at', '>=', $threshold)->get();
        $activeChatIds = $activeUsers->map(fn($u) => $u->chat_user_id)->unique()->values()->toArray();

        $result = $conversations->map(function ($c) use ($userId, $activeChatIds) {
            $lastMsg = ChatMessage::where('conversation_id', $c->id)
                                  ->where('deleted', false)
                                  ->orderBy('sent_at', 'desc')
                                  ->first();

            $data = $c->toFrontendSummary($userId, $activeChatIds);

            if ($lastMsg && $lastMsg->status === 'sent' && !empty($data['online'])) {
                $lastMsg->update(['status' => 'delivered']);
                $lastMsg->status = 'delivered';
            }

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
            return [
                'id' => $u->chat_user_id,
                'name' => $u->name,
                'role' => $u->role,
                'unit' => $u->primary_unit ?: 'Head Office',
                'initials' => strtoupper($initials) ?: 'US',
                'online' => $u->is_online,
                'lastActiveAt' => $u->last_active_at ? $u->last_active_at->toISOString() : null,
            ];
        });

        return response()->json($users);
    }

    public function getChatRequests(Request $request)
    {
        $requests = [];

        // 1. Legal Requests (Telaah / Review Hukum)
        try {
            $legals = \App\Models\LegalRequest::orderBy('created_at', 'desc')->get();
            foreach ($legals as $lgl) {
                $status = $lgl->status ?: 'In Process';
                $formattedStatus = ucwords(str_replace('_', ' ', $status));
                $code = $lgl->request_code ?: "LGL-{$lgl->id}";

                $requests[] = [
                    'id' => (string)$code,
                    'code' => (string)$code,
                    'type' => 'Legal Operation',
                    'title' => (string)($lgl->title ?: 'Legal Request'),
                    'unit' => (string)($lgl->unit ?: 'Head Office'),
                    'status' => $formattedStatus,
                ];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error loading LegalRequests for chat: ' . $e->getMessage());
        }

        // 2. Customer Projects (Proyek Pelanggan)
        try {
            $projects = \App\Models\CustomerProject::orderBy('created_at', 'desc')->get();
            foreach ($projects as $cp) {
                $status = $cp->status ?: 'Active';
                $title = $cp->name . ($cp->customer ? " — {$cp->customer}" : '');

                $requests[] = [
                    'id' => (string)$cp->id,
                    'code' => (string)$cp->id,
                    'type' => 'Customer Project',
                    'title' => (string)$title,
                    'unit' => (string)($cp->unit ?: 'Head Office'),
                    'status' => (string)$status,
                ];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error loading CustomerProjects for chat: ' . $e->getMessage());
        }

        // 3. Internal Construction Works (Pekerjaan Konstruksi & SPK)
        try {
            $constructions = \App\Models\ConstructionWork::orderBy('created_at', 'desc')->get();
            foreach ($constructions as $cw) {
                $status = $cw->status ?: 'Active';
                $title = $cw->name . ($cw->vendor ? " — {$cw->vendor}" : '');
                $code = $cw->contract_number ?: $cw->id;

                $requests[] = [
                    'id' => (string)$cw->id,
                    'code' => (string)$code,
                    'type' => 'Internal Construction',
                    'title' => (string)$title,
                    'unit' => (string)($cw->unit ?: 'Head Office'),
                    'status' => (string)$status,
                ];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error loading ConstructionWorks for chat: ' . $e->getMessage());
        }

        // 4. Document Center Key Documents (SOP, Kebijakan, Sertifikasi, dll.)
        try {
            $docs = \App\Models\Document::whereNotNull('file_name')
                ->where(function ($q) {
                    $q->whereIn('category', ['General Documents', 'Vendor Documents', 'Contracts', 'Legal', 'Construction'])
                      ->orWhereNull('related_request')
                      ->orWhere('related_request', '-')
                      ->orWhere('related_request', '');
                })
                ->orderBy('uploaded_date', 'desc')
                ->limit(25)
                ->get();

            foreach ($docs as $doc) {
                $title = $doc->related_title ? "{$doc->related_title} ({$doc->file_name})" : $doc->file_name;
                $requests[] = [
                    'id' => (string)$doc->id,
                    'code' => (string)$doc->id,
                    'type' => 'Document Center',
                    'title' => (string)$title,
                    'unit' => (string)($doc->unit ?: 'All Units'),
                    'status' => (string)($doc->status ?: 'Active'),
                ];
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Error loading Documents for chat: ' . $e->getMessage());
        }

        // 5. Fallback item riwayat lampau (rr-1 s/d rr-6) agar percakapan lama tetap terbaca rapi
        $legacy = [
            ['id' => 'rr-1', 'code' => 'CTR-2026-0142', 'type' => 'Legal Operation', 'title' => 'Vendor service agreement — PT Sinar Abadi', 'unit' => 'Head Office', 'status' => 'In Process'],
            ['id' => 'rr-2', 'code' => 'CTR-2026-0155', 'type' => 'Legal Operation', 'title' => 'Office lease renewal — Surabaya', 'unit' => 'Branch Surabaya', 'status' => 'Waiting Review'],
            ['id' => 'rr-3', 'code' => 'CPJ-2026-0031', 'type' => 'Customer Project', 'title' => 'Warehouse automation rollout', 'unit' => 'Project Cikarang', 'status' => 'Active'],
            ['id' => 'rr-4', 'code' => 'CSH-2026-0908', 'type' => 'Cashflow / Finance', 'title' => 'Down payment disbursement Q3', 'unit' => 'Head Office', 'status' => 'Pending Approval'],
            ['id' => 'rr-5', 'code' => 'BGT-2026-0077', 'type' => 'Budget Request', 'title' => 'Additional GA budget — facility repair', 'unit' => 'Branch Medan', 'status' => 'Waiting Review'],
            ['id' => 'rr-6', 'code' => 'VND-2026-0210', 'type' => 'Vendor Management', 'title' => 'New vendor onboarding — CV Mitra Jaya', 'unit' => 'Branch Bandung', 'status' => 'Draft'],
        ];

        foreach ($legacy as $leg) {
            $requests[] = $leg;
        }

        return response()->json($requests);
    }

    public function getPresence(Request $request)
    {
        $threshold = Carbon::now()->subSeconds(\App\Models\User::ONLINE_THRESHOLD_SECONDS);
        $activeUsers = \App\Models\User::where('last_active_at', '>=', $threshold)->get();
        $activeChatIds = $activeUsers->map(fn($u) => $u->chat_user_id)->unique()->values()->toArray();

        return response()->json([
            'activeUserIds' => $activeChatIds,
            'timestamp' => Carbon::now()->toISOString(),
        ]);
    }

    public function heartbeat(Request $request)
    {
        $userId = $request->input('userId') 
            ?: $request->header('X-User-Id') 
            ?: $request->header('X-User-Identifier') 
            ?: $request->input('identifier');

        $user = null;
        if ($userId) {
            $user = \App\Models\User::findByChatUserId($userId);
            if ($user) {
                $user->touchActive();
            }
        }

        $threshold = Carbon::now()->subSeconds(\App\Models\User::ONLINE_THRESHOLD_SECONDS);
        $activeUsers = \App\Models\User::where('last_active_at', '>=', $threshold)->get();
        $activeChatIds = $activeUsers->map(fn($u) => $u->chat_user_id)->unique()->values()->toArray();

        return response()->json([
            'status' => 'ok',
            'userId' => $user ? $user->chat_user_id : $userId,
            'activeUserIds' => $activeChatIds,
            'timestamp' => Carbon::now()->toISOString(),
        ]);
    }

    public function leave(Request $request)
    {
        $userId = $request->input('userId') 
            ?: $request->header('X-User-Id') 
            ?: $request->header('X-User-Identifier') 
            ?: $request->input('identifier');

        if ($userId) {
            $user = \App\Models\User::findByChatUserId($userId);
            if ($user) {
                $user->update(['last_active_at' => Carbon::now()->subMinutes(10)]);
            }
        }

        return response()->json(['status' => 'ok']);
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
        $userId = $request->query('userId') ?: $request->header('X-User-Id');
        if ($userId) {
            $cleanUser = strtolower($userId);
            $altUser = str_starts_with($cleanUser, 'u-') ? substr($cleanUser, 2) : 'u-' . $cleanUser;
            $userVariants = array_values(array_unique([$cleanUser, $altUser, $userId]));

            // When user opens/fetches messages, mark messages from others as read
            $updatedCount = ChatMessage::where('conversation_id', $conversationId)
                ->whereNotIn('sender_id', $userVariants)
                ->where('status', '!=', 'read')
                ->update(['status' => 'read']);

            if ($updatedCount > 0) {
                $conversation = Conversation::find($conversationId);
                if ($conversation) {
                    $conversation->markAsReadForUser($userId);
                    try {
                        event(new \App\Events\ConversationUpdated($conversation->toFrontendSummary($userId)));
                    } catch (\Throwable $e) {
                        // ignore
                    }
                }
            }
        }

        // Auto-promote any remaining 'sent' messages to 'delivered' if the other party is online
        $threshold = Carbon::now()->subSeconds(\App\Models\User::ONLINE_THRESHOLD_SECONDS);
        $onlineChatIds = \App\Models\User::where('last_active_at', '>=', $threshold)
            ->get()
            ->map(fn($u) => $u->chat_user_id)
            ->toArray();

        if (!empty($onlineChatIds)) {
            $conversation = Conversation::find($conversationId);
            if ($conversation) {
                if ($conversation->type === 'direct') {
                    $members = $conversation->member_ids ?? [];
                    $otherMember = collect($members)->first(function($m) use ($userId) {
                        return strtolower($m) !== strtolower($userId ?? '');
                    });
                    if ($otherMember && in_array(strtolower($otherMember), array_map('strtolower', $onlineChatIds))) {
                        ChatMessage::where('conversation_id', $conversationId)
                            ->where('status', 'sent')
                            ->update(['status' => 'delivered']);
                    }
                } else {
                    ChatMessage::where('conversation_id', $conversationId)
                        ->where('status', 'sent')
                        ->update(['status' => 'delivered']);
                }
            }
        }

        $messages = ChatMessage::where('conversation_id', $conversationId)
                                ->orderBy('sent_at', 'asc')
                                ->get();

        return response()->json($messages->map(fn($m) => $m->toFrontendFormat()));
    }

    public function sendMessage(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:1000',
            'senderId' => 'nullable|string',
            'attachments' => 'nullable|array',
            'replyTo' => 'nullable|array',
        ], [
            'body.required' => 'Pesan teks wajib diisi.',
            'body.max' => 'Pesan chat maksimal 1000 karakter.',
        ]);

        if (!empty($request->input('attachments'))) {
            return response()->json([
                'message' => 'Chat difungsikan untuk obrolan teks saja. Lampiran file dinonaktifkan.',
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
        $senderId = $validated['senderId'] ?? 'u-virga';

        // Check if recipient is active on web to mark as delivered immediately
        $status = 'sent';
        $threshold = Carbon::now()->subSeconds(\App\Models\User::ONLINE_THRESHOLD_SECONDS);
        $onlineChatIds = \App\Models\User::where('last_active_at', '>=', $threshold)
            ->get()
            ->map(fn($u) => $u->chat_user_id)
            ->toArray();

        if ($conversation->type === 'direct') {
            $members = $conversation->member_ids ?? [];
            $cleanSender = strtolower($senderId);
            $recipientId = collect($members)->first(function($m) use ($cleanSender) {
                $cleanM = strtolower($m);
                return $cleanM !== $cleanSender && $cleanM !== str_replace('u-', '', $cleanSender);
            });
            if ($recipientId && in_array(strtolower($recipientId), array_map('strtolower', $onlineChatIds))) {
                $status = 'delivered';
            }
        } else {
            if (!empty($onlineChatIds)) {
                $status = 'delivered';
            }
        }

        $message = ChatMessage::create([
            'id' => $msgId,
            'conversation_id' => $conversationId,
            'sender_id' => $senderId,
            'body' => $validated['body'],
            'attachments' => [],
            'reply_to' => $validated['replyTo'] ?? null,
            'status' => $status,
            'deleted' => false,
            'sent_at' => Carbon::now(),
        ]);

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

        return response()->json($msgFormat, 201);
    }

    public function markRead(Request $request, $conversationId)
    {
        $conversation = Conversation::find($conversationId);
        $userId = $request->input('userId') ?: $request->query('userId') ?: $request->header('X-User-Id');
        if ($conversation) {
            if ($userId) {
                $conversation->markAsReadForUser($userId);

                $cleanUser = strtolower($userId);
                $altUser = str_starts_with($cleanUser, 'u-') ? substr($cleanUser, 2) : 'u-' . $cleanUser;
                $userVariants = array_values(array_unique([$cleanUser, $altUser, $userId]));

                // Update all messages from other users in this conversation to 'read'
                ChatMessage::where('conversation_id', $conversationId)
                    ->whereNotIn('sender_id', $userVariants)
                    ->where('status', '!=', 'read')
                    ->update(['status' => 'read']);
            } else {
                $conversation->update(['unread_count' => 0, 'unread_counts' => []]);
                ChatMessage::where('conversation_id', $conversationId)
                    ->where('status', '!=', 'read')
                    ->update(['status' => 'read']);
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
        // Model 1: Kontak dan ruang percakapan tetap tersimpan permanen.
        return 0;
    }

    public function purgeExpiredMessages(): int
    {
        // Fitur auto-delete dinonaktifkan: semua pesan teks tersimpan permanen
        return 0;
    }

    public function purgeExpired(Request $request)
    {
        return response()->json([
            'success' => true,
            'model' => 'Permanent Retention',
            'deletedConversations' => 0,
            'deletedMessages' => 0,
            'message' => 'Fitur auto-delete dinonaktifkan. Semua riwayat obrolan dan kontak tersimpan permanen.',
        ]);
    }

    public function clearAll(Request $request)
    {
        ChatMessage::query()->delete();
        Conversation::query()->delete();
        try {
            \Illuminate\Support\Facades\Cache::put('ssc_chat_seeded_v1', true, now()->addYears(5));
        } catch (\Throwable $e) {}

        return response()->json([
            'success' => true,
            'message' => 'Semua kontak percakapan dan riwayat pesan berhasil dikosongkan.',
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

    public function detachRequest(Request $request, $conversationId, $requestId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $current = $conversation->related_request_ids ?? [];

        $cleanTarget = strtolower($requestId);
        $updated = array_values(array_filter($current, function ($id) use ($cleanTarget) {
            return strtolower($id) !== $cleanTarget;
        }));

        $conversation->update(['related_request_ids' => $updated]);

        $summary = $conversation->toFrontendSummary();

        try {
            event(new \App\Events\ConversationUpdated($summary));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed in detachRequest: ' . $e->getMessage());
        }

        return response()->json($summary);
    }

    public function addMembers(Request $request, $conversationId)
    {
        $validated = $request->validate([
            'memberIds' => 'required|array|min:1',
            'memberIds.*' => 'required|string',
        ]);

        $conversation = Conversation::findOrFail($conversationId);

        if ($conversation->type !== 'group') {
            return response()->json(['message' => 'Hanya percakapan grup yang dapat menambahkan anggota baru.'], 422);
        }

        $actorId = $request->header('X-User-Id') ?: $request->input('actorId') ?: 'u-virga';
        $normActor = strtolower($actorId);
        $normActorKey = str_starts_with($normActor, 'u-') ? substr($normActor, 2) : $normActor;

        $normOwner = strtolower($conversation->owner_id ?: '');
        $normOwnerKey = str_starts_with($normOwner, 'u-') ? substr($normOwner, 2) : $normOwner;

        if ($normActorKey !== $normOwnerKey) {
            return response()->json(['message' => 'Hanya pemilik (owner) grup yang dapat menambahkan anggota baru.'], 403);
        }

        $currentMembers = is_array($conversation->member_ids) ? $conversation->member_ids : [];
        $newMemberIds = $validated['memberIds'];

        // Saring hanya anggota yang belum tergabung
        $trulyNew = [];
        $currentNormalized = array_map(function ($id) {
            $c = strtolower($id);
            return str_starts_with($c, 'u-') ? substr($c, 2) : $c;
        }, $currentMembers);

        foreach ($newMemberIds as $newId) {
            $norm = strtolower($newId);
            $normKey = str_starts_with($norm, 'u-') ? substr($norm, 2) : $norm;
            if (!in_array($normKey, $currentNormalized)) {
                $trulyNew[] = $newId;
            }
        }

        if (!empty($trulyNew)) {
            $updatedMembers = array_values(array_unique(array_merge($currentMembers, $trulyNew)));
            $conversation->update(['member_ids' => $updatedMembers]);

            // Kirim pesan sistem notifikasi penambahan anggota
            $actorUser = \App\Models\User::findByChatUserId($actorId);
            $actorName = $actorUser ? $actorUser->name : 'Seseorang';

            $addedNames = [];
            foreach ($trulyNew as $mId) {
                $u = \App\Models\User::findByChatUserId($mId);
                $addedNames[] = $u ? $u->name : $mId;
            }

            $namesString = count($addedNames) === 1
                ? $addedNames[0]
                : implode(', ', array_slice($addedNames, 0, -1)) . ' dan ' . end($addedNames);

            $systemMsg = ChatMessage::create([
                'id' => 'MSG-' . round(microtime(true) * 1000) . '-' . rand(100, 999),
                'conversation_id' => $conversationId,
                'sender_id' => 'system',
                'body' => "{$actorName} menambahkan {$namesString} ke dalam grup.",
                'attachments' => [],
                'status' => 'read',
                'deleted' => false,
                'sent_at' => Carbon::now(),
            ]);

            try {
                event(new \App\Events\MessageSent($systemMsg->toFrontendFormat()));
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Broadcast message failed in addMembers: ' . $e->getMessage());
            }
        }

        $conversation->touch();
        $summary = $conversation->toFrontendSummary();

        try {
            event(new \App\Events\ConversationUpdated($summary));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed in addMembers: ' . $e->getMessage());
        }

        return response()->json($summary);
    }

    public function removeMember(Request $request, $conversationId, $memberId)
    {
        $conversation = Conversation::findOrFail($conversationId);

        if ($conversation->type !== 'group') {
            return response()->json(['message' => 'Hanya percakapan grup yang dapat mengeluarkan anggota.'], 422);
        }

        $currentMembers = is_array($conversation->member_ids) ? $conversation->member_ids : [];
        $normTarget = strtolower($memberId);
        $normTargetKey = str_starts_with($normTarget, 'u-') ? substr($normTarget, 2) : $normTarget;

        $targetInGroup = collect($currentMembers)->first(function ($m) use ($normTargetKey) {
            $c = strtolower($m);
            $ck = str_starts_with($c, 'u-') ? substr($c, 2) : $c;
            return $ck === $normTargetKey;
        });

        if (!$targetInGroup) {
            return response()->json(['message' => 'Pengguna bukan anggota grup ini.'], 404);
        }

        $actorId = $request->header('X-User-Id') ?: $request->input('actorId') ?: 'u-virga';
        $normActor = strtolower($actorId);
        $normActorKey = str_starts_with($normActor, 'u-') ? substr($normActor, 2) : $normActor;

        $normOwner = strtolower($conversation->owner_id ?: '');
        $normOwnerKey = str_starts_with($normOwner, 'u-') ? substr($normOwner, 2) : $normOwner;

        $isLeavingSelf = ($normActorKey === $normTargetKey);

        // Jika bukan keluar sendiri, maka yang boleh mengeluarkan HANYA owner grup
        if (!$isLeavingSelf && $normActorKey !== $normOwnerKey) {
            return response()->json(['message' => 'Hanya pemilik (owner) grup yang dapat mengeluarkan anggota.'], 403);
        }

        // Jangan keluarkan owner jika masih ada anggota lain
        if ($conversation->owner_id === $targetInGroup && count($currentMembers) > 1) {
            return response()->json(['message' => 'Pemilik grup tidak dapat dikeluarkan. Anda dapat membubarkan grup jika ingin menghapusnya.'], 422);
        }

        $updatedMembers = array_values(array_filter($currentMembers, fn($m) => $m !== $targetInGroup));
        $conversation->update(['member_ids' => $updatedMembers]);

        $actorUser = \App\Models\User::findByChatUserId($actorId);
        $targetUser = \App\Models\User::findByChatUserId($targetInGroup);

        $actorName = $actorUser ? $actorUser->name : 'Seseorang';
        $targetName = $targetUser ? $targetUser->name : $targetInGroup;

        $systemText = ($isLeavingSelf)
            ? "{$targetName} keluar dari grup."
            : "{$actorName} mengeluarkan {$targetName} dari grup.";

        $systemMsg = ChatMessage::create([
            'id' => 'MSG-' . round(microtime(true) * 1000) . '-' . rand(100, 999),
            'conversation_id' => $conversationId,
            'sender_id' => 'system',
            'body' => $systemText,
            'attachments' => [],
            'status' => 'read',
            'deleted' => false,
            'sent_at' => Carbon::now(),
        ]);

        try {
            event(new \App\Events\MessageSent($systemMsg->toFrontendFormat()));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast message failed in removeMember: ' . $e->getMessage());
        }

        $conversation->touch();
        $summary = $conversation->toFrontendSummary();

        try {
            event(new \App\Events\ConversationUpdated($summary));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed in removeMember: ' . $e->getMessage());
        }

        return response()->json($summary);
    }

    public function destroyConversation(Request $request, $conversationId)
    {
        $conversation = Conversation::findOrFail($conversationId);
        $actorId = $request->header('X-User-Id') ?: $request->input('actorId') ?: 'u-virga';
        $normActor = strtolower($actorId);
        $normActorKey = str_starts_with($normActor, 'u-') ? substr($normActor, 2) : $normActor;

        $normOwner = strtolower($conversation->owner_id ?: '');
        $normOwnerKey = str_starts_with($normOwner, 'u-') ? substr($normOwner, 2) : $normOwner;

        if ($conversation->type === 'group' && $normActorKey !== $normOwnerKey) {
            return response()->json(['message' => 'Hanya pemilik (owner) grup yang dapat menghapus atau membubarkan grup.'], 403);
        }

        // Hapus semua pesan obrolan grup
        ChatMessage::where('conversation_id', $conversationId)->delete();
        $conversation->delete();

        try {
            event(new \App\Events\ConversationUpdated(['id' => $conversationId, 'deleted' => true]));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Broadcast failed in destroyConversation: ' . $e->getMessage());
        }

        return response()->json(['success' => true, 'message' => 'Grup berhasil dihapus.']);
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
