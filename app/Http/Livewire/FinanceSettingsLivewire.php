<?php

namespace App\Http\Livewire;

use Exception;
use LVR\Colour\Hex;

class FinanceSettingsLivewire extends BaseLivewireComponent
{

    // App settings
    public $enableReferSystem;
    public $referRewardAmount;
    public $enableDriverWallet;
    public $driverWalletRequired;
    public $vendorEarningEnabled;
    public $driversCommission;
    public $vendorsCommission;
    public $minimumTopupAmount;
    public $vendorSetDeliveryFee;


    public function mount()
    {
        //

        $this->enableDriverWallet = (bool) setting('enableDriverWallet');
        $this->driverWalletRequired = (bool) setting('driverWalletRequired');
        $this->vendorEarningEnabled = (bool) setting('vendorEarningEnabled');
        $this->enableReferSystem = (bool) setting('enableReferSystem');
        $this->vendorSetDeliveryFee = (bool) setting('vendorSetDeliveryFee');
        $this->referRewardAmount = (float) setting('referRewardAmount');
        $this->driversCommission = setting('driversCommission', "0");
        $this->vendorsCommission = setting('vendorsCommission', "0");
        $this->minimumTopupAmount = setting('minimumTopupAmount', 100);
    }

    public function render()
    {

        $this->mount();
        return view('livewire.settings.finance-settings');
    }


    public function saveAppSettings()
    {


        try {

            $this->isDemo();
            $appSettings = [
                'enableDriverWallet' =>  $this->enableDriverWallet,
                'driverWalletRequired' =>  $this->driverWalletRequired,
                'vendorEarningEnabled' =>  $this->vendorEarningEnabled,
                'driversCommission' =>  $this->driversCommission,
                'vendorsCommission' =>  $this->vendorsCommission,
                'minimumTopupAmount' =>  $this->minimumTopupAmount,
                'enableReferSystem' =>  $this->enableReferSystem,
                'referRewardAmount' =>  $this->referRewardAmount,
                'vendorSetDeliveryFee' =>  $this->vendorSetDeliveryFee,
            ];

            // update the site name
            setting($appSettings)->save();



            $this->showSuccessAlert(__("Finance Settings saved successfully!"));
            $this->reset();
        } catch (Exception $error) {
            $this->showErrorAlert($error->getMessage() ?? __("Finance Settings save failed!"));
        }
    }
}
