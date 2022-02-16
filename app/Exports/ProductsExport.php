<?php

namespace App\Exports;

use App\Models\Product;

class ProductsExport extends BaseExport
{
    /**
    * @return \Illuminate\Support\Collection
    */


    public function collection()
    {
        return Product::all();
    }
}
