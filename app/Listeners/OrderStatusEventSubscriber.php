<?php

namespace App\Listeners;

use App\Models\Order;
use Spatie\ModelStatus\Status;
use App\Traits\FirebaseMessagingTrait;


class OrderStatusEventSubscriber
{

    use FirebaseMessagingTrait;

    /** @var \Spatie\ModelStatus\Status|null */
    public $oldStatus;

    /** @var \Spatie\ModelStatus\Status */
    public $newStatus;

    /** @var \Illuminate\Database\Eloquent\Model */
    public $model;

    public function __construct(?Status $oldStatus, Status $newStatus, Order $model)
    {
        $this->oldStatus = $oldStatus;

        $this->newStatus = $newStatus;

        $this->model = $model;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function subscribe($events)
    {
        //
        $events->listen(
            'Spatie\ModelStatus\Events\StatusUpdated',
            [OrderStatusEventSubscriber::class, 'handleOrderUpdate']
        );
    }

    public function handleOrderUpdate($event)
    {
        //set the correct dateTime from carbon
        $oldStatus = $event->oldStatus;
        $newStatus = $event->newStatus;
        $oldStatusName = ($oldStatus != null ? $oldStatus->name : "");

        // logger("handleOrderUpdate", [
        //     "newStatus" => $newStatus,
        //     "oldStatus" => $oldStatus,
        //     "oldStatusName" => $oldStatusName,
        // ]);

        if ($oldStatusName != $newStatus->name ) {
            //
            $order = Order::find($event->model->id);
            $order->updated_at = \Carbon\Carbon::now();
            $order->save();
            //
            $this->sendOrderStatusChangeNotification($order);
        } 
        // else {
        //     logger("notification not called");
        // }
    }
}
