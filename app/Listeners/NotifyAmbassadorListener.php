<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\OrderCompletedEvent;
use Illuminate\Mail\Message;

class NotifyAmbassadorListener
{
    public function handle($event)
    {
        $order = $event->order;

        \Mail::send('admin', ['order' => $order], function(Message $message){
            $message->subject('An Order completed');
            $message->to('admin@admin.com');
        });
    }
}
