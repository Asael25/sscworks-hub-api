<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewAccountWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $username;
    public string $email;
    public string $plainPassword;
    public string $role;
    public string $unit;
    public string $loginUrl;
    public string $createdAt;

    public function __construct(
        string $recipientName,
        string $username,
        string $email,
        string $plainPassword,
        string $role = 'User',
        string $unit = 'Head Office',
        string $loginUrl = '',
        string $createdAt = ''
    ) {
        $this->recipientName = $recipientName;
        $this->username = $username;
        $this->email = $email;
        $this->plainPassword = $plainPassword;
        $this->role = $role;
        $this->unit = $unit;
        $this->loginUrl = $loginUrl ?: (env('FRONTEND_URL', 'https://tes.sscworks.com'));
        $this->createdAt = $createdAt ?: date('d M Y, H:i');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SSCWorks Hub] Akun Baru Anda Telah Dibuat oleh Administrator",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-account-welcome',
            with: [
                'recipientName' => $this->recipientName,
                'username' => $this->username,
                'email' => $this->email,
                'plainPassword' => $this->plainPassword,
                'role' => $this->role,
                'unit' => $this->unit,
                'loginUrl' => $this->loginUrl,
                'createdAt' => $this->createdAt,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
