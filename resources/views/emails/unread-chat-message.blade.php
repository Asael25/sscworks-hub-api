<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Baru di SSCWorks Hub</title>
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
            max-width: 580px;
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
            background: rgba(56, 189, 248, 0.15);
            border: 1px solid rgba(56, 189, 248, 0.35);
            color: #38bdf8;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 4px 12px;
            border-radius: 9999px;
            margin-bottom: 12px;
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
            margin-bottom: 12px;
        }
        .lead-text {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }
        .chat-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0284c7;
            border-radius: 10px;
            padding: 18px 20px;
            margin-bottom: 28px;
        }
        .chat-meta {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 13px;
        }
        .sender-badge {
            font-weight: 700;
            color: #0369a1;
            background: #e0f2fe;
            padding: 2px 8px;
            border-radius: 6px;
            display: inline-block;
        }
        .room-name {
            color: #64748b;
            font-size: 12px;
            margin-top: 4px;
        }
        .chat-body {
            font-size: 14px;
            line-height: 1.55;
            color: #1e293b;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px 14px;
            white-space: pre-wrap;
            word-break: break-word;
        }
        .attachment-tag {
            display: inline-block;
            margin-top: 8px;
            font-size: 12px;
            color: #0284c7;
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 6px;
            padding: 3px 8px;
        }
        .cta-container {
            text-align: center;
            margin: 32px 0 20px;
        }
        .cta-button {
            display: inline-block;
            background-color: #0284c7;
            color: #ffffff !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(2, 132, 199, 0.25);
            transition: background-color 0.2s;
        }
        .cta-button:hover {
            background-color: #0369a1;
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
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <!-- Header -->
            <div class="header">
                <div class="badge">SSCWorks Hub Collaboration</div>
                <h1>Pesan Masuk Belum Dibaca</h1>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">
                    Halo, {{ $recipientName }}! 👋
                </div>
                <div class="lead-text">
                    Anda menerima pesan baru di <strong>SSCWorks Hub</strong> yang belum Anda baca saat offline:
                </div>

                <!-- Chat Preview Card -->
                <div class="chat-card">
                    <div>
                        <span class="sender-badge">{{ $senderName }}</span>
                    </div>
                    <div class="room-name">
                        📍 Ruang: <strong>{{ $conversationName }}</strong> &bull; {{ $sentAt }}
                    </div>
                    <div class="chat-body" style="margin-top: 10px;">
                        {{ $messageBody }}
                    </div>

                    @if(!empty($hasAttachments))
                    <div class="attachment-tag">
                        📎 Menyertakan {{ $attachmentCount }} lampiran file
                    </div>
                    @endif
                </div>

                <!-- Action Button -->
                <div class="cta-container">
                    <a href="{{ $chatUrl }}" class="cta-button" target="_blank">
                        💬 Buka &amp; Balas Pesan
                    </a>
                </div>

                <div style="font-size: 12px; color: #94a3b8; text-align: center;">
                    Klik tombol di atas untuk langsung membuka ruang diskusi di browser Anda.
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p>Email otomatis ini dikirim oleh sistem notifikasi <strong>SSCWorks Hub</strong>.</p>
                <p>&copy; {{ date('Y') }} PT Sumber Samudera Cemerlang &bull; All Rights Reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
