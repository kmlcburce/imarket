<?php

namespace Increment\Imarket\Cart\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Cart\Models\Checkout;
use Increment\Imarket\Cart\Models\CheckoutItem;
use Increment\Imarket\Merchant\Models\Merchant;
use Increment\Imarket\Product\Models\Product;
use Increment\Imarket\Product\Models\Pricing;
use Increment\Imarket\Payment\Models\StripeWebhook;
use Carbon\Carbon;
use App\Jobs\Notifications;

class CheckoutController extends APIController
{
  protected $subTotal = 0;
  protected $total = 0;
  protected $tax = 0;
  public $cartClass = 'Increment\Imarket\Cart\Http\CartController';
  public $checkoutItemClass = 'Increment\Imarket\Cart\Http\CheckoutItemController';
  public $merchantClass = 'Increment\Imarket\Merchant\Http\MerchantController';
  public $locationClass = 'Increment\Imarket\Location\Http\LocationController';

  function __construct(){
  	$this->model = new Checkout();

    $this->notRequired = array(
      'coupon_id',
      'order_number',
      'payment_type',
      'payment_payload',
      'payment_payload_value'
    );
  }

  public function retrieve(Request $request){
    $data = $request->all();
    $this->model = new Checkout();
    $this->retrieveDB($data);
    $result = $this->response['data'];
    if(sizeof($result) > 0){
      $i = 0;
      foreach ($result as $key) {
        $this->response['data'][$i]['account'] = $this->retrieveAccountDetails($result[$i]['account_id']);
        $i++;
      }
    }
    return $this->response();
  }

  public function retrieveOrders(Request $request){
    $data = $request->all();
    $this->model = new Checkout();
    $this->retrieveDB($data);
    $result = $this->response['data'];
    if(sizeof($result) > 0){
      $i = 0;
      foreach ($result as $key) {
        $this->response['data'][$i]['name'] = null;
        $this->response['data'][$i]['location'] = app($this->locationClass)->getAppenedLocationByParams('id', $key['location_id']);
        $this->response['data'][$i]['coupon'] = null;
        $this->response['data'][$i]['date'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
        $i++;
      }
    }
    return $this->response();
  }

  public function create(Request $request){
    $data = $request->all();
    $prefix = app($this->merchantClass)->getByParamsReturnByParam('id', $data['merchant_id'], 'prefix');
    $counter = Checkout::where('merchant_id', '=', $data['merchant_id'])->count();
    $data['order_number'] = $prefix ? $prefix.$this->toCode($counter) : $this->toCode($counter);
    $this->model = new Checkout();
    $this->insertDB($data);
    if($this->response['data'] > 0){
      // create items
      $cartItems = app($this->cartClass)->getItemsInArray('account_id', $data['account_id']);
      if(sizeof($cartItems) > 0){
        $items = array();
        $i = 0;
        foreach ($cartItems as $key => $value) {
          $item = array(
            'account_id'  => $data['account_id'],
            'checkout_id' => $this->response['data'],
            'payload'     => 'product',
            'payload_value' => $cartItems[$i]['id'],
            'size'        => '',
            'color'       => '',
            'qty'       => $cartItems[$i]['quantity'],
            'price'       => $cartItems[$i]['price'][0]['price'],
            'status'       => 'pending'
          );
          $items[] = $item;
          $i++;
        }

        app($this->checkoutItemClass)->insertInArray($items);
        app($this->cartClass)->emptyItems($data['account_id']);

        $data['merchant_account_id'] = null;
        $merchant = Merchant::select('account_id')->where('id', '=', $data['merchant_id'])->get();
        if (sizeof($merchant) > 0) {
          $data['merchant_account_id'] = $merchant[0]['account_id'];
        }
        Notifications::dispatch('orders', $data);
      }
    }

    return $this->response();
  }

  public function toCode($size){
    $length = strlen((string)$size);
    $code = '00000000';
    return substr_replace($code, $size, intval(7 - $length));
  }

  public function getByParams($column, $value){
    $result = Checkout::where($column, '=', $value)->get();
    return sizeof($result) > 0 ? $result[0] : null;
  }

  public function generateCode(){
    $code = 'che_'.substr(str_shuffle($this->codeSource), 0, 60);
    $codeExist = Checkout::where('code', '=', $code)->get();
    if(sizeof($codeExist) > 0){
      $this->generateCode();
    }else{
      return $code;
    }
  }
}
