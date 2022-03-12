<?php

namespace App\Observers;

use App\Models\Order;
use App\Models\User;
use App\Mail\OrderUpdateMail;
use App\Models\AutoAssignment;
use App\Models\PackageTypePricing;
use App\Services\OrderEarningService;
use App\Services\TaxiOrderService;
use App\Traits\FirebaseAuthTrait;
use App\Traits\FirebaseDBTrait;
use App\Traits\OrderTrait;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{

    use FirebaseDBTrait, FirebaseAuthTrait;
    use OrderTrait;

    public function creating(Order $model)
    {
        // logger("Pending Order", [$model]);
        $model->code = Str::random(10);
        $model->verification_code = $this->generateRandomString(5);
        if (empty($model->user_id)) {
            $model->user_id = Auth::id();
        }

        if (empty($model->payer)) {
            $model->payer = true;
        }
    }

    public function created(Order $model)
    {
        //sending notifications base on status change of the order
        // logger("called updated order ===> YES");
        // logger("order created called", [$model->id]);
        // $model->sendOrderStatusChangeNotification($model, true);
        $this->sendOrderUpdateMail($model);
        $this->autoMoveToReady($model);
        $this->autoMoveToPreparing($model);
        $this->clearAutoAssignment($model);

        //for taxi booking
        if (!empty($model->taxi_order)) {
            $taxiOrderService = new TaxiOrderService();
            $taxiOrderService->saveTaxiOrderToFirebaseFirestore($model);
        }
    }

    public function updating(Order $model)
    {
        //sending notifications base on status change of the order
        // logger("order updated called started", [$model->id]);
        //driver id changed
        if ($model->isDirty('driver_id')) {
            $model->sendOrderNotificationToDriver($model);
        }

        //payment_status
        if (in_array($model->status, ['delivered', 'completed']) && $model->payment_method->is_cash) {
            $model->payment_status = "successful";
        }
    }

    public function updated(Order $model)
    {
        //sending notifications base on status change of the order
        // logger("order updated called started", [$model->id]);
        //driver id changed
        if ($model->isDirty('driver_id')) {
            $model->sendOrderNotificationToDriver($model);
        }
        //
        $model->refresh();
        // $model->sendOrderStatusChangeNotification($model);
        $this->sendOrderUpdateMail($model);
        $orderEarningService = new OrderEarningService();
        $orderEarningService->updateEarning($model);

        $model->refundUser();
        $this->updatePaymentStatus($model);
        $this->autoMoveToReady($model);
        $this->autoMoveToPreparing($model);
        $this->clearAutoAssignment($model);
        $this->clearFirestore($model);


        //for taxi booking
        if (!empty($model->taxi_order)) {
            $taxiOrderService = new TaxiOrderService();
            $taxiOrderService->updateTaxiOrderToFirebaseFirestore($model);
        }


        //revert qty of cancelled order
        if (!empty($model->products())  && in_array($model->status, ['failed', 'cancelled'])) {
            foreach ($model->products() as $orderProduct) {
                $orderProduct->product->available_qty += $orderProduct->quantity;
                $orderProduct->product->save();
            }
        }
    }

    //
    public function sendOrderUpdateMail($model)
    {
        //only delivered
        if (in_array($model->status, ['delivered'])) {
            //send mail
            try {
                \Mail::to($model->user->email)
                    ->cc([$model->vendor->email])
                    ->send(new OrderUpdateMail($model));
            } catch (\Exception $ex) {
                // logger("Mail Error", [$ex]);
                logger("Mail Error");
            }
        }
    }

    public function autoMoveToReady(Order $order)
    {

        //
        $packageTypePricing = PackageTypePricing::where([
            "vendor_id" => $order->vendor_id,
            "package_type_id" => $order->package_type_id,
        ])->first();

        //
        if (
            in_array($order->status, ["pending", "preparing"])
            && ($packageTypePricing->auto_assignment ?? 0)
            && ($order->payment_method != null && $order->payment_method->is_cash || $order->payment_status == "successful")
        ) {
            // logger("Auto move to ready kicked in");
            $order->setStatus("ready");
        }
    }

    public function autoMoveToPreparing(Order $order)
    {

        if (
            in_array($order->status, ["pending"])
            && ($order->vendor->auto_accept ?? 0)
            && ($order->payment_method != null && $order->payment_method->is_cash || $order->payment_status == "successful")
        ) {
            $order->setStatus("preparing");
        }
    }

    public function clearAutoAssignment(Order $order)
    {
        //
        $order->refresh();
        if (in_array($order->status, ["ready", "enroute"])) {
            $autoAssignments = AutoAssignment::where('order_id', $order->id)->get();
            if (count($autoAssignments) > 0) {
                AutoAssignment::where('order_id', $order->id)->delete();
            }
        }
    }


    function generateRandomString($length = 25)
    {
        $characters = '123456789abcdefghijklmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function updatePaymentStatus($order)
    {
        //update payment_status after order is completed by driver or vendor
        $user = User::find(\Auth::id() ?? 0);
        if (
            !empty($user) && $user->hasAnyRole("admin|manager|driver") &&
            in_array($order->status, ["successful", "completed", "delivered"]) &&
            $order->payment_method->is_cash
        ) {
            $order->payment_status = "successful";
            $order->saveQuietly();
        }

        //calculate earning incase you missed it 
        $orderEarningService = new OrderEarningService();
        $orderEarningService->updateEarning($order);
    }
}
