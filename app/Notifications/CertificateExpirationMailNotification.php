<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CertificateExpirationMailNotification extends Notification
{
    use Queueable;

    protected $certificates;
    protected $userEmail;

    /**
     * Create a new notification instance.
     */
    public function __construct($certificates, $userEmail)
    {
        $this->certificates = $certificates;
        $this->userEmail = $userEmail;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {

        $mailMessage= (new MailMessage)
            ->subject('Certificate Expiration Mail Notification')
            ->line('Hello!')
            ->line('The following certificates are about to expire:')
            ->line('Certificates:')
            ->line('Domain         Not Before         Not After            Sub Domain  ');

            foreach($this->certificates as $certificate){
                $mailMessage->line("{$certificate->domain}         {$certificate->not_before}         {$certificate->not_after}            {$certificate->common_name}  ");
            }
            $mailMessage->action('View It In Website:' , url('http://localhost:3000/'));
            $mailMessage->line('Thank you for using our application!');


        return $mailMessage;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    /**
     * Generate the HTML table for certificates.
     *
     * @return string
     */
    private function generateTable(): string
    {
        $tableHead = "
            <table style='width:100%; border-collapse: collapse;'>
                <thead>
                    <tr>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Domain</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Not Before</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Not After</th>
                        <th style='border: 1px solid #ddd; padding: 8px;'>Sub Domain</th>
                    </tr>
                </thead>
            ";

        $tableBody = "<tbody>";
        if ($this->certificates->isNotEmpty()) {
            foreach ($this->certificates as $certificate) {
                $tableBodyTr = "
                    <tr>
                        <td style='border: 1px solid #ddd; padding: 8px;'>{$certificate->domain}</td>
                        <td style='border: 1px solid #ddd; padding: 8px;'>{$certificate->not_before}</td>
                        <td style='border: 1px solid #ddd; padding: 8px;'>{$certificate->not_after}</td>
                        <td style='border: 1px solid #ddd; padding: 8px;'>{$certificate->common_name}</td>
                    </tr>
                ";
                $tableBody .= $tableBodyTr;
            }
        } else {
            // Add a message for the case when there are no certificates
            $tableBody .= "<tr><td colspan='4'>No certificates found</td></tr>";
        }

        $allTable = $tableHead . $tableBody . "
                </tbody>
            </table>
        ";

        return $allTable;
    }
}
