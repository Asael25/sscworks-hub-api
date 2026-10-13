<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApprovalNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $type; // 'pending' | 'decision'
    public string $recipientName;
    public string $ticketId;
    public string $module;
    public string $title;
    public string $summary;
    public string $unit;
    public string $submitterName;
    public string $submittedDate;
    public string $stageName;
    public int $currentStage;
    public int $totalStages;
    public string $priority;
    public ?string $dueDate;
    public ?string $amountLabel;
    public string $status;
    public ?string $actionLabel;
    public ?string $decisionNote;
    public ?string $actorName;
    public ?string $actorRole;
    public string $actionUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(
        string $type,
        string $recipientName,
        string $ticketId,
        string $module,
        string $title,
        string $summary = '',
        string $unit = 'Head Office',
        string $submitterName = '',
        string $submittedDate = '',
        string $stageName = 'Review Tahap Aktif',
        int $currentStage = 1,
        int $totalStages = 1,
        string $priority = 'Medium',
        ?string $dueDate = null,
        ?string $amountLabel = '-',
        string $status = 'Waiting',
        ?string $actionLabel = null,
        ?string $decisionNote = null,
        ?string $actorName = null,
        ?string $actorRole = null,
        ?string $actionUrl = null
    ) {
        $this->type = $type;
        $this->recipientName = $recipientName;
        $this->ticketId = $ticketId;
        $this->module = $module;
        $this->title = $title;
        $this->summary = $summary;
        $this->unit = $unit;
        $this->submitterName = $submitterName;
        $this->submittedDate = $submittedDate ?: date('d M Y');
        $this->stageName = $stageName;
        $this->currentStage = $currentStage;
        $this->totalStages = $totalStages;
        $this->priority = $priority;
        $this->dueDate = $dueDate;
        $this->amountLabel = $amountLabel ?: '-';
        $this->status = $status;
        $this->actionLabel = $actionLabel;
        $this->decisionNote = $decisionNote;
        $this->actorName = $actorName;
        $this->actorRole = $actorRole;
        $this->actionUrl = $actionUrl ?: (env('FRONTEND_URL', 'http://localhost:5173') . '/approval-center');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        if ($this->type === 'pending') {
            $subject = "[SSCWorks Hub] Permohonan Persetujuan Baru: {$this->title} ({$this->ticketId})";
        } elseif ($this->type === 'submitted') {
            $subject = "[SSCWorks Hub] Bukti Pengajuan Permohonan: {$this->title} ({$this->ticketId})";
        } else {
            $act = strtolower($this->actionLabel ?? 'update');
            if ($act === 'approved') {
                $subject = "[SSCWorks Hub] Pengajuan Disetujui: {$this->title} ({$this->ticketId})";
            } elseif ($act === 'rejected') {
                $subject = "[SSCWorks Hub] Pengajuan Ditolak: {$this->title} ({$this->ticketId})";
            } elseif ($act === 'returned') {
                $subject = "[SSCWorks Hub] Pengajuan Perlu Revisi: {$this->title} ({$this->ticketId})";
            } else {
                $subject = "[SSCWorks Hub] Update Approval: {$this->title} ({$this->ticketId})";
            }
        }

        return new Envelope(subject: $subject);
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.approval-notification',
            with: [
                'type' => $this->type,
                'recipientName' => $this->recipientName,
                'ticketId' => $this->ticketId,
                'module' => $this->module,
                'title' => $this->title,
                'summary' => $this->summary,
                'unit' => $this->unit,
                'submitterName' => $this->submitterName,
                'submittedDate' => $this->submittedDate,
                'stageName' => $this->stageName,
                'currentStage' => $this->currentStage,
                'totalStages' => $this->totalStages,
                'priority' => $this->priority,
                'dueDate' => $this->dueDate,
                'amountLabel' => $this->amountLabel,
                'status' => $this->status,
                'actionLabel' => $this->actionLabel,
                'decisionNote' => $this->decisionNote,
                'actorName' => $this->actorName,
                'actorRole' => $this->actorRole,
                'actionUrl' => $this->actionUrl,
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
