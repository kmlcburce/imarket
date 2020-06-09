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
    public $productController = 'Increment\Imarket\Product\Http\ProductController';

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
      $tempResult = json_decode($tempResult, true);
      $this->response['data'] = $this->manageResultSearch($tempResult, $data['location'], $data['start_date'], $data['end_date']);
      return $this->response();
    }

    public function manageResultSearch($result, $location, $startDate, $endDate){
      $newResult = array();
      if(sizeof($result) > 0){
        $i = 0;
        foreach ($result as $key) {
          $schedule = $this->checkDate($result[$i]['id'], $startDate, $endDate);
          if($schedule == null){
            $result[$i]['price'] = app($this->productPricingController)->getPrice($result[$i]['id']);
            $result[$i]['featured'] = app($this->productImageController)->getProductImage($result[$i]['id'], 'featured');
            $result[$i]['images'] = app($this->productImageController)->getProductImage($result[$i]['id'], null);
            $result[$i]['tag_array'] = app($this->productController)->manageTags($result[$i]['tags']);
            if($location == null){
              $result[$i]['location'] = app($this->productExclusiveLocationController)->getByParams($result[$i]['id']);
            }else{
              $result[$i]['location'] = app($this->productExclusiveLocationController)->getByParamsWithLocation($result[$i]['id'], $location);
            }
            $result[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y H:i A');
            $result[$i]['merchant'] = app($this->merchantController)->getByParams('id', $result[$i]['merchant_id']);
            $result[$i]['schedule'] = null;
            $newResult[] = $result[$i];
          }
          $i++;
        }
      }
      return $newResult;
    }

    public function retrieveDetails(Request $request){
      $data = $request->all();
      $this->model = new Product();
      $this->retrieveDB($data);
      $this->response['data'] = $this->manageResult($this->response['data'], null, $data['start_date'], $data['end_date']);
      return $this->response();
    }

    public function manageResult($result, $location, $startDate = null, $endDate = null){
      if(sizeof($result) > 0){
        $i = 0;
        foreach ($result as $key) {
          $result[$i]['price'] = app($this->productPricingController)->getPrice($result[$i]['id']);
          $result[$i]['featured'] = app($this->productImageController)->getProductImage($result[$i]['id'], 'featured');
          $result[$i]['images'] = app($this->productImageController)->getProductImage($result[$i]['id'], null);
          $result[$i]['tag_array'] = app($this->productController)->manageTags($result[$i]['tags']);
          if($location == null){
            $result[$i]['location'] = app($this->productExclusiveLocationController)->getByParams($result[$i]['id']);
          }else{
            $result[$i]['location'] = app($this->productExclusiveLocationController)->getByParamsWithLocation($result[$i]['id'], $location);
          }
          $result[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y H:i A');
          $result[$i]['merchant'] = app($this->merchantController)->getByParams('id', $result[$i]['merchant_id']);

          if($startDate != null){
            $result[$i]['schedule'] = $this->checkDate($result[$i]['id'], $startDate, $endDate);
          }

          $i++;
        }
      }
      return $result;
    }

    public function checkDate($productId, $startDate, $endDate){
      $result = Rental::where('product_id', '=', $productId)
      ->where(function ($query) use ($startDate) {
        $query->where('start', '<=', $startDate)->where('end', '>=', $startDate);
      })->orWhere(function ($query) use ($endDate) {
        $query->where('start', '<=', $endDate)->where('end', '>=', $endDate);
      })
      ->get();
      return sizeof($result) > 0 ? $result[0] : null;
    }
}
