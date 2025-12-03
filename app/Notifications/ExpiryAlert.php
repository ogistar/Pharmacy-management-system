<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class ExpiryAlert extends Notification
{
    use Queueable;

    protected $purchase;

    public function __construct($purchase)
    {
        $this->purchase = $purchase;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'type' => 'expiry',
            'purchase_id' => $this->purchase->id,
            'product_name' => $this->purchase->product,
            'expiry_date' => $this->purchase->expiry_date,
            'quantity' => $this->purchase->quantity,
        ];
    }
}
