<?php


namespace Increment\Imarket\Delivery\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Delivery\Models\Delivery;
use Carbon\Carbon;
use App\Jobs\Notifications;
class DeliveryController extends APIController
{
  public $locationClass = 'Increment\Imarket\Location\Http\LocationController';
  public $checkoutClass = 'Increment\Imarket\Cart\Http\CheckoutController';
  public $merchantClass = 'Increment\Imarket\Merchant\Http\MerchantController';
  function __construct(){
    $this->model = new Delivery();
    $this->notRequired = array('history');
  }

  public function create(Request $request){
    $data = $request->all();
    
    if($this->getByParams('checkout_id', $data['checkout_id']) != null){
      $this->response['data'] = null;
      $this->response['error'] = 'Already exist';
      return $this->response();
    }

    $data['code'] = $this->generateCode();
    $data['status'] = 'pending';
    $this->model = new Delivery();
    $this->insertDB($data);

    if($this->response['data'] > 0){
      $array = array(
        'merchant'    => app($this->merchantClass)->getByParamsReturnByParam('id', $data['merchant_id'], 'code'),
        'delivery'    => $data['code'],
        'checkout'    => app($this->checkoutClass)->getByParamsReturnByParam('id', $data['checkout_id'], 'code'),
        'check_id'    => $data['checkout_id'],
        'assigned_rider' => $this->retrieveNameOnly($data['rider'])
      );
      Notifications::dispatch('rider', $array);
    }
    return $this->response();
  }

  public function getByParams($column, $value){
    $result = Delivery::where($column, '=', $value)->get();
    return sizeof($result) > 0 ? $result[0] : null;
  }

  public function generateCode(){
    $code = 'del_'.substr(str_shuffle($this->codeSource), 0, 60);
    $codeExist = Delivery::where('code', '=', $code)->get();
    if(sizeof($codeExist) > 0){
      $this->generateCode();
    }else{
      return $code;
    }
  }

  public function getDeliveryName($column, $value){
    $result = Delivery::where($column, '=', $value)->get();
    if(sizeof($result) > 0){
      return $this->retrieveNameOnly($result[0]['rider']);
    }
    return null;
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
        $this->response['data'][$i]['checkout'] = null;
        if($checkout){
          $this->response['data'][$i]['checkout'] = array(
            'id'  => $checkout['id'],
            'order_number'  => $checkout['order_number'],
            'currency'  => $checkout['currency'],
            'shipping_fee'  => $checkout['shipping_fee']
          );
        }
        $this->response['data'][$i]['date'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y h:i A');
        // $this->response['data'][$i]['merchant_location'] = app($this->locationClass)->getByParams('merchant_id', $key['merchant_id']);
        // $this->response['data'][$i]['location'] = null;
        // if($checkout){
        //   $this->response['data'][$i]['location'] = app($this->locationClass)->getByParams('id', $checkout['id']);
        // }

        unset($this->response['data'][$i]['deleted_at'], $this->response['data'][$i]['updated_at'], $this->response['data'][$i]['created_at']);
        unset($this->response['data'][$i]['history']);
        $i++;
      }
    }
    return $this->response();
  }
}