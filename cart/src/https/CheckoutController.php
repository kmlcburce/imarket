<?php

namespace Increment\Imarket\Cart\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Cart\Models\Checkout;
use Increment\Imarket\Cart\Models\CheckoutItem;
use Increment\Imarket\Product\Models\Product;
use Increment\Imarket\Product\Models\Pricing;
use Increment\Imarket\Payment\Models\StripeWebhook;
use Carbon\Carbon;
class CheckoutController extends APIController
{
  protected $subTotal = 0;
  protected $total = 0;
  protected $tax = 0;
  public $cartClass = 'Increment\Imarket\Cart\Http\CartController';
  public $checkoutItemClass = 'Increment\Imarket\Cart\Http\CheckoutItemController';

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
        $this->response['data']$[$i]['account'] = $this->retrieveAccountDetails($kresult[$i]['account_id']);
        $i++;
      }
    }
    return $this->response();
  }

  public function create(Request $request){
    $data = $request->all();
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
            'price'       => 100,
            'status'       => 'pending'
          );
          $items[] = $item;
          $i++;
        }
        app($this->checkoutItemClass)->insertInArray($items);
      }
    }

    return $this->response();
  }

  public function getByParams($column, $value){
    $result = Checkout::where($column, '=', $value)->get();
    return sizeof($result) > 0 ? $result[0] : null;
  }
}
