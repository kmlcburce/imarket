<?php

namespace Increment\Imarket\Rental\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Rental\Models\Rental;
use Increment\Imarket\Product\Models\Product;
use Increment\Imarket\Product\Models\ProductExclusiveLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class RentalController extends APIController
{
    public $productImageController = 'Increment\Imarket\Product\Http\ProductImageController';
    public $productAttrController = 'Increment\Imarket\Product\Http\ProductAttributeController';
    public $productPricingController = 'Increment\Imarket\Product\Http\PricingController';
    public $productExclusiveLocationController = 'Increment\Imarket\Product\Http\ProductExclusiveLocationController';
    public $merchantController = 'Increment\Imarket\Merchant\Http\MerchantController';

   	function __construct(){
   		$this->model = new Rental();
   	}

    public function search(Request $request){
      $data = $request->all();
      $tempResult = DB::table('product_exclusive_locations as T1')
          ->join('products as T2', 'T2.id', '=', 'T1.product_id')
          ->where('T1.locality', 'like', $data['location'].'%')
          ->where('T2.tags', 'like', $data['category'].'%')
          ->select('T2.*')
          ->get();
      $this->response['data'] = json_decode($tempResult, true);
      $this->response['data'] = $this->manageResult($this->response['data'], $data['location']);
      return $this->response();
    }

    public function manageResult($result, $location){
      if(sizeof($result) > 0){
        $i = 0;
        foreach ($result as $key) {
          $result[$i]['price'] = app($this->productPricingController)->getPrice($result[$i]['id']);
          $result[$i]['featured'] = app($this->productImageController)->getProductImage($result[$i]['id'], 'featured');
          $result[$i]['location'] = app($this->productExclusiveLocationController)->getByParams($result[$i]['id'], $location);
          $result[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y H:i A');
          $i++;
        }
      }
      return $result;
    }
}
