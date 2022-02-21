<?php

namespace App\Upgrades;

use Illuminate\Support\Facades\Schema;

class Upgrade33 extends BaseUpgrade
{

    public $versionName = "1.5.0";
    //Runs or migrations to be done on this version
    public function run()
    {

        //add prepare time to vendor
        if (!Schema::hasColumn("orders", 'payer')) {
            Schema::table("orders", function ($table) {
                $table->boolean('payer')->default(true)->after('height');
            });
        }
    }

    public function update(){}
    

}
