<?php

namespace Increment\Imarket\Merchant\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Merchant\Models\Merchant;
use Increment\Imarket\Cart\Models\Checkout;
use Carbon\Carbon;
use App\Synqt;
use Illuminate\Support\Facades\DB;

class MerchantController extends APIController
{
  function __construct()
  {
    $this->model = new Merchant();

    $this->notRequired = array(
      'name', 'address', 'prefix', 'logo', 'website', 'email', 'schedule', 'website', 'addition_informations'
    );
  }
  public $locationClass = 'Increment\Imarket\Location\Http\LocationController';
  public $imageClass = 'Increment\Common\Image\Http\ImageController';

  public function create(Request $request)
  {
    $data = $request->all();
    $verify = Merchant::where('account_id', '=', $data['account_id'])->get();
    if (count($verify) > 0) {
      array_push($this->response['error'], "Duplicate value for account id " . $data['account_id']);
      return $this->response();
    } else {
      $data['code'] = $this->generateCode();
      $data['status'] = 'not_verified';
      $this->model = new Merchant();
      $this->insertDB($data);
      return $this->response();
    }
  }

  public function generateCode()
  {
    $code = 'mer_' . substr(str_shuffle($this->codeSource), 0, 60);
    $codeExist = Merchant::where('id', '=', $code)->get();
    if (sizeof($codeExist) > 0) {
      $this->generateCode();
    } else {
      return $code;
    }
  }

  public function retrieve(Request $request)
  {
    $data = $request->all();
    $this->model = new Merchant();
    $this->retrieveDB($data);
    $result = $this->response['data'];
    if (sizeof($result) > 0) {
      $i = 0;
      foreach ($result as $key) {
        $accountId = $result[$i]['account_id'];
        $accountDetails = $this->retrieveAccountDetails($accountId);
        $accountDate = Carbon::createFromFormat('Y-m-d H:i:s', $accountDetails['created_at']);
        $current = Carbon::now();
        $diff = $accountDate->diffInDays($current, false);
        if ($diff < 30) {
          $this->response['data'][$i]['account'] = $this->retrieveAccountDetails($accountId);
          if (env('RATING') == true) {
            $this->response['data'][$i]['rating'] = app('Increment\Common\Rating\Http\RatingController')->getRatingByPayload('merchant', $accountId);
          }
        } else if (env('PLAN') == true) {
          if (app('Increment\Plan\Http\PlanController')->checkPlan($accountId) == true) {
            $this->response['data'][$i]['account'] = $this->retrieveAccountDetails($accountId);
            if (env('RATING') == true) {
              $this->response['data'][$i]['rating'] = app('Increment\Common\Rating\Http\RatingController')->getRatingByPayload('merchant', $accountId);
            }
          }
        } else {
          //
        }
        $i++;
      }
    }
    return $this->response();
  }

  public function retrieveMerchants(Request $request)
  {
    $data = $request->all();
    $synqt = Synqt::where('id', '=', $data['synqt_id'])->where('deleted_at', '=', null)->get();
    $others = [];
    $res = [];
    if (sizeof($synqt) > 0) {
      $condition = json_decode($synqt[0]['details'], true);
      if(sizeof($condition['cuisine']) > 0){
        $a=0;
        foreach($condition['cuisine'] as $key){
          $others = Merchant::limit($data['limit'])->offset($data['offset'])->get();
          $response = $this->manageResultMerchant($key, $others, $condition, $synqt);
          if($response !== null){
            array_push($res, $response);
          }
          $a++;
        }
        $this->response['data'] = $res;
      }else{
        $res = $this->manageResultMerchant(null, $others, $condition, $synqt);
        $this->response['data'] = $res;
      }
      return $this->response();
    }
  }

  public function manageResultMerchant($key, $others, $condition, $synqt){
    $result = [];
    if(sizeof($others) > 0){  
      $i = 0;
      foreach ($others as $value) {
        $value['addition_informations'] = (array)json_decode($value['addition_informations']);
        // dd();
        if(in_array($key, $value['addition_informations']['cuisine'])){
          $distance = app($this->locationClass)->getLocationDistanceByMerchant(json_decode($synqt[0]['location_id']), json_decode($value['address']));
          $totalDistance = preg_replace('/[^0-9.]+/', '', $distance);
          if($totalDistance <= $condition['radius']){
            $products = DB::table('products as T1')
              ->leftJoin('pricings as T2', 'T2.product_id', '=', 'T1.id')
              ->where('T2.price', '>=', $condition['price_range']['min'])
              ->where('T2.price', '<=', $condition['price_range']['max'])
              ->where('T1.merchant_id', '=', $value['id'])->get();
            $others[$i]['products'] = $products;
            $others[$i]['account'] = $this->retrieveAccountDetails($value['account_id']);
            $others[$i]['rating'] = app('Increment\Common\Rating\Http\RatingController')->getRatingByPayload('merchant_id', $value['id']);
            $others[$i]['featured_photos'] = app($this->imageClass)->retrieveFeaturedPhotos('account_id', $value['account_id'], 'category', 'featured-photo');
  
            return $others[$i];
          }
        }else{
          $distance = app($this->locationClass)->getLocationDistanceByMerchant(json_decode($synqt[0]['location_id']), json_decode($value['address']));
          $totalDistance = preg_replace('/[^0-9.]+/', '', $distance);
          if($totalDistance <= $condition['radius']){
            $products = DB::table('products as T1')
              ->leftJoin('pricings as T2', 'T2.product_id', '=', 'T1.id')
              ->where('T2.price', '>=', $condition['price_range']['min'])
              ->where('T2.price', '<=', $condition['price_range']['max'])
              ->where('T1.merchant_id', '=', $value['id'])->get();
            $others[$i]['products'] = $products;
            $others[$i]['account'] = $this->retrieveAccountDetails($value['account_id']);
            $others[$i]['rating'] = app('Increment\Common\Rating\Http\RatingController')->getRatingByPayload('merchant_id', $value['id']);
            $others[$i]['featured_photos'] = app($this->imageClass)->retrieveFeaturedPhotos('account_id', $value['account_id'], 'category', 'featured-photo');
  
            return $others[$i];
          }
          // return $others[$i];
        }
        $i++;
      }
    }
  }

  public function retrieveAll(Request $request)
  {
    $data = $request->all();
    $this->model = new Merchant();
    $this->retrieveDB($data);
    return $this->response();
  }

  public function updateByVerification(Request $request)
  {
    $data = $request->all();
    $result = Merchant::where('account_id', '=', $data['account_id'])->update(array(
      'status' => $data['status']
    ));
    $this->response['data'] = $result ? true : false;
    return $this->response();
  }

  public function updateByParams($column, $value, $data)
  {
    $result = Merchant::where($column, '=', $value)->update($data);
    return $result;
  }

  public function getOrderNumber($accountId)
  {
    $account = Merchant::where('id', '=', $accountId)->first();
    if ($account) {
      $checkouts = Checkout::where('account_id', '=', $accountId)->count();
      if ($checkouts) {
        if ($checkouts >= 1000) {
          return $account->order_suffix . $checkouts;
        } else if ($checkouts >= 100) {
          return $account->order_suffix . '0' . $checkouts;
        } else if ($checkouts >= 10) {
          return $account->order_suffix . '00' . $checkouts;
        } else if ($checkouts >= 0) {
          return $account->order_suffix . '000' . $checkouts;
        }
      } else {
        return $account->order_suffix . '0001';
      }
    }
    return null;
  }

  public function getMerchant($id)
  {
    $result = Merchant::where('id', '=', $id)->get();

    if (sizeof($result) > 0) {
      $result[0]['account'] = $this->retrieveAccountDetails($result[0]['account_id']);
      if (env('RATING') == true) {
        $result[0]['rating'] = app('Increment\Common\Rating\Http\RatingController')->getRatingByPayload('merchant', $result[0]['account_id']);
      }
      return $result[0];
    } else {
      return null;
    }
  }

  public function getByParams($column, $value)
  {
    $result = Merchant::where($column, '=', $value)->get();
    return sizeof($result) > 0 ? $result[0] : null;
  }

  public function getByParamsReturnByParam($column, $value, $param)
  {
    $result = Merchant::where($column, '=', $value)->get();
    return sizeof($result) > 0 ? $result[0][$param] : null;
  }


  public function retriveMerchantWithRating(Request $request)
  {
    $data = $request->all();
    $reservation = app('Increment\Imarket\Reservation\Http\ReservationController')->retrieveByParams(
      array(
        array('deleted_at', '=', null),
        array('payload_value', '=', $data['synqtId'])
      ),
      ['merchant_id']
    );
    if ($reservation !== null) {
      $result = Merchant::where('id', '=', $reservation['merchant_id'])->where('deleted_at', '=', null)->get();
      if (sizeof($result) > 0) {
        $i = 0;
        foreach ($result as $value) {
          $result[$i]['rating'] = app('Increment\Common\Rating\Http\RatingController')->getRatingByParams('payload_value', $result[$i]['id']);
        }
        $this->response['data'] = $result;
      }
    }
    return $this->response();
  }
}
