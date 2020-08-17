<?php


namespace Increment\Imarket\Delivery\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Delivery\Models\Delivery;
use Carbon\Carbon;
class DeliveryController extends APIController
{
  public $locationClass = 'Increment\Imarket\Location\Http\LocationController';
  public $checkoutClass = 'Increment\Imarket\Cart\Http\CheckoutController';
  function __construct(){
    $this->model = new Delivery();
  }

  public function myDeliveries(Request $request){
    $data = $request->all();
    $this->model = new Delivery();
    $this->retrieveDB($data);
    $result = $this->response['data'];
    if(sizeof($result) > 0){
      $i = 0;
      foreach ($result as $key) {
        $checkout = app($this->checkoutClass)->getByParams('id', $key['checkout_id']);
        $this->response['data'][$i]['checkout'] = $checkout;
        $this->response['data'][$i]['merchant_location'] = app($this->locationClass)->getByParams('merchant_id', $key['merchant_id']);
        $this->response['data'][$i]['location'] = null;
        if($checkout){
          $this->response['data'][$i]['location'] = app($this->locationClass)->getByParams('id', $checkout['id']);
        }
        $i++;
      }
    }
    return $this->response();
  }
}