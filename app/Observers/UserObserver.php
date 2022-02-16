<?php

namespace App\Observers;

use App\Models\User;
use App\Mail\NewAccountMail;
use App\Models\Vehicle;
use App\Traits\FirebaseAuthTrait;

class UserObserver
{

    use FirebaseAuthTrait;

    public function creating(User $user)
    {
        //
        $user->code = \Str::random(3) . "" . $user->id . "" . \Str::random(2);
    }

    public function created(User $user)
    {
        //update wallet
        if (empty($user->wallet)) {
            $user->updateWallet(0);
        }
        //send mail
        try {
            \Mail::to($user->email)->send(new NewAccountMail($user));
        } catch (\Exception $ex) {
            // logger("Mail Error", [$ex]);
            logger("Mail Error");
        }

        //set vehicle type id, if any to firebase
        $this->updateDriverVehicleType($user);
    }

    public function updated(User $user)
    {
        //set vehicle type id, if any to firebase
        $this->updateDriverVehicleType($user);
    }

    public function deleting(User $model)
    {
       
    }



    // UPDATE DRIVER DATA TO FIRESTORE 
    public function updateDriverVehicleType(User $user)
    {

        //driver user
        if (!$user->hasRole('driver')) {
            return;
        }

        //get any connected vehicle to the driver 
        $vehicleTypeId = 0;
        $vehicle = Vehicle::where('driver_id', $user->id)->first();
        if (!empty($vehicle)) {
            $vehicleTypeId = $vehicle->vehicle_type_id;
        }
        //sync vehicle type id
        //driver ref
        $driverRef = "drivers/" . $user->id . "";
        $firestoreClient = $this->getFirebaseStoreClient();
        //
        try {
            $firestoreClient->addDocument(
                $driverRef,
                [
                    'vehicle_type_id' => (int) $vehicleTypeId
                ]
            );
        } catch (\Exception $error) {
            try {
                $firestoreClient->updateDocument(
                    $driverRef,
                    [
                        'vehicle_type_id' => (int) $vehicleTypeId
                    ]
                );
            } catch (\Exception $error) {
                logger("Dirver DATA update error", [$error]);
            }
        }
    }
}
