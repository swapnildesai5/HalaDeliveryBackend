@section('title', __('Finance Settings'))
<div>

    <x-baseview title="{{ __('Finance Settings') }}">

        <x-form action="saveAppSettings">

            <div class="">
                <p class="pt-4 text-2xl">{{ __("Refer") }}</p>
                <div class='grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3'>

                    {{-- refer --}}
                    <div class="block mt-4 text-sm">
                        <p>{{ __('Refer System') }}</p>
                        <x-checkbox title="{{ __('Enable') }}" name="enableReferSystem" :defer="true" />
                    </div>
                    <div x-data="{ open: @entangle('enableReferSystem') }">
                        <x-input title="{{ __('Refer Amount') }}" name="referRewardAmount" />
                    </div>


                </div>




                {{-- Driver releated settings --}}
                <p class="pt-4 mt-10 text-2xl border-t">{{ __('Earning') }}</p>
                <div class='grid grid-cols-1 gap-4 mb-10 md:grid-cols-2 '>

                    <div class="block mt-4 text-sm border rounded p-2">
                        <p>{{ __('Vendor Earning') }}</p>
                        <x-checkbox title="{{ __('Enable') }}" name="vendorEarningEnabled" :defer="true" />

                        <p class="py-2 text-xs font-light"><span class="text-red-600 text-sm font-medium pr-2">Note:</span>{{ __('Vendor Earning Enable(irrespective of the payment method used)') }}</p>
                    </div>


                    <div class="block mt-4 text-sm border rounded p-2">
                        <p>{{ __('Driver Wallet Balance Require') }}</p>
                        <x-checkbox title="{{ __('Enable') }}" name="driverWalletRequired" :defer="true" />
                        <p class="py-2 text-xs font-light"><span class="text-red-600 text-sm font-medium pr-2">Note:</span>{{ __('Driver must have enough in wallet balance irrespective of the payment method') }}</p>
                    </div>

                    <div class="block mt-4 text-sm">
                        <p>{{ __('Driver Wallet System') }}</p>
                        <x-checkbox title="{{ __('Enable') }}" name="enableDriverWallet" :defer="true" />
                    </div>
                    

                </div>
                {{-- Finance --}}
                <p class="pt-4 mt-10 text-2xl border-t">{{ __("Finance") }}</p>
                <div class='grid grid-cols-1 gap-4 mb-10 md:grid-cols-2 lg:grid-cols-3'>
                    <div class="block mt-4 text-sm">
                        <x-input title="{{ __('General Drivers Commission') }}(%)" name="driversCommission" />
                    </div>

                    <div class="block mt-4 text-sm">
                        <x-input title="{{ __('General Vendors Commission') }}(%)" name="vendorsCommission" />
                    </div>

                    <div class="block mt-4 text-sm">
                        <x-input title="{{ __('Minimum Wallet Topup Ammount') }}" name="minimumTopupAmount" />
                    </div>
                    <div class="block mt-4 text-sm">
                        <p>{{ __('Vendor can set delivery fee') }}</p>
                        <x-checkbox title="{{ __('Enable') }}" name="vendorSetDeliveryFee" :defer="true" />
                    </div>

                </div>


                <x-buttons.primary title="{{ __('Save Changes') }}" />
                <div>
        </x-form>

    </x-baseview>

</div>
