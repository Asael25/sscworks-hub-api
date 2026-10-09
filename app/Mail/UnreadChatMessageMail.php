<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UnreadChatMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $recipientName;
    public string $senderName;
    public string $conversationName;
    public string $messageBody;
    public string $sentAt;
    public bool $hasAttachments;
    public int $attachmentCount;
    public string $chatUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $recipientName,
        string $senderName,
        string $conversationName,
        string $messageBody,
        string $sentAt = '',
        bool $hasAttachments = false,
        int $attachmentCount = 0,
        string $chatUrl = ''
    ) {
        $this->recipientName = $recipientName;
        $this->senderName = $senderName;
        $this->conversationName = $conversationName;
        $this->messageBody = $messageBody;
        $this->sentAt = $sentAt ?: date('d M Y, H:i');
        $this->hasAttachments = $hasAttachments;
        $this->attachmentCount = $attachmentCount;
        $this->chatUrl = $chatUrl ?: (config('app.frontend_url') ?: 'http://localhost:5173/chat');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[SSCWorks Hub] Pesan Baru dari {$this->senderName} ({$this->conversationName})",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.unread-chat-message',
            with: [
                'recipientName' => $this->recipientName,
                'senderName' => $this->senderName,
                'conversationName' => $this->conversationName,
                'messageBody' => $this->messageBody,
                'sentAt' => $this->sentAt,
                'hasAttachments' => $this->hasAttachments,
                'attachmentCount' => $this->attachmentCount,
                'chatUrl' => $this->chatUrl,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
