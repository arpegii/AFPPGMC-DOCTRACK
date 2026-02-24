<?php

namespace App\Notifications;

use App\Models\Document;
use App\Models\DocumentForwardHistory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentForwardedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $document;
    protected $forwardHistory;

    public function __construct(Document $document, DocumentForwardHistory $forwardHistory)
    {
        $this->document = $document;
        $this->forwardHistory = $forwardHistory;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Document Forwarded to Your Unit - ' . $this->document->document_number)
            ->view('emails.document-forwarded', [
                'document' => $this->document,
                'user' => $notifiable,
                'forwardHistory' => $this->forwardHistory,
            ]);
    }

    /**
     * Get the array representation for database storage.
     */
    public function toArray($notifiable)
    {
        return [
            'document_id' => $this->document->id,
            'document_number' => $this->document->document_number,
            'title' => $this->document->title,
            'type' => 'document_forwarded',
            'message' => 'Document forwarded from ' . $this->forwardHistory->fromUnit->name . ' to ' . $this->forwardHistory->toUnit->name,
            'from_unit' => $this->forwardHistory->fromUnit->name,
            'to_unit' => $this->forwardHistory->toUnit->name,
            'forwarded_by' => $this->forwardHistory->forwardedBy->name,
            'notes' => $this->forwardHistory->notes,
            'url' => route('documents.view', $this->document->id),
        ];
    }
}
