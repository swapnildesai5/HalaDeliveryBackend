<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Vendor;
use App\Models\VendorType;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class DevDesk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:desk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run dev commands for some tasks';

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

        if (false) {
            if (!\App::environment('production')) {
                //generate random prepare time and delivery time for vendors
                $vendors = Vendor::all();
                foreach ($vendors as $vendor) {

                    //prepare time range or not
                    if (rand(0, 1)) {
                        $vendor->prepare_time = "" . rand(10, 30) . " - " . rand(60, 90) . "";
                    } else {
                        $vendor->prepare_time = "" . rand(20, 90) . "";
                    }

                    //delivery time range or not
                    if (rand(0, 1)) {
                        $vendor->delivery_time = "" . rand(10, 30) . " - " . rand(60, 90) . "";
                    } else {
                        $vendor->delivery_time = "" . rand(20, 90) . "";
                    }

                    //
                    $vendor->save();
                }
            }
        }

        if (true) {
            //generate data to test out e-commerce
            if (!\App::environment('production')) {
                //
                $commerceVendorType = VendorType::where('slug', 'commerce')->first();
                $vendors = Vendor::where('vendor_type_id', $commerceVendorType->id)->get();
                foreach ($vendors as $vendor) {
                    $vendor->delete();
                }


                //create new vendors
                $numberOfVendors = 1; //rand(2, 5);
                $faker = \Faker\Factory::create();
                for ($i = 0; $i < $numberOfVendors; $i++) {
                    $vendor = new Vendor();
                    $vendor->name = $faker->company;
                    $vendor->description = $faker->catchPhrase;
                    $vendor->delivery_fee = $faker->randomNumber(2, false);
                    $vendor->delivery_range = $faker->randomNumber(3, false);
                    $vendor->tax = $faker->randomNumber(2, false);
                    $vendor->phone = $faker->phoneNumber;
                    $vendor->email = $faker->email;
                    $vendor->address = $faker->address;
                    $vendor->latitude = $faker->latitude();
                    $vendor->longitude = $faker->longitude();
                    $vendor->tax = rand(0, 1);
                    $vendor->pickup = rand(0, 1);
                    $vendor->delivery = rand(0, 1);
                    $vendor->is_active = 1;
                    $vendor->vendor_type_id = $commerceVendorType->id;
                    $vendor->save();

                    //
                    try {
                        $vendor->addMediaFromUrl("https://source.unsplash.com/240x240/?logo")->toMediaCollection("logo");
                        $vendor->addMediaFromUrl("https://source.unsplash.com/420x240/?vendor")->toMediaCollection("feature_image");
                    } catch (\Exception $ex) {
                        logger("Error", [$ex->getMessage()]);
                    }

                    //products
                    $productNames = ["T-shirt", 'School bag', 'Ceiling Fan', "air cooler", "baby wear", "fashion", "tech", "gadgets"];

                    $keyword = $productNames[rand(0, count($productNames) - 1)];
                    $productsArray = $this->getProducts($keyword);

                    foreach ($productsArray as $productObject) {

                        if ($productObject["price"] == null) {
                            $productObject["price"] = rand(1, 1000);
                        }
                        $product = new Product();
                        $product->name = $productObject["name"];
                        $product->description = $productObject["name"];
                        $product->price = $productObject["price"];
                        $product->discount_price = rand(0, $product->price);
                        $product->capacity = "";
                        $product->unit = "";
                        $product->package_count = 1;
                        $product->featured = rand(0, 1);
                        $product->deliverable = rand(0, 1);
                        $product->is_active = 1;
                        $product->vendor_id = $vendor->id;
                        $product->save();

                        //
                        try {
                            $product->addMediaFromUrl($productObject["image"])->toMediaCollection();
                        } catch (\Exception $ex) {
                            logger("Error", [$ex->getMessage()]);
                        }
                    }
                }
            }
        }

       
        return 0;
    }

    public function getProducts($keyword)
    {


        $response = Http::withHeaders([
            'x-rapidapi-host' => 'amazon-scraper4.p.rapidapi.com',
            'x-rapidapi-key' => '5f43ecf1c8msh055a0e55503f6c6p1d7189jsn529594637e8a'
        ])
            ->get("https://amazon-scraper4.p.rapidapi.com/search/" . $keyword . "?api_key=3c2de5d260e5ceda5852b9cd64c84691");

        logger("response", [$response->json()]);
        if ($response->successful()) {
            return $response->json()["results"];
        } else {
            return [];
        }
    }

   
}
