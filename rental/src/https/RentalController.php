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
    public $notificationClass = 'Increment\Common\Notification\Http\NotificationController';

   	function __construct(){
   		$this->model = new Rental();
   	}

    public function create(Request $request){
      $data = $request->all();
      $data['code'] = $this->generateCode();
      $this->insertDB($data);
      $merchant = app($this->merchantController)->getByParams('id', $data['merchant_id']);
      if($this->response['data'] > 0 && $merchant != null){
        $parameter = array(
          'to' => $data['to'],
          'from' => $data['account_id'],
          'payload' => 'installment',
          'payload_value' => $data['code'],
          'route' => '/installments',
          'created_at' => Carbon::now()
        );
        app($this->notificationClass)->createByParams($parameter);
      }
      return $this->response();
    }

    public function generateCode(){
      $code = 'REN-'.substr(str_shuffle($this->codeSource), 0, 60);
      $codeExist = Rental::where('code', '=', $code)->get();
      if(sizeof($codeExist) > 0){
        $this->generateCode();
      }else{
        return $code;
      }
    }

    public function getByParams($column, $value){
      $result = Rental::where($column, '=', $value)->orderBy('created_at', 'desc')->get();
      if(sizeof($result) > 0){
        $i = 0;
        foreach ($result as $key => $value) {
          $result[$i]['product']     = app($this->productController)->getProductByParamsInstallment('id', $result[$i]['product_id']);
          $result[$i]['start_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['start'])->copy()->tz($this->response['timezone'])->format('F j, Y H:i A');
          $result[$i]['end_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['end'])->copy()->tz($this->response['timezone'])->format('F j, Y H:i A');
          $result[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y H:i A');
          $i++;
        }
        return $result;
      }
      return null;
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
