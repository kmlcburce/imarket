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
    if (sizeof($synqt) > 0) {
      $condition = json_decode($synqt[0]['details'], true);
      $locations = app($this->locationClass)->getAllLocation();
      $merchant = Merchant::get();
      $i = 0;
      if (sizeof($merchant) > 0) {
        $m = 0;
        foreach ($merchant as $merchantKey) {
          foreach ($locations as $key) {
            $distance = app($this->locationClass)->getLocationDistance('account_id', $synqt[0]['account_id'], $key['account_id']);
            $totalDistance = preg_replace('/[^0-9.]+/', '', $distance);
            if ($totalDistance < $condition['radius']) {
              $products = DB::table('products')->where('merchant_id', '=', $merchantKey['id'])->get();
              $products = json_decode(json_encode($products), true);
              $a = 0;
              foreach ($products as $prod) {
                $pricing = DB::table('pricings')->where('product_id', '=', $prod['id'])
                  ->where('price', '>=', $condition['price_range']['min'])
                  ->where('price', '<=', $condition['price_range']['max'])
                  ->get();
                $pricing = json_decode(json_encode($pricing), true);
                if (sizeof($pricing) > 0) {
                  $products[$a]['pricing'] = $pricing;
                }
                $a++;
              }
              $merchant[$m]['products'] = $products;
              $merchant[$m]['account'] = $this->retrieveAccountDetails($merchantKey['account_id']);
              $merchant[$m]['rating'] = app('Increment\Common\Rating\Http\RatingController')->getRatingByPayload('account_id', $merchantKey['account_id']);

            }
            $i++;
          }
          $m++;
        }
      }
      $this->response['data'] = $merchant;
    }
    return $this->response();
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
