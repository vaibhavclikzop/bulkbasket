<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderEstimateStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $order_type;
    public $emailTemplate;

    public function __construct($order, $order_type, $emailTemplate)
    {
        $this->order = $order;
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
                ->view('emails.order-estimate-mail-status')
                ->with(['content' => $content]);
        }
        return $this->subject($subject)
            ->view('emails.order-estimate-mail-status')
            ->with([
                'order' => $this->order,
                'orderType' => $this->order_type,
            ]);
    }
}
