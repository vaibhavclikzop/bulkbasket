<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $suppliers;
    public $order_type;
    public $emailTemplate;

    public function __construct($order, $suppliers, $order_type, $emailTemplate)
    {
        $this->order = $order;
        $this->suppliers = $suppliers;
        $this->order_type = $order_type;
        $this->emailTemplate = $emailTemplate;
    }

    public function build()
    {
        $subject = $this->emailTemplate->subject ?? 'Your Order Status Update';

        if ($this->emailTemplate && $this->emailTemplate->description) {
            $content = $this->emailTemplate->description;
            $placeholders = [
                '#id#',
                'id',
                '#order_status#',
                'order_status',
                '#order_type#',
                'order_type',
                '#total_amount#',
                'total_amount'
            ];
            $values = [
                $this->order->id,
                $this->order->id,
                $this->order->order_status,
                $this->order->order_status,
                $this->order_type,
                $this->order_type,
                $this->order->total_amount,
                $this->order->total_amount
            ];
            $content = str_replace($placeholders, $values, $content);
            return $this->subject($subject)
                ->view('emails.order-status-mail')
                ->with(['content' => $content]);
        }
        return $this->subject($subject)
            ->view('emails.order-status-mail')
            ->with([
                'order' => $this->order,
                'orderType' => $this->order_type,
            ]);
    }
}
