<?php

namespace App\Notifications;

use App\Models\Cv;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AnalysisFailedNotification extends Notification
{
    use Queueable;

    public function __construct(public Cv $cv)
    {
        // On stocke le Cv concerné pour construire le message dans toMail()
    }

    /**
     * Canal utilisé : mail uniquement pour l'instant.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Échec de l\'analyse IA - ' . $this->cv->candidate_name)
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line("L'analyse du CV de {$this->cv->candidate_name} a échoué après plusieurs tentatives.")
            ->line('Offre concernée : ' . $this->cv->jobPosting->title)
            ->action('Voir le CV', url("/job-postings/{$this->cv->job_posting_id}/cvs/{$this->cv->id}"))
            ->line('Vous pouvez réessayer l\'analyse ou vérifier que le PDF est lisible.');
    }
}