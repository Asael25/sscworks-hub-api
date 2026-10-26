<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Approval;
use App\Models\ApprovalStage;
use App\Models\ApprovalHistory;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    private function formatItem($item)
    {
        $notesData = [];
        if (!empty($item->notes) && str_starts_with(trim($item->notes), '{')) {
            $decoded = json_decode($item->notes, true);
            if (is_array($decoded)) {
                $notesData = $decoded;
            }
        }
        $isDelReq = !empty($notesData['isDeletionRequest']) || str_starts_with($item->request, 'Permohonan Hapus:');

        return [
            'id' => $item->id,
            'module' => $item->module,
            'request' => $item->request,
            'summary' => $item->summary,
            'unit' => $item->unit,
            'submittedBy' => $item->submitted_by,
            'submittedById' => $item->submitted_by_id,
            'submittedDate' => $item->submitted_date,
            'period' => $item->period,
            'amount' => $item->amount ? (float) $item->amount : null,
            'amountLabel' => $item->amount_label,
            'stages' => $item->stages->map(function ($stg) {
                return [
                    'id'           => $stg->id,
                    'order'        => $stg->order,
                    'stageLevel'   => (int) ($stg->stage_level ?? 1),
                    'stageName'    => $stg->stage_name ?? ('Tahap ' . ($stg->stage_level ?? 1)),
                    'isSequential' => (bool) $stg->is_sequential,
                    'subOrder'     => (int) ($stg->sub_order ?? 1),
                    'name'         => $stg->name,
                    'role'         => $stg->role,
                    'approver'     => $stg->approver,
                    'unitScope'    => $stg->unit_scope,
                    'status'       => $stg->status ?? 'Waiting',
                    'approvedBy'   => $stg->approved_by,
                    'approvedAt'   => $stg->approved_at,
                ];
            }),
            'currentStage' => $item->current_stage,
            'priority' => $item->priority,
            'status' => $item->status,
            'dueDate' => $item->due_date,
            'attachments' => $item->attachments->map(function ($att) {
                return [
                    'name' => $att->name,
                    'size' => $att->size,
                    'type' => $att->type,
                    'url' => $att->file_url ?? $att->file_path,
                ];
            }),
            'history' => $item->history->map(function ($hst) {
                return [
                    'actor' => $hst->actor,
                    'role' => $hst->role,
                    'action' => $hst->action,
                    'note' => $hst->note,
                    'at' => $hst->at,
                ];
            }),
            'notes' => $item->notes ?? '',
            'isDeletionRequest' => (bool) $isDelReq,
            'deletionReason' => $notesData['deletionReason'] ?? ($isDelReq ? $item->summary : null),
            'targetItemId' => $notesData['targetItemId'] ?? null,
            'originalStatus' => $notesData['originalStatus'] ?? null,
            'isContractRevision' => !empty($notesData['isContractRevision']) || str_starts_with($item->request, 'Revisi Kontrak:'),
            'contractRevision' => $notesData['contractRevision'] ?? null,
        ];
    }

    private function isSameUnit(?string $itemUnit, ?string $userUnit): bool
    {
        if (!$itemUnit || !$userUnit) return false;
        if (trim($itemUnit) === trim($userUnit)) return true;

        $cleanItem = strtolower(trim(str_replace(['SSC', 'Works', 'Cabang', '—', '-'], ' ', $itemUnit)));
        $cleanUser = strtolower(trim(str_replace(['SSC', 'Works', 'Cabang', '—', '-'], ' ', $userUnit)));

        $cleanItem = preg_replace('/\s+/', ' ', $cleanItem);
        $cleanUser = preg_replace('/\s+/', ' ', $cleanUser);

        if (!empty($cleanItem) && !empty($cleanUser)) {
            if ($cleanItem === $cleanUser) return true;
            if (str_contains($cleanItem, $cleanUser) || str_contains($cleanUser, $cleanItem)) return true;
        }

        return false;
    }

    public function index(Request $request)
    {
        $query = Approval::with(['stages', 'attachments', 'history'])
            ->orderBy('created_at', 'desc');

        $identifier = $request->header('X-User-Identifier');
        if ($identifier) {
            $user = \App\Models\User::whereRaw('LOWER(identifier) = ?', [strtolower($identifier)])
                ->orWhereRaw('LOWER(username) = ?', [strtolower($identifier)])
                ->first();
            if ($user) {
                $additional = is_array($user->additional_units)
                    ? $user->additional_units
                    : json_decode($user->additional_units ?? '[]', true);

                $isMultiUnitAdmin = in_array($user->role, ['Super Admin', 'Admin Super'])
                    || strtolower($user->identifier) === 'rudi@sscworks.com'
                    || strtolower($user->username ?? '') === 'rudi'
                    || (is_array($additional) && in_array('*', $additional));

                if (!$isMultiUnitAdmin) {
                    $allowedUnits = array_filter(array_merge([$user->primary_unit], $additional ?? []));
                    
                    $query->where(function ($q) use ($allowedUnits) {
                        foreach ($allowedUnits as $unit) {
                            if (!$unit || $unit === '*') continue;
                            $q->orWhere('unit', $unit);
                            
                            $dashHyphen = str_replace('—', '-', $unit);
                            $dashEm = str_replace('-', '—', $unit);
                            $q->orWhere('unit', $dashHyphen)
                              ->orWhere('unit', $dashEm);
                              
                            $clean = trim(str_replace(['SSC', 'Works', 'Cabang', '—', '-'], ' ', $unit));
                            if (!empty($clean)) {
                                $q->orWhere('unit', 'LIKE', '%' . $clean . '%');
                            }
                        }
                    });
                }
            }
        }

        $approvals = $query->get();

        // Format data sesuai ekspektasi TypeScript Interface Frontend (camelCase)
        $formatted = $approvals->map(fn($item) => $this->formatItem($item));

        return response()->json($formatted);
    }

    public function decide(Request $request, $id)
    {
        $approval = Approval::with(['stages', 'attachments', 'history'])->find($id);
        if (!$approval) {
            return response()->json(['message' => 'Pengajuan tidak ditemukan.'], 404);
        }

        $validated = $request->validate([
            'decision' => 'required|in:approve,return,reject',
            'reason' => 'nullable|string',
        ]);

        $decision = $validated['decision'];
        $reason = $validated['reason'] ?? null;

        $identifier = $request->header('X-User-Identifier');
        $user = null;
        if ($identifier) {
            $user = \App\Models\User::whereRaw('LOWER(identifier) = ?', [strtolower($identifier)])
                ->orWhereRaw('LOWER(username) = ?', [strtolower($identifier)])
                ->first();
        }

        $actorName = $user ? $user->name : ($request->input('actor') ?: 'User');
        $actorRole = $user ? $user->role : ($request->input('role') ?: 'Approver');

        // Check if already finalized
        if (in_array($approval->status, ['Approved', 'Rejected'])) {
            return response()->json(['message' => 'Pengajuan ini sudah difinalisasi.'], 422);
        }

        $stages = $approval->stages()->orderBy('order')->get();
        
        // Dapatkan stages di level/layer aktif
        $currentLayerStages = $stages->filter(function ($s) use ($approval) {
            $lvl = $s->stage_level ?: $s->order;
            return (int) $lvl === (int) $approval->current_stage;
        });

        if ($currentLayerStages->isEmpty()) {
            $currentLayerStages = $stages->where('order', $approval->current_stage);
        }
        if ($currentLayerStages->isEmpty()) {
            $currentLayerStages = collect([$stages->first()]);
        }

        $pendingInLayer = $currentLayerStages->filter(fn($s) => $s->status !== 'Approved');

        // Check approver assignment if not superadmin
        $isSuperAdmin = $user && in_array(strtolower(trim($user->role)), [
            'super admin',
            'admin super',
            'it & bussiness system support',
            'it & business system support',
        ]);

        $matchedStage = null;
        $isSequential = (bool) ($currentLayerStages->first()?->is_sequential ?? false);

        // Cocokkan user login dengan approver yang pending di layer ini sesuai alur workflow
        foreach ($pendingInLayer as $stg) {
            $isMatch = false;
            if ($stg->approver) {
                $targetApprover = strtolower(trim($stg->approver));
                $userName = strtolower(trim($user->name ?? ''));
                $userUsername = strtolower(trim($user->username ?? ''));
                $userIdentifier = strtolower(trim($user->identifier ?? ''));
                $userEmailPrefix = explode('@', $userIdentifier)[0];

                $nameWords = array_filter(preg_split('/\s+/', $userName));
                $targetWords = array_filter(preg_split('/\s+/', $targetApprover));
                $isWordMatch = false;
                foreach ($nameWords as $nw) {
                    if (in_array($nw, $targetWords)) {
                        $isWordMatch = true;
                        break;
                    }
                }

                $isMatch = ($userName === $targetApprover)
                    || str_contains($userName, $targetApprover)
                    || str_contains($targetApprover, $userName)
                    || $isWordMatch
                    || ($userUsername === $targetApprover)
                    || ($userIdentifier === $targetApprover)
                    || ($userEmailPrefix === $targetApprover);
            } elseif ($stg->role && $user) {
                $targetRole = strtolower(trim($stg->role));
                $userRole = strtolower(trim($user->role ?? ''));

                $isRoleMatch = ($targetRole === $userRole)
                    || ($targetRole === 'branch manager' && in_array($userRole, ['branch manager', 'kepala cabang', 'manajer proyek']))
                    || ($targetRole === 'general manager' && in_array($userRole, ['general manager', 'gm']));

                if ($isRoleMatch) {
                    $scope = $stg->unit_scope ?? 'All units';
                    $userPrimaryUnit = $user->primary_unit ?? '';

                    if ($scope === 'All units') {
                        $isMatch = true;
                    } elseif ($scope === 'Same unit') {
                        // Same unit WAJIB memverifikasi kesamaan unit pengajuan dengan primary_unit pejabat penanggung jawab
                        $isMatch = $this->isSameUnit($approval->unit, $userPrimaryUnit);
                    } elseif ($scope === 'Head Office' || $scope === 'Head Office only') {
                        $isMatch = str_contains(strtolower($userPrimaryUnit), 'head office');
                    } else {
                        $isMatch = true;
                    }
                }
            }

            if ($isMatch) {
                // Jika sequential, pastikan sub_order sebelumnya sudah disetujui
                if ($isSequential) {
                    $earlierPending = $pendingInLayer->first(fn($p) => (int)$p->sub_order < (int)$stg->sub_order);
                    if ($earlierPending) {
                        return response()->json([
                            'message' => 'Tahap ini bersifat sekuensial. Harap tunggu persetujuan dari ' . ($earlierPending->approver ?: $earlierPending->role) . ' terlebih dahulu.'
                        ], 422);
                    }
                }
                $matchedStage = $stg;
                break;
            }
        }

        if (!$matchedStage) {
            $expected = $pendingInLayer->first()?->approver ?: ($pendingInLayer->first()?->role ?: 'pejabat berwenang');
            return response()->json([
                'message' => "Tahap persetujuan ini memerlukan otorisasi [{$expected}] sesuai alur workflow dan tidak dapat diproses oleh akun Anda."
            ], 403);
        }


        // Jika fallback matchedStage masih kosong
        if (!$matchedStage) {
            $matchedStage = $pendingInLayer->first() ?? $currentLayerStages->first();
        }

        $actionLabel = 'Approved';

        $notesData = [];
        if (!empty($approval->notes) && str_starts_with(trim($approval->notes), '{')) {
            $notesData = json_decode($approval->notes, true) ?: [];
        }
        $isDeletion = !empty($notesData['isDeletionRequest']) || str_starts_with($approval->request, 'Permohonan Hapus:');

        if ($decision === 'approve') {
            $actionLabel = 'Approved';
            if ($isDeletion) {
                $targetId = $notesData['targetItemId'] ?? null;
                if ($targetId) {
                    try {
                        // Remove original approval ticket if exists so it disappears from all approvers' queues
                        \App\Models\Approval::where('id', '!=', $approval->id)
                            ->where(function ($q) use ($targetId) {
                                $q->where('id', $targetId)
                                    ->orWhere('request', 'LIKE', "%{$targetId}%")
                                    ->orWhere('summary', 'LIKE', "%{$targetId}%");
                            })
                            ->delete();
                    } catch (\Throwable $e) {
                        \Log::warning("Error deleting related approval: " . $e->getMessage());
                    }
                }

                try {
                    if (in_array($approval->module, ['Legal', 'Legal Operations'])) {
                        if ($targetId) {
                            \App\Models\LegalAttachment::where('request_id', $targetId)->delete();
                            \App\Models\LegalRequest::where('id', $targetId)->orWhere('request_code', $targetId)->delete();
                        }
                    } elseif (in_array($approval->module, ['Internal Construction', 'Konstruksi Internal', 'Construction'])) {
                        if ($targetId) {
                            \App\Models\ConstructionWeeklyProgress::where('report_id', $targetId)->orWhere('id', $targetId)->delete();
                            \App\Models\ConstructionWork::where('id', $targetId)->delete();
                        }
                    } elseif (in_array($approval->module, ['Project Customer', 'Customer Project'])) {
                        if ($targetId) {
                            \App\Models\CustomerProjectWeeklyProgress::where('project_id', $targetId)->delete();
                            \App\Models\CustomerProject::where('id', $targetId)->delete();
                            if (is_numeric($targetId)) {
                                \App\Models\CustomerProjectWeeklyProgress::where('id', $targetId)->delete();
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::error("Error executing deletion for module {$approval->module}: " . $e->getMessage());
                }
                $approval->status = 'Approved';
            } else {
                // Tandai approval stage spesifik ini sebagai Approved
                if ($matchedStage) {
                    $matchedStage->update([
                        'status'      => 'Approved',
                        'approved_by' => $actorName,
                        'approved_at' => date('d M Y, H:i'),
                    ]);
                }

                // Cek sisa approver di layer/level aktif ini
                $remainingInLayer = ApprovalStage::where('approval_id', $approval->id)
                    ->where(function ($q) use ($approval) {
                        $q->where('stage_level', $approval->current_stage)
                          ->orWhere(function ($q2) use ($approval) {
                              $q2->whereNull('stage_level')->where('order', $approval->current_stage);
                          });
                    })
                    ->where('status', '!=', 'Approved')
                    ->count();

                // Hitung layer/level berikutnya
                $distinctLevels = $stages->map(fn($s) => (int) ($s->stage_level ?: $s->order))->unique()->sort()->values();
                $currentLevelIndex = $distinctLevels->search((int)$approval->current_stage);
                $hasNextLevel = ($currentLevelIndex !== false && isset($distinctLevels[$currentLevelIndex + 1]));
                $nextLevel = $hasNextLevel ? $distinctLevels[$currentLevelIndex + 1] : null;

                if ($remainingInLayer > 0) {
                    // Masih ada approver lain di layer yang sama yang belum approve
                    $approval->status = 'In Review';
                    $approval->save();

                    ApprovalHistory::create([
                        'approval_id' => $approval->id,
                        'actor'       => $actorName,
                        'role'        => $actorRole,
                        'action'      => 'Approved (Layer in Progress)',
                        'note'        => "{$actorName} ({$actorRole}) menyetujui bagiannya pada {$matchedStage->name}. Menunggu {$remainingInLayer} approver lain di tahap ini sebelum melangkah ke tahap selanjutnya.",
                        'at'          => date('d M Y, H:i'),
                    ]);

                    $fresh = $approval->fresh(['stages', 'attachments', 'history']);
                    return response()->json([
                        'message'  => "Persetujuan Anda berhasil dicatat. Tiket masih menunggu {$remainingInLayer} persetujuan lainnya di tahap ini.",
                        'item'     => $this->formatItem($fresh),
                    ]);
                } elseif ($hasNextLevel) {
                    // Seluruh approver di layer ini sudah ACC! Lanjut ke layer berikutnya!
                    $approval->current_stage = $nextLevel;
                    $approval->status = 'In Review';
                    $approval->save();

                    ApprovalHistory::create([
                        'approval_id' => $approval->id,
                        'actor'       => $actorName,
                        'role'        => $actorRole,
                        'action'      => 'Layer Approved',
                        'note'        => "Seluruh persetujuan pada " . ($matchedStage->stage_name ?? ("Tahap " . $approval->current_stage)) . " telah lengkap. Tiket melangkah ke Tahap {$nextLevel}.",
                        'at'          => date('d M Y, H:i'),
                    ]);

                    \App\Services\ApprovalNotificationService::notifyPendingApprover($approval->id);

                    $fresh = $approval->fresh(['stages', 'attachments', 'history']);
                    return response()->json([
                        'message'  => "Seluruh persetujuan di tahap ini telah lengkap. Tiket otomatis melangkah ke tahap berikutnya.",
                        'item'     => $this->formatItem($fresh),
                    ]);
                } else {
                    // Ini adalah FINAL APPROVAL (semua layer selesai)!
                    $approval->status = 'Approved';
                    $approval->save();
                }
            }

                // Sync parent record
                if (in_array($approval->module, ['Project Customer', 'Customer Project'])) {
                    if (str_starts_with($approval->id, 'CSR-') || str_contains($approval->request, 'Site Report')) {
                        $projectName = trim(explode('·', $approval->request)[1] ?? '');
                        $project = \App\Models\CustomerProject::where('name', $projectName)->first();

                        $actual = 0;
                        $target = 0;
                        $period = date('Y-m-d');
                        $reportedBy = $approval->submitted_by;

                        if (preg_match('/Progres Realisasi (\d+(\.\d+)?)%/', $approval->summary, $mActual)) {
                            $actual = (float) $mActual[1];
                        }
                        if (preg_match('/Target (\d+(\.\d+)?)%/', $approval->summary, $mTarget)) {
                            $target = (float) $mTarget[1];
                        }
                        if (preg_match('/Laporan Mingguan \(([^)]+)\)/', $approval->summary, $mPeriod)) {
                            $period = $mPeriod[1];
                        }

                        if ($project) {
                            $project->update([
                                'actual' => $actual,
                                'status' => $actual >= 100 ? 'Completed' : 'On Schedule',
                            ]);

                            \App\Models\CustomerProjectWeeklyProgress::updateOrCreate(
                                [
                                    'project_id' => $project->id,
                                    'period'     => $period,
                                ],
                                [
                                    'target'      => $target,
                                    'actual'      => $actual,
                                    'note'        => $approval->notes ?? "Site report mingguan disetujui.",
                                    'reported_by' => $reportedBy,
                                ]
                            );
                        }
                    } elseif (!str_starts_with($approval->id, 'REV-')) {
                        \App\Models\CustomerProject::where('id', $approval->id)->update(['status' => 'On Schedule']);
                    }
                } elseif (in_array($approval->module, ['Internal Construction', 'Konstruksi Internal', 'Construction'])) {
                    if (str_starts_with($approval->id, 'WPR-')) {
                        $wp = \App\Models\ConstructionWeeklyProgress::where('report_id', $approval->id)->first();
                        if ($wp) {
                            $wp->update(['status' => 'Approved']);
                            $work = \App\Models\ConstructionWork::find($wp->work_id);
                            if ($work) {
                                $work->update([
                                    'actual' => $wp->actual,
                                    'period' => $wp->period,
                                    'status' => $wp->actual >= 100 ? 'Completed' : 'On Schedule',
                                ]);
                            }
                        } else {
                            \App\Models\ConstructionWeeklyProgress::where('report_id', $approval->id)->update(['status' => 'Approved']);
                        }
                    } elseif (!str_starts_with($approval->id, 'REV-')) {
                        \App\Models\ConstructionWork::where('id', $approval->id)->update(['status' => 'On Schedule']);
                    }
                } elseif (in_array($approval->module, ['Legal Operations', 'Legal'])) {
                    \App\Models\LegalRequest::where('request_code', $approval->id)->orWhere('id', $approval->id)->update(['status' => 'completed']);
                }

                // Handler jika approval adalah Revisi Kontrak / Addendum
                if (!empty($notesData['isContractRevision']) || str_starts_with($approval->request, 'Revisi Kontrak:')) {
                    $rev = $notesData['contractRevision'] ?? [];
                    $targetId = $rev['itemId'] ?? ($notesData['targetItemId'] ?? null);
                    $revAmount = isset($rev['revisedAmount']) ? (float)$rev['revisedAmount'] : (float)$approval->amount;
                    $revTargetDate = $rev['revisedTargetDate'] ?? null;
                    $itemType = $rev['itemType'] ?? '';

                    $addendum = \App\Models\ContractAddendum::where('approval_id', $approval->id)
                        ->orWhere(function ($q) use ($targetId) {
                            if ($targetId) {
                                $q->where('target_item_id', $targetId)->where('status', 'Waiting Approval');
                            }
                        })
                        ->first();
                    if ($addendum) {
                        $addendum->update([
                            'status' => 'Approved',
                            'approved_by' => $actorName,
                            'approved_at' => now()->translatedFormat('d M Y, H:i'),
                        ]);
                    }

                    if ($itemType === 'construction' || in_array($approval->module, ['Internal Construction', 'Konstruksi Internal', 'Construction'])) {
                        $work = \App\Models\ConstructionWork::find($targetId);
                        if ($work) {
                            $updatePayload = [];
                            if ($revAmount > 0) $updatePayload['budget'] = $revAmount;
                            if ($revTargetDate) $updatePayload['target_date'] = $revTargetDate;
                            $work->update($updatePayload);
                        }
                    } elseif ($itemType === 'customer-project' || in_array($approval->module, ['Customer Project', 'Project Customer'])) {
                        $project = \App\Models\CustomerProject::find($targetId);
                        if ($project) {
                            $updatePayload = [];
                            if ($revAmount > 0) $updatePayload['contract_value'] = $revAmount;
                            if ($revTargetDate) $updatePayload['deadline'] = $revTargetDate;
                            $project->update($updatePayload);
                        }
                    }
                }
        } elseif ($decision === 'return') {
            $actionLabel = 'Returned';
            $approval->status = 'Returned';
            if (str_starts_with($approval->id, 'WPR-')) {
                \App\Models\ConstructionWeeklyProgress::where('report_id', $approval->id)->update(['status' => 'Revision Required']);
            }
            if (!empty($notesData['isContractRevision']) || str_starts_with($approval->request, 'Revisi Kontrak:')) {
                $rev = $notesData['contractRevision'] ?? [];
                $targetId = $rev['itemId'] ?? ($notesData['targetItemId'] ?? null);
                $addendum = \App\Models\ContractAddendum::where('approval_id', $approval->id)
                    ->orWhere(function ($q) use ($targetId) {
                        if ($targetId) {
                            $q->where('target_item_id', $targetId)->where('status', 'Waiting Approval');
                        }
                    })
                    ->first();
                if ($addendum) {
                    $addendum->update([
                        'status' => 'Returned',
                        'rejection_reason' => $reason ?: 'Dikembalikan oleh Approver untuk perbaikan',
                    ]);
                }
            }
        } else {
            $actionLabel = 'Rejected';
            $approval->status = 'Rejected';
            if ($isDeletion) {
                $targetId = $notesData['targetItemId'] ?? null;
                $origStatus = $notesData['originalStatus'] ?? 'Approved';
                try {
                    if (in_array($approval->module, ['Legal', 'Legal Operations'])) {
                        if ($targetId) {
                            $validLegalStatus = in_array(strtolower($origStatus), ['draft', 'waiting_review', 'in_process', 'completed', 'rejected'])
                                ? strtolower($origStatus)
                                : 'waiting_review';
                            \App\Models\LegalRequest::where('id', $targetId)->orWhere('request_code', $targetId)->update(['status' => $validLegalStatus]);
                        }
                    } elseif (in_array($approval->module, ['Internal Construction', 'Konstruksi Internal', 'Construction'])) {
                        if ($targetId) {
                            \App\Models\ConstructionWeeklyProgress::where('report_id', $targetId)->orWhere('id', $targetId)->update(['status' => $origStatus]);
                            \App\Models\ConstructionWork::where('id', $targetId)->update(['status' => $origStatus]);
                        }
                    } elseif (in_array($approval->module, ['Project Customer', 'Customer Project'])) {
                        if ($targetId) {
                            \App\Models\CustomerProject::where('id', $targetId)->update(['status' => $origStatus]);
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::error("Error resetting status on deletion reject: " . $e->getMessage());
                }
            } elseif (str_starts_with($approval->id, 'WPR-')) {
                \App\Models\ConstructionWeeklyProgress::where('report_id', $approval->id)->update(['status' => 'Rejected']);
            }

            if (!empty($notesData['isContractRevision']) || str_starts_with($approval->request, 'Revisi Kontrak:')) {
                $rev = $notesData['contractRevision'] ?? [];
                $targetId = $rev['itemId'] ?? ($notesData['targetItemId'] ?? null);
                $addendum = \App\Models\ContractAddendum::where('approval_id', $approval->id)
                    ->orWhere(function ($q) use ($targetId) {
                        if ($targetId) {
                            $q->where('target_item_id', $targetId)->where('status', 'Waiting Approval');
                        }
                    })
                    ->first();
                if ($addendum) {
                    $addendum->update([
                        'status' => 'Rejected',
                        'rejection_reason' => $reason ?: 'Ditolak oleh Approver',
                    ]);
                }
            }
        }

        $approval->save();

        // Record history
        \App\Models\ApprovalHistory::create([
            'approval_id' => $approval->id,
            'actor' => $actorName,
            'role' => $actorRole,
            'action' => $actionLabel,
            'note' => $reason,
            'at' => now()->translatedFormat('d M Y, H:i'),
        ]);

        $fresh = $approval->fresh(['stages', 'attachments', 'history']);

        // Kirim email notifikasi ke pemohon (dan reviewer tahap berikutnya jika ada)
        \App\Services\ApprovalNotificationService::notifyDecision(
            $fresh,
            $decision,
            $reason,
            $actorName,
            $actorRole
        );

        return response()->json([
            'message' => "Pengajuan berhasil di-{$actionLabel}.",
            'item' => $this->formatItem($fresh)
        ]);
    }

    public function requestDeletion(Request $request)
    {
        $validated = $request->validate([
            'module' => 'required|string',
            'targetItemId' => 'required|string',
            'targetItemName' => 'required|string',
            'reason' => 'required|string|min:5',
            'unit' => 'nullable|string',
            'submittedBy' => 'nullable|string',
            'originalStatus' => 'nullable|string',
        ]);

        $delId = 'DEL-' . date('Ymd') . '-' . substr(strtoupper(uniqid()), -4);
        $unit = $validated['unit'] ?? 'Head Office';
        $pic = $validated['submittedBy'] ?? 'User';
        $picId = strtolower(str_replace(' ', '', $pic));

        $notesPayload = json_encode([
            'isDeletionRequest' => true,
            'deletionReason' => $validated['reason'],
            'targetItemId' => $validated['targetItemId'],
            'originalStatus' => $validated['originalStatus'] ?? 'Waiting Approval',
        ]);

        $approval = Approval::create([
            'id' => $delId,
            'module' => $validated['module'],
            'request' => 'Permohonan Hapus: ' . $validated['targetItemName'],
            'summary' => "Permohonan Hapus {$validated['targetItemId']}: {$validated['reason']}",
            'unit' => $unit,
            'submitted_by' => $pic,
            'submitted_by_id' => $picId,
            'submitted_date' => date('d M Y'),
            'period' => date('M Y'),
            'amount' => null,
            'amount_label' => '-',
            'current_stage' => 1,
            'priority' => 'High',
            'status' => 'Waiting',
            'due_date' => date('Y-m-d', strtotime('+3 days')),
            'notes' => $notesPayload,
        ]);

        \App\Models\ApprovalStage::create([
            'approval_id' => $delId,
            'order' => 1,
            'name' => 'Persetujuan Hapus Super Admin',
            'role' => 'Super Admin',
            'approver' => null,
            'unit_scope' => 'All units',
        ]);

        \App\Models\ApprovalHistory::create([
            'approval_id' => $delId,
            'actor' => $pic,
            'role' => 'Pemohon',
            'action' => 'Permohonan Hapus Diajukan',
            'note' => $validated['reason'],
            'at' => date('d M Y, H:i'),
        ]);

        // Kirim notifikasi email ke Super Admin untuk permohonan hapus
        \App\Services\ApprovalNotificationService::notifyPendingApprover($approval);
        \App\Services\ApprovalNotificationService::notifySubmissionReceipt($approval);

        return response()->json([
            'ok' => true,
            'id' => $delId,
            'message' => 'Permohonan penghapusan data telah dikirim ke Super Admin.',
        ], 201);
    }

    public function requestRevision(Request $request)
    {
        $validated = $request->validate([
            'module'             => 'required|string',
            'itemType'           => 'required|string|in:construction,customer-project',
            'itemId'             => 'required|string',
            'itemName'           => 'required|string',
            'unit'               => 'nullable|string',
            'submittedBy'        => 'nullable|string',
            'previousAmount'     => 'required|numeric',
            'revisedAmount'      => 'required|numeric',
            'previousTargetDate' => 'nullable|string',
            'revisedTargetDate'  => 'nullable|string',
            'reason'             => 'required|string|min:5',
            'documentName'       => 'nullable|string',
            'documentUrl'        => 'nullable|string',
        ]);

        if ($validated['itemType'] === 'construction') {
            $work = \App\Models\ConstructionWork::find($validated['itemId']);
            if ($work && !in_array($work->status, ['On Schedule', 'Delayed'])) {
                return response()->json([
                    'message' => 'Revisi kontrak hanya dapat diajukan untuk pekerjaan yang sedang berjalan (Status: On Schedule atau Delayed). Status saat ini: ' . $work->status,
                ], 422);
            }
        } elseif ($validated['itemType'] === 'customer-project') {
            $proj = \App\Models\CustomerProject::find($validated['itemId']);
            if ($proj && !in_array($proj->status, ['On Schedule', 'Delayed'])) {
                return response()->json([
                    'message' => 'Revisi kontrak hanya dapat diajukan untuk project customer yang sedang berjalan (Status: On Schedule atau Delayed). Status saat ini: ' . $proj->status,
                ], 422);
            }
        }

        $revId = 'REV-' . date('Ymd') . '-' . substr(strtoupper(uniqid()), -4);
        $addendumId = 'ADD-' . date('Ymd') . '-' . substr(strtoupper(uniqid()), -4);
        $unit = $validated['unit'] ?? 'Head Office';
        $pic = $validated['submittedBy'] ?? 'User';
        $picId = strtolower(str_replace(' ', '', $pic));

        $existingCount = \App\Models\ContractAddendum::where('target_item_id', $validated['itemId'])->count();
        $addendumNumber = $existingCount + 1;

        $notesPayload = json_encode([
            'isContractRevision' => true,
            'targetItemId'       => $validated['itemId'],
            'contractRevision'   => [
                'addendumId'         => $addendumId,
                'addendumNumber'     => $addendumNumber,
                'itemType'           => $validated['itemType'],
                'itemId'             => $validated['itemId'],
                'itemName'           => $validated['itemName'],
                'previousAmount'     => (float) $validated['previousAmount'],
                'revisedAmount'      => (float) $validated['revisedAmount'],
                'previousTargetDate' => $validated['previousTargetDate'] ?? '',
                'revisedTargetDate'  => $validated['revisedTargetDate'] ?? '',
                'reason'             => $validated['reason'],
                'documentName'       => $validated['documentName'] ?? null,
                'documentUrl'        => $validated['documentUrl'] ?? null,
            ],
        ]);

        $diff = (float)$validated['revisedAmount'] - (float)$validated['previousAmount'];
        $diffStr = ($diff >= 0 ? '+' : '-') . 'Rp ' . number_format(abs($diff), 0, ',', '.');
        $summary = "Addendum #{$addendumNumber}: Penyesuaian Nilai Rp " . number_format($validated['previousAmount'], 0, ',', '.') . " ➔ Rp " . number_format($validated['revisedAmount'], 0, ',', '.') . " ({$diffStr})" . ($validated['revisedTargetDate'] ? ", Target: {$validated['revisedTargetDate']}" : "");

        // Cek apakah dokumen induk memiliki alur persetujuan (stages) aktif
        $parentApproval = Approval::with('stages')->find($validated['itemId']);
        $moduleName = $parentApproval ? $parentApproval->module : $validated['module'];

        $approval = Approval::create([
            'id'              => $revId,
            'module'          => $moduleName,
            'request'         => 'Revisi Kontrak: ' . $validated['itemName'],
            'summary'         => $summary,
            'unit'            => $unit,
            'submitted_by'    => $pic,
            'submitted_by_id' => $picId,
            'submitted_date'  => date('d M Y'),
            'period'          => date('M Y'),
            'amount'          => (float) $validated['revisedAmount'],
            'amount_label'    => 'Rp ' . number_format($validated['revisedAmount'], 0, ',', '.'),
            'current_stage'   => 1,
            'priority'        => 'High',
            'status'          => 'Waiting',
            'due_date'        => date('Y-m-d', strtotime('+5 days')),
            'notes'           => $notesPayload,
        ]);

        if ($parentApproval && $parentApproval->stages->isNotEmpty()) {
            // Warisi tahapan approval yang sama persis dari dokumen induk
            foreach ($parentApproval->stages->sortBy('order') as $stg) {
                \App\Models\ApprovalStage::create([
                    'approval_id' => $revId,
                    'order'       => $stg->order,
                    'name'        => $stg->name,
                    'role'        => $stg->role,
                    'approver'    => $stg->approver,
                    'unit_scope'  => $stg->unit_scope,
                ]);
            }

            try {
                \App\Services\ApprovalNotificationService::notifyPendingApprover($revId);
                \App\Services\ApprovalNotificationService::notifySubmissionReceipt($revId);
            } catch (\Throwable $th) {
                \Log::warning("Notification email error: " . $th->getMessage());
            }
        } else {
            // Gunakan WorkflowEngine sesuai konfigurasi System Settings (WorkflowEngine sudah otomatis kirim notifikasi)
            \App\Services\WorkflowEngine::attachStagesToApproval(
                $revId,
                $moduleName,
                'Revisi Kontrak',
                (float) $validated['revisedAmount'],
                $unit
            );
        }

        $addendum = \App\Models\ContractAddendum::create([
            'addendum_id'          => $addendumId,
            'module'               => $validated['module'],
            'target_item_id'       => $validated['itemId'],
            'addendum_number'      => $addendumNumber,
            'approval_id'          => $revId,
            'previous_amount'      => (float) $validated['previousAmount'],
            'revised_amount'       => (float) $validated['revisedAmount'],
            'previous_target_date' => $validated['previousTargetDate'],
            'revised_target_date'  => $validated['revisedTargetDate'],
            'reason'               => $validated['reason'],
            'document_name'        => $validated['documentName'],
            'document_url'         => $validated['documentUrl'],
            'status'               => 'Waiting Approval',
            'submitted_by'         => $pic,
            'submitted_date'       => date('d M Y'),
        ]);

        if (!empty($validated['documentName'])) {
            \App\Models\ApprovalAttachment::create([
                'approval_id' => $revId,
                'name'        => $validated['documentName'],
                'size'        => 'Dokumen Addendum PDF',
                'type'        => 'application/pdf',
                'file_url'    => $validated['documentUrl'] ?? null,
                'file_path'   => $validated['documentUrl'] ?? null,
            ]);
        }

        \App\Models\ApprovalHistory::create([
            'approval_id' => $revId,
            'actor'       => $pic,
            'role'        => 'Pemohon',
            'action'      => 'Pengajuan Revisi Kontrak (Addendum) Diajukan',
            'note'        => $validated['reason'],
            'at'          => date('d M Y, H:i'),
        ]);


        return response()->json([
            'ok'       => true,
            'id'       => $revId,
            'addendum' => [
                'id'                 => $addendum->addendum_id,
                'addendumNumber'     => (int) $addendum->addendum_number,
                'submissionDate'     => $addendum->submitted_date,
                'effectiveDate'      => null,
                'previousBudget'     => (float) $addendum->previous_amount,
                'revisedBudget'      => (float) $addendum->revised_amount,
                'previousTargetDate' => $addendum->previous_target_date,
                'revisedTargetDate'  => $addendum->revised_target_date,
                'reason'             => $addendum->reason,
                'documentName'       => $addendum->document_name,
                'documentUrl'        => $addendum->document_url,
                'status'             => $addendum->status,
            ],
            'message'  => "Pengajuan revisi kontrak #{$addendumNumber} telah dikirim ke Approval Center.",
        ], 201);
    }

    public function destroy($id)
    {
        $approval = Approval::find($id);
        if (!$approval) {
            return response()->json(['message' => 'Approval tidak ditemukan'], 404);
        }

        // Hanya hapus data riwayat pengajuan/approval tiket saja
        // Progress bar resmi mingguan dan capaian persentase riil proyek/pekerjaan tetap utuh
        \App\Models\ApprovalStage::where('approval_id', $id)->delete();
        \App\Models\ApprovalAttachment::where('approval_id', $id)->delete();
        \App\Models\ApprovalHistory::where('approval_id', $id)->delete();
        $approval->delete();

        return response()->json([
            'ok' => true,
            'message' => "Card riwayat tiket {$id} berhasil dihapus permanen.",
        ]);
    }

    public function previewEmail(Request $request)
    {
        $type = $request->query('type', 'pending');
        $id = $request->query('id');
        $action = $request->query('action', 'approved');

        if ($id && ($item = Approval::with('stages')->find($id))) {
            $stages = $item->stages()->orderBy('order')->get();
            $current = $stages->firstWhere('order', $item->current_stage) ?? $stages->first();
            $stageName = $current ? ($current->name ?: $current->role) : 'Review Tahap Aktif';

            return view('emails.approval-notification', [
                'type' => $type,
                'recipientName' => $request->query('recipient_name', 'Andi Sultan'),
                'ticketId' => $item->id,
                'module' => $item->module,
                'title' => $item->request,
                'summary' => $item->summary ?? '',
                'unit' => $item->unit ?? 'Head Office',
                'submitterName' => $item->submitted_by ?? 'User SSC',
                'submittedDate' => $item->submitted_date ?? date('d M Y'),
                'stageName' => $stageName,
                'currentStage' => (int) ($item->current_stage ?: 1),
                'totalStages' => $stages->count() ?: 1,
                'priority' => $item->priority ?? 'Medium',
                'dueDate' => $item->due_date,
                'amountLabel' => $item->amount_label ?? '-',
                'status' => $item->status ?? 'Waiting',
                'actionLabel' => ucfirst($action),
                'decisionNote' => $request->query('note', 'Pengajuan telah diperiksa dan sesuai dengan ketentuan anggaran operasional.'),
                'actorName' => $request->query('actor', 'Fachrudi Putra (Super Admin)'),
                'actorRole' => 'Approver',
                'actionUrl' => env('FRONTEND_URL', 'http://localhost:5173') . '/approval-center',
            ]);
        }

        return view('emails.approval-notification', [
            'type' => $type,
            'recipientName' => $request->query('recipient_name', 'Andi Sultan'),
            'ticketId' => 'PRJ-2026-004',
            'module' => 'Project Customer',
            'title' => 'Pembangunan Fasilitas Distribusi Gudang Utama',
            'summary' => 'Pengajuan persetujuan kontrak proyek customer untuk pembangunan fasilitas gudang utama SSC Works.',
            'unit' => 'Cabang Surabaya',
            'submitterName' => 'Andi Sultan',
            'submittedDate' => date('d M Y'),
            'stageName' => 'Tahap 1: Review Manajer Proyek',
            'currentStage' => 1,
            'totalStages' => 3,
            'priority' => 'High',
            'dueDate' => date('Y-m-d', strtotime('+5 days')),
            'amountLabel' => 'Rp 850.000.000',
            'status' => $type === 'pending' ? 'Waiting' : ucfirst($action),
            'actionLabel' => ucfirst($action),
            'decisionNote' => $request->query('note', 'Dokumen spesifikasi teknis dan RAB telah diverifikasi lengkap.'),
            'actorName' => $request->query('actor', 'Virga Damayanti'),
            'actorRole' => 'Unit Head',
            'actionUrl' => env('FRONTEND_URL', 'http://localhost:5173') . '/approval-center',
        ]);
    }

    public function testEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'recipientName' => 'nullable|string',
            'type' => 'nullable|string|in:pending,decision,submitted',
            'action' => 'nullable|string|in:approved,rejected,returned',
            'ticketId' => 'nullable|string',
        ]);

        $email = $validated['email'];
        $recipientName = $validated['recipientName'] ?? 'Pengguna SSC';
        $type = $validated['type'] ?? 'pending';
        $action = $validated['action'] ?? 'approved';
        $ticketId = $validated['ticketId'] ?? null;

        $ticket = $ticketId ? Approval::with('stages')->find($ticketId) : null;

        if ($ticket) {
            $stages = $ticket->stages()->orderBy('order')->get();
            $current = $stages->firstWhere('order', $ticket->current_stage) ?? $stages->first();
            $stageName = $current ? ($current->name ?: $current->role) : 'Review Tahap Aktif';

            $mailable = new \App\Mail\ApprovalNotificationMail(
                type: $type,
                recipientName: $recipientName,
                ticketId: $ticket->id,
                module: $ticket->module,
                title: $ticket->request,
                summary: $ticket->summary ?? '',
                unit: $ticket->unit ?? 'Head Office',
                submitterName: $ticket->submitted_by ?? 'User SSC',
                submittedDate: $ticket->submitted_date ?? date('d M Y'),
                stageName: $stageName,
                currentStage: (int) ($ticket->current_stage ?: 1),
                totalStages: $stages->count() ?: 1,
                priority: $ticket->priority ?? 'Medium',
                dueDate: $ticket->due_date,
                amountLabel: $ticket->amount_label ?? '-',
                status: $ticket->status ?? 'Waiting',
                actionLabel: ucfirst($action),
                decisionNote: 'Ini adalah uji coba notifikasi approval resmi dari SSCWorks Hub.',
                actorName: 'Super Administrator',
                actorRole: 'Reviewer',
                actionUrl: env('FRONTEND_URL', 'http://localhost:5173') . '/approval-center'
            );
        } else {
            $mailable = new \App\Mail\ApprovalNotificationMail(
                type: $type,
                recipientName: $recipientName,
                ticketId: 'PRJ-2026-TEST',
                module: 'Project Customer',
                title: 'Tes Notifikasi Approval System',
                summary: 'Pengujian pengiriman email notifikasi approval center SSCWorks Hub.',
                unit: 'SSC Works — Head Office',
                submitterName: 'Andi Sultan',
                submittedDate: date('d M Y'),
                stageName: 'Tahap 1: Verifikasi Dokumen',
                currentStage: 1,
                totalStages: 3,
                priority: 'High',
                dueDate: date('Y-m-d', strtotime('+3 days')),
                amountLabel: 'Rp 450.000.000',
                status: $type === 'pending' ? 'Waiting' : ucfirst($action),
                actionLabel: ucfirst($action),
                decisionNote: 'Uji coba berhasil: format email dan koneksi SMTP approval berfungsi optimal.',
                actorName: 'Super Administrator',
                actorRole: 'Approver',
                actionUrl: env('FRONTEND_URL', 'http://localhost:5173') . '/approval-center'
            );
        }

        $hasSmtpCreds = !empty(env('MAIL_USERNAME')) && !empty(env('MAIL_PASSWORD'));

        try {
            if (!$hasSmtpCreds) {
                // Catat ke log mailer jika kredensial SMTP belum diisi di .env
                \Illuminate\Support\Facades\Mail::mailer('log')->to($email)->send($mailable);

                return response()->json([
                    'success' => true,
                    'isSimulation' => true,
                    'message' => "Email berhasil disimulasikan & dicatat di laravel.log (Kredensial Gmail di .env belum diisi).",
                    'driver' => 'log',
                    'details' => [
                        'recipient' => $email,
                        'type' => $type,
                        'subject' => $mailable->envelope()->subject,
                        'note' => 'Untuk pengiriman ke inbox Gmail sungguhan, masukkan MAIL_USERNAME dan MAIL_PASSWORD di file .env backend.',
                    ]
                ]);
            }

            \Illuminate\Support\Facades\Mail::to($email)->send($mailable);

            return response()->json([
                'success' => true,
                'isSimulation' => false,
                'message' => "Email notifikasi approval ({$type}) berhasil dikirim ke {$email}!",
                'driver' => config('mail.default'),
                'details' => [
                    'recipient' => $email,
                    'type' => $type,
                    'subject' => $mailable->envelope()->subject,
                ]
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Test approval email failed: ' . $e->getMessage());

            // Catat salinan email ke log mailer agar tidak hilang
            try {
                \Illuminate\Support\Facades\Mail::mailer('log')->to($email)->send($mailable);
            } catch (\Throwable $logEx) {}

            $reason = $e->getMessage();
            if (str_contains($reason, 'getaddrinfo') || str_contains($reason, 'Connection could not be established')) {
                $reason = "Koneksi SMTP ke smtp.gmail.com gagal. Periksa koneksi internet laptop Anda atau ubah MAIL_MAILER=log di file .env untuk mode simulasi lokal.";
            }

            return response()->json([
                'success' => false,
                'isSimulation' => true,
                'message' => "Gagal mengirim via SMTP Gmail: {$reason} (Salinan email telah dicatat di storage/logs/laravel.log).",
                'driver' => 'log',
            ]);
        }
    }
}
