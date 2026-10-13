<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Baru Anda di SSCWorks Hub</title>
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
        .header p {
            margin: 8px 0 0 0;
            font-size: 13px;
            color: #94a3b8;
        }
        .content {
            padding: 32px 30px;
        }
        .greeting {
            font-size: 16px;
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .lead-text {
            font-size: 14px;
            line-height: 1.6;
            color: #475569;
            margin-bottom: 24px;
        }
        .credential-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .cred-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin-bottom: 14px;
            border-bottom: 1px dashed #cbd5e1;
            padding-bottom: 6px;
        }
        .cred-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 13px;
        }
        .cred-label {
            color: #64748b;
            font-weight: 500;
        }
        .cred-val {
            color: #0f172a;
            font-weight: 600;
            font-family: monospace;
        }
        .notice-card {
            background: #fffbeb;
            border: 1px solid #fef3c7;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 14px 16px;
            margin-bottom: 28px;
        }
        .notice-title {
            font-size: 13px;
            font-weight: 700;
            color: #92400e;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .notice-desc {
            font-size: 12px;
            color: #b45309;
            line-height: 1.5;
            margin: 0;
        }
        .btn-wrapper {
            text-align: center;
            margin: 30px 0 10px;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%);
            color: #ffffff !important;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            padding: 12px 32px;
            border-radius: 10px;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }
        .footer {
            background-color: #f8fafc;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            font-size: 12px;
            color: #94a3b8;
        }
        .footer p {
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="badge">PT SINERGI SUKSES CONSULTINDO</div>
                <h1>Selamat Datang di SSCWorks Hub</h1>
                <p>Platform Terpadu Operasional & Tata Kelola Perusahaan</p>
            </div>

            <div class="content">
                <div class="greeting">Halo, {{ $recipientName }}!</div>
                <div class="lead-text">
                    Akun baru Anda telah dibuatkan oleh Super Administrator. Anda kini dapat mengakses platform <strong>SSCWorks Hub</strong> untuk mengelola pekerjaan dan operasional perusahaan.
                </div>

                <div class="credential-box">
                    <div class="cred-title">Informasi Kredensial Masuk</div>
                    <div class="cred-row">
                        <span class="cred-label">Email / Identifier:</span>
                        <span class="cred-val">{{ $email }}</span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-label">Username:</span>
                        <span class="cred-val">{{ $username }}</span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-label">Password Awal:</span>
                        <span class="cred-val" style="background:#e2e8f0; padding:2px 6px; border-radius:4px; color:#1e293b;">{{ $plainPassword }}</span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-label">Role:</span>
                        <span class="cred-val" style="color:#0284c7;">{{ $role }}</span>
                    </div>
                    <div class="cred-row">
                        <span class="cred-label">Unit Kerja:</span>
                        <span class="cred-val">{{ $unit }}</span>
                    </div>
                </div>

                <div class="notice-card">
                    <div class="notice-title">
                        <span>Pemberitahuan Persetujuan EULA & Privasi</span>
                    </div>
                    <p class="notice-desc">
                        Saat pertama kali login ke dalam sistem, Anda akan diminta untuk membaca seluruh ketentuan <strong>EULA (End User License Agreement)</strong> dan menyetujui bahwa seluruh aktivitas dan data operasional pada platform ini dipantau oleh Administrator untuk kepentingan kepatuhan.
                    </p>
                </div>

                <div class="btn-wrapper">
                    <a href="{{ $loginUrl }}" class="btn" target="_blank">
                        Buka Aplikasi & Masuk Sekarang
                    </a>
                </div>
            </div>

            <div class="footer">
                <p>Email ini dikirim secara otomatis oleh sistem SSCWorks Hub.</p>
                <p>&copy; {{ date('Y') }} PT Sinergi Sukses Consultindo. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
