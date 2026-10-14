<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Akun Baru Anda di SSCWorks Hub</title>
    <style type="text/css">
        /* Client-specific resets */
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; outline: none; text-decoration: none; }
        
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background-color: #f1f5f9;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
            -webkit-font-smoothing: antialiased;
        }

        /* Responsive styles */
        @media only screen and (max-width: 600px) {
            .wrapper {
                padding: 16px 8px !important;
            }
            .container {
                width: 100% !important;
                max-width: 100% !important;
                border-radius: 12px !important;
            }
            .header-padding {
                padding: 24px 18px !important;
            }
            .content-padding {
                padding: 22px 16px !important;
            }
            .footer-padding {
                padding: 18px 16px !important;
            }
            .header h1 {
                font-size: 19px !important;
                line-height: 1.3 !important;
            }
            .cred-box {
                padding: 14px 12px !important;
            }
            .cred-cell-label {
                width: 100% !important;
                display: block !important;
                padding-bottom: 2px !important;
            }
            .cred-cell-value {
                width: 100% !important;
                display: block !important;
                padding-bottom: 12px !important;
            }
            .btn-mobile {
                display: block !important;
                width: 100% !important;
                box-sizing: border-box !important;
                text-align: center !important;
                padding: 14px 18px !important;
            }
            .btn-table {
                width: 100% !important;
            }
        }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">

    <!-- Wrapper Table -->
    <table border="0" cellpadding="0" cellspacing="0" width="100%" class="wrapper" style="background-color: #f1f5f9; padding: 36px 12px;">
        <tr>
            <td align="center" valign="top">
                
                <!-- Main Container -->
                <table border="0" cellpadding="0" cellspacing="0" width="100%" class="container" style="max-width: 580px; background-color: #ffffff; border-radius: 16px; overflow: hidden; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);">
                    
                    <!-- Header -->
                    <tr>
                        <td align="center" class="header-padding" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #334155 100%); background-color: #0f172a; padding: 32px 24px; text-align: center; color: #ffffff;">
                            
                            <!-- Brand Badge -->
                            <table border="0" cellpadding="0" cellspacing="0" style="margin: 0 auto 12px auto;">
                                <tr>
                                    <td style="background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.35); padding: 4px 14px; border-radius: 9999px; text-align: center;">
                                        <span style="color: #38bdf8; font-size: 11px; font-weight: 700; letter-spacing: 1.2px; text-transform: uppercase;">PT SSC WORKS</span>
                                    </td>
                                </tr>
                            </table>

                            <h1 style="margin: 0 0 6px 0; font-size: 21px; font-weight: 700; color: #f8fafc; letter-spacing: -0.3px; line-height: 1.35;">
                                Selamat Datang di SSCWorks Hub
                            </h1>
                            <p style="margin: 0; font-size: 13px; color: #94a3b8; line-height: 1.4;">
                                Platform Terpadu Operasional &amp; Tata Kelola Perusahaan
                            </p>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td class="content-padding" style="padding: 28px 26px;">
                            
                            <!-- Greeting -->
                            <p style="margin: 0 0 8px 0; font-size: 16px; font-weight: 700; color: #0f172a;">
                                Halo, {{ $recipientName }}!
                            </p>
                            
                            <!-- Lead text -->
                            <p style="margin: 0 0 22px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                                Akun Anda telah berhasil dibuatkan oleh Administrator. Kini Anda dapat mengakses platform <strong>SSCWorks Hub</strong> untuk mengelola tugas dan operasional perusahaan.
                            </p>

                            <!-- Credentials Box -->
                            <div class="cred-box" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; margin-bottom: 22px;">
                                
                                <div style="font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #64748b; margin-bottom: 14px; border-bottom: 1px dashed #cbd5e1; padding-bottom: 8px;">
                                    Kredensial Akun Masuk Anda
                                </div>

                                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                                    
                                    <!-- Email -->
                                    <tr>
                                        <td valign="top" class="cred-cell-label" style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 500; width: 38%;">
                                            Email / Identifier:
                                        </td>
                                        <td valign="top" class="cred-cell-value" style="padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 600; word-break: break-all;">
                                            {{ $email }}
                                        </td>
                                    </tr>

                                    <!-- Username -->
                                    <tr>
                                        <td valign="top" class="cred-cell-label" style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 500; width: 38%;">
                                            Username:
                                        </td>
                                        <td valign="top" class="cred-cell-value" style="padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 600; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;">
                                            {{ $username }}
                                        </td>
                                    </tr>

                                    <!-- Password -->
                                    <tr>
                                        <td valign="top" class="cred-cell-label" style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 500; width: 38%;">
                                            Password Awal:
                                        </td>
                                        <td valign="top" class="cred-cell-value" style="padding: 6px 0;">
                                            <span style="display: inline-block; background-color: #e2e8f0; border: 1px solid #cbd5e1; padding: 3px 8px; border-radius: 6px; font-size: 13px; font-weight: 700; color: #0f172a; font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; word-break: break-all;">
                                                {{ $plainPassword }}
                                            </span>
                                        </td>
                                    </tr>

                                    <!-- Role -->
                                    <tr>
                                        <td valign="top" class="cred-cell-label" style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 500; width: 38%;">
                                            Role:
                                        </td>
                                        <td valign="top" class="cred-cell-value" style="padding: 6px 0;">
                                            <span style="display: inline-block; background-color: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: 12px; font-weight: 600; padding: 2px 8px; border-radius: 4px;">
                                                {{ $role }}
                                            </span>
                                        </td>
                                    </tr>

                                    <!-- Unit -->
                                    <tr>
                                        <td valign="top" class="cred-cell-label" style="padding: 6px 0; font-size: 13px; color: #64748b; font-weight: 500; width: 38%;">
                                            Unit Kerja:
                                        </td>
                                        <td valign="top" class="cred-cell-value" style="padding: 6px 0; font-size: 13px; color: #0f172a; font-weight: 600;">
                                            {{ $unit }}
                                        </td>
                                    </tr>

                                </table>
                            </div>

                            <!-- Notice Card: EULA -->
                            <div style="background-color: #fffbeb; border: 1px solid #fef3c7; border-left: 4px solid #f59e0b; border-radius: 8px; padding: 14px 16px; margin-bottom: 26px;">
                                <div style="font-size: 13px; font-weight: 700; color: #92400e; margin-bottom: 4px;">
                                    &#9888; Pemberitahuan EULA &amp; Kepatuhan
                                </div>
                                <p style="margin: 0; font-size: 12px; color: #b45309; line-height: 1.55;">
                                    Saat login pertama kali, sistem akan mewajibkan Anda untuk membaca dan menyetujui ketentuan <strong>EULA (End User License Agreement)</strong>. Seluruh akses operasional dipantau oleh Administrator untuk kepatuhan dan integritas perusahaan.
                                </p>
                            </div>

                            <!-- CTA Button -->
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" class="btn-table" style="margin: 0 0 10px 0;">
                                <tr>
                                    <td align="center">
                                        <table border="0" cellpadding="0" cellspacing="0" class="btn-table">
                                            <tr>
                                                <td align="center" style="border-radius: 10px; background: linear-gradient(135deg, #0284c7 0%, #2563eb 100%); background-color: #2563eb;">
                                                    <a href="{{ $loginUrl }}" target="_blank" class="btn-mobile" style="display: inline-block; padding: 13px 30px; font-size: 14px; font-weight: 600; color: #ffffff !important; text-decoration: none; border-radius: 10px; border: 1px solid #1d4ed8; -webkit-text-size-adjust: none;">
                                                        Buka Aplikasi &amp; Masuk Sekarang &rarr;
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td class="footer-padding" style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 24px; text-align: center;">
                            <p style="margin: 0 0 4px 0; font-size: 12px; color: #94a3b8; line-height: 1.5;">
                                Email ini dibuat secara otomatis oleh sistem SSCWorks Hub. Mohon tidak membalas email ini.
                            </p>
                            <p style="margin: 0; font-size: 11px; color: #cbd5e1;">
                                &copy; {{ date('Y') }} PT SSC WORKS. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
                <!-- End Main Container -->

            </td>
        </tr>
    </table>
    <!-- End Wrapper Table -->

</body>
</html>
