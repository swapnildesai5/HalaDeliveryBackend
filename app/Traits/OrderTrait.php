<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Order;
use App\Models\User;
use App\Services\FirestoreRestService;

trait OrderTrait
{
    use GoogleMapApiTrait;
    use FirebaseAuthTrait;

    public function getNewOrderStatus(Request $request)
    {

        $orderDate = Carbon::parse("" . $request->pickup_date . " " . $request->pickup_time . "");
        $hoursDiff = Carbon::now()->diffInHours($orderDate);

        if (!empty($request->pickup_date) && $hoursDiff > setting('minScheduledTime', 2)) {
            return "scheduled";
        } else {
            return "pending";
        }
    }



    // DATA
    public function clearFirestore(Order $order)
    {
        //
        $canClearFirestore = (bool) setting('clearFirestore', 1);
        //
        if (in_array($order->status, ['failed', 'cancelled', 'delivered', 'completed']) && $canClearFirestore) {
            try {
                $firestoreClient = $this->getFirebaseStoreClient();
                $firestoreClient->deleteDocument("orders/" . $order->code . "");
            } catch (\Exception $ex) {
                logger("Error deleting firebase firestore document", [$ex->getMessage() ?? $ex]);
            }
        }

        //clear driver new laert node on firebase
        $user = User::find(\Auth::id());
        //
        if (!in_array($order->status, ['pending']) && !empty($user) && $user->hasRole('driver')) {
            try {
                $firestoreClient = $this->getFirebaseStoreClient();
                $driverNewOrderAlertRef = "driver_new_order/" . $user->id . "";
                $firestoreClient->deleteDocument($driverNewOrderAlertRef);
            } catch (\Exception $ex) {
                logger("Error deleting driver new order alert firestore document", [$ex->getMessage() ?? $ex]);
            }
        }
    }

    public function clearDriverNewOrderFirestore()
    {

        //
        try {
            $firestoreRestService = new FirestoreRestService();
            $expiredDriverNewOrders = $firestoreRestService->exipredDriverNewOrders();
            foreach ($expiredDriverNewOrders as $expiredDriverNewOrder) {
                try {
                    $firestoreClient = $this->getFirebaseStoreClient();
                    $driverNewOrderAlertRef = "driver_new_order/" . $expiredDriverNewOrder["id"] . "";
                    $firestoreClient->deleteDocument($driverNewOrderAlertRef);
                } catch (\Exception $ex) {
                    logger("Error deleting driver new order alert firestore document", [$ex->getMessage() ?? $ex]);
                }
            }
        } catch (\Exception $ex) {
            logger("Error deleting firebase firestore document", [$ex->getMessage() ?? $ex]);
        }
    }
}
