<?php

namespace App\Observers;

use App\Models\Order;
use App\Traits\FirebaseAuthTrait;
use App\Traits\OrderTrait;

class TaxiOrderObserver
{

    use FirebaseAuthTrait, OrderTrait;

    public function updated(Order $model)
    {

        $driver = $model->driver;
        //update driver node on firebase 
        if (!empty($driver)) {
            //update driver free record on firebase
            //driver ref
            $driverRef = "drivers/" . $driver->id . "";
            $firestoreClient = $this->getFirebaseStoreClient();
            //
            try {
                $firestoreClient->addDocument(
                    $driverRef,
                    [
                        'free' => $driver->assigned_orders == 0 ? 1 : 0,
                        'online' => (int) $driver->is_online,
                    ]
                );
            } catch (\Exception $error) {
                try {
                    $firestoreClient->updateDocument(
                        $driverRef,
                        [
                            'free' => $driver->assigned_orders == 0 ? 1 : 0,
                            'online' => (int) $driver->is_online,
                        ]
                    );
                } catch (\Exception $error) {
                    logger("Dirver DATA update error", [$error]);
                }
            }
        }

        //
        $this->clearFirestore($model);
    }


    
}
