<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Carbon\Carbon;

class AutoCancelTaxiOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'taxi:cancel';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cancel pending taxi order when the time is right';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //
        $cancelTime = setting('taxi.cancelPendingTaxiOrderTime', 2);
        $expireDateTime = \Carbon\Carbon::now()->timezone(setting('timeZone', 'UTC'))->subMinutes($cancelTime)->format('Y-m-d h:i:s');
        //get orders pending for more the ``autoCancelPendingOrderTime``
        $orders = Order::currentStatus('pending')->whereHas('taxi_order')->whereTime('updated_at', '<=', $expireDateTime)->limit(20)->get();

        foreach ($orders as $order) {
            $order->setStatus('cancelled');
        }
    }
}
