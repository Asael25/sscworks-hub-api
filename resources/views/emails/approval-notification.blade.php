<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'pending' ? 'Permohonan Persetujuan Baru' : ($type === 'submitted' ? 'Bukti Pengajuan Permohonan' : 'Hasil Keputusan Approval') }} - SSCWorks Hub</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            background-color: #f1f5f9;
            padding: 40px 15px;
            box-sizing: border-box;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
            border: 1px solid #e2e8f0;
        }
        .header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
            padding: 32px 30px;
            text-align: center;
            color: #ffffff;
        }
        .badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 9999px;
            margin-bottom: 12px;
        }
        .badge-pending {
            background: rgba(245, 158, 11, 0.2);
            border: 1px solid rgba(245, 158, 11, 0.5);
            color: #fbbf24;
        }
        .badge-submitted {
            background: rgba(14, 165, 233, 0.2);
            border: 1px solid rgba(14, 165, 233, 0.5);
            color: #38bdf8;
        }
        .badge-approved {
            background: rgba(16, 185, 129, 0.2);
            border: 1px solid rgba(16, 185, 129, 0.5);
            color: #34d399;
        }
        .badge-rejected {
            background: rgba(239, 68, 68, 0.2);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #f87171;
        }
        .badge-returned {
            background: rgba(249, 115, 22, 0.2);
            border: 1px solid rgba(249, 115, 22, 0.5);
            color: #fb923c;
        }
        .header h1 {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #f8fafc;
        }
        .content {
            padding: 32px 30px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 10px;
        }
        .lead-text {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }
        .ticket-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .ticket-box-header {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
            margin-bottom: 14px;
        }
        .ticket-id {
            font-size: 12px;
            font-weight: 700;
            color: #0284c7;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .ticket-title {
            font-size: 17px;
            font-weight: 700;
            color: #0f172a;
            margin: 4px 0 0 0;
            line-height: 1.4;
        }
        .ticket-summary {
            font-size: 13px;
            color: #64748b;
            margin: 6px 0 0 0;
            line-height: 1.5;
        }
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        .grid-table td {
            padding: 7px 0;
            vertical-align: top;
        }
        .grid-table .label {
            width: 36%;
            color: #64748b;
            font-weight: 500;
        }
        .grid-table .value {
            color: #0f172a;
            font-weight: 600;
        }
        .decision-card {
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .decision-approved {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            border-left: 4px solid #10b981;
        }
        .decision-rejected {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-left: 4px solid #ef4444;
        }
        .decision-returned {
            background-color: #fff7ed;
            border: 1px solid #fed7aa;
            border-left: 4px solid #f97316;
        }
        .decision-pending {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-left: 4px solid #0284c7;
        }
        .decision-title {
            font-size: 13px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .decision-desc {
            font-size: 13px;
            color: #334155;
            line-height: 1.5;
            margin: 0;
        }
        .cta-container {
            text-align: center;
            margin: 32px 0 20px 0;
        }
        .btn-cta {
            display: inline-block;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 12px 28px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.25), 0 2px 4px -2px rgba(2, 132, 199, 0.25);
        }
        .footer {
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 24px 30px;
            text-align: center;
            font-size: 12px;
            color: #94a3b8;
            line-height: 1.5;
        }
        .priority-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 4px;
        }
        .priority-high {
            background: #fee2e2;
            color: #991b1b;
        }
        .priority-medium {
            background: #fef3c7;
            color: #92400e;
        }
        .priority-low {
            background: #e2e8f0;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                @if($type === 'pending')
                    <div class="badge badge-pending">Menunggu Persetujuan</div>
                    <h1>Tinjauan Persetujuan Diperlukan</h1>
                @elseif($type === 'submitted')
                    <div class="badge badge-submitted">Pengajuan Terkirim</div>
                    <h1>Bukti Pengajuan Permohonan</h1>
                @else
                    @php $act = strtolower($actionLabel ?? 'update'); @endphp
                    @if($act === 'approved')
                        <div class="badge badge-approved">Pengajuan Disetujui</div>
                        <h1>Pengajuan Anda Telah Disetujui</h1>
                    @elseif($act === 'rejected')
                        <div class="badge badge-rejected">Pengajuan Ditolak</div>
                        <h1>Pengajuan Tidak Disetujui</h1>
                    @elseif($act === 'returned')
                        <div class="badge badge-returned">Perlu Revisi</div>
                        <h1>Pengajuan Dikembalikan untuk Revisi</h1>
                    @else
                        <div class="badge badge-pending">Update Approval</div>
                        <h1>Pembaruan Status Pengajuan</h1>
                    @endif
                @endif
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">Halo, {{ $recipientName }}</div>
                
                @if($type === 'pending')
                    <div class="lead-text">
                        Terdapat tiket pengajuan baru di <strong>SSCWorks Hub — Approval Center</strong> yang memerlukan persetujuan dan verifikasi dari Anda sebagai <strong>{{ $stageName }}</strong>.
                    </div>
                @elseif($type === 'submitted')
                    <div class="lead-text">
                        Permohonan pengajuan Anda telah berhasil dicatat ke dalam sistem <strong>SSCWorks Hub</strong> dan saat ini sedang dalam proses peninjauan oleh <strong>{{ $stageName }}</strong>.
                    </div>
                @else
                    <div class="lead-text">
                        Status permohonan pengajuan Anda telah diperbarui oleh reviewer di <strong>SSCWorks Hub</strong>. Berikut ringkasan keputusannya:
                    </div>
                @endif

                <!-- Highlight Box Decision / Callout -->
                @if($type === 'decision')
                    @php $act = strtolower($actionLabel ?? 'update'); @endphp
                    @if($act === 'approved')
                        <div class="decision-card decision-approved">
                            <div class="decision-title" style="color: #065f46;">
                                ✓ Disetujui oleh {{ $actorName ?? 'Reviewer' }} ({{ $actorRole ?? 'Approver' }})
                            </div>
                            <p class="decision-desc">
                                {{ $decisionNote ? '"' . $decisionNote . '"' : 'Pengajuan telah diverifikasi dan disetujui sesuai prosedur yang berlaku.' }}
                            </p>
                        </div>
                    @elseif($act === 'rejected')
                        <div class="decision-card decision-rejected">
                            <div class="decision-title" style="color: #991b1b;">
                                ✕ Ditolak oleh {{ $actorName ?? 'Reviewer' }} ({{ $actorRole ?? 'Approver' }})
                            </div>
                            <p class="decision-desc">
                                <strong>Alasan Penolakan:</strong> {{ $decisionNote ? '"' . $decisionNote . '"' : 'Pengajuan ditolak oleh reviewer.' }}
                            </p>
                        </div>
                    @elseif($act === 'returned')
                        <div class="decision-card decision-returned">
                            <div class="decision-title" style="color: #9a3412;">
                                ↺ Dikembalikan untuk Revisi oleh {{ $actorName ?? 'Reviewer' }} ({{ $actorRole ?? 'Approver' }})
                            </div>
                            <p class="decision-desc">
                                <strong>Catatan Revisi:</strong> {{ $decisionNote ? '"' . $decisionNote . '"' : 'Mohon perbaiki dokumen atau data terkait sebelum diajukan kembali.' }}
                            </p>
                        </div>
                    @endif
                @elseif($type === 'submitted')
                    <div class="decision-card decision-submitted" style="background-color: #f0fdf4; border: 1px solid #bbf7d0; border-left: 4px solid #16a34a;">
                        <div class="decision-title" style="color: #15803d;">
                            ✓ Pengajuan Berhasil Didaftarkan
                        </div>
                        <p class="decision-desc">
                            Tiket Anda telah diteruskan ke reviewer tahap aktif: <strong>{{ $stageName }}</strong> (Tahap {{ $currentStage }} dari {{ $totalStages }}). Anda akan menerima notifikasi email otomatis kembali saat ada keputusan persetujuan.
                        </p>
                    </div>
                @else
                    <div class="decision-card decision-pending">
                        <div class="decision-title" style="color: #0369a1;">
                            ⏳ Menunggu Tindakan Anda
                        </div>
                        <p class="decision-desc">
                            Tahap aktif: <strong>{{ $stageName }}</strong> (Tahap {{ $currentStage }} dari {{ $totalStages }}). Mohon tinjau rincian dokumen pengajuan di bawah ini.
                        </p>
                    </div>
                @endif

                <!-- Ticket Information Box -->
                <div class="ticket-box">
                    <div class="ticket-box-header">
                        <div class="ticket-id">[{{ $ticketId }}] &bull; {{ $module }}</div>
                        <h2 class="ticket-title">{{ $title }}</h2>
                        @if(!empty($summary))
                            <p class="ticket-summary">{{ $summary }}</p>
                        @endif
                    </div>

                    <table class="grid-table">
                        <tr>
                            <td class="label">Pemohon (PIC):</td>
                            <td class="value">{{ $submitterName ?: 'User SSC' }}</td>
                        </tr>
                        <tr>
                            <td class="label">Unit Kerja / Cabang:</td>
                            <td class="value">{{ $unit }}</td>
                        </tr>
                        <tr>
                            <td class="label">Tanggal Pengajuan:</td>
                            <td class="value">{{ $submittedDate }}</td>
                        </tr>
                        @if($amountLabel && $amountLabel !== '-')
                        <tr>
                            <td class="label">Nilai / Nominal:</td>
                            <td class="value" style="color: #0284c7;">{{ $amountLabel }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">Tahap Approval:</td>
                            <td class="value">{{ $stageName }} (Tahap {{ $currentStage }}/{{ $totalStages }})</td>
                        </tr>
                        <tr>
                            <td class="label">Prioritas:</td>
                            <td class="value">
                                @php $pri = strtolower($priority); @endphp
                                @if($pri === 'high')
                                    <span class="priority-badge priority-high">High Priority</span>
                                @elseif($pri === 'medium')
                                    <span class="priority-badge priority-medium">Medium</span>
                                @else
                                    <span class="priority-badge priority-low">Normal</span>
                                @endif
                            </td>
                        </tr>
                        @if(!empty($dueDate))
                        <tr>
                            <td class="label">Tenggat Waktu:</td>
                            <td class="value" style="color: #b91c1c;">{{ $dueDate }}</td>
                        </tr>
                        @endif
                        <tr>
                            <td class="label">Status Saat Ini:</td>
                            <td class="value">
                                <strong>{{ $status }}</strong>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Call to Action Button -->
                <div class="cta-container">
                    <a href="{{ $actionUrl }}" class="btn-cta" target="_blank">
                        {{ $type === 'submitted' ? 'Pantau Status di Approval Center' : 'Buka Approval Center' }}
                    </a>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p style="margin: 0 0 6px 0;"><strong>SSCWorks Hub</strong> &bull; Enterprise Operations & Multi-Level Approval System</p>
                <p style="margin: 0;">Email ini dikirim secara otomatis oleh sistem. Harap tidak membalas email ini.</p>
            </div>
        </div>
    </div>
</body>
</html>
