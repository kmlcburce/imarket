<?php


namespace Increment\Imarket\Delivery\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Delivery\Models\Delivery;
use Carbon\Carbon;
use App\Jobs\Notifications;
use Illuminate\Support\Facades\DB;
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
    $data['amount'] = $data['delivery_fee'] + $this->getPerformanceBonus($data["total"]) + 15;
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

  public function getPerformanceBonus($total){
    if ($total > 0 && $total < 300){
      return 0;
    }else if ($total >= 300 && $total < 1000){
      return (($total*0.20)*0.40)-15;
    }else if ($total >= 1000 && $total < 2000){
      return (($total*0.20)*0.35)-15;
    }else if ($total >= 2000 && $total < 3000){
      return (($total*0.20)*0.30)-15;
    }else if ($total >= 3000 && $total < 4000){
      return (($total*0.20)*0.25)-15;
    }else if ($total >= 4000 && $total < 5000){
      return (($total*0.20)*0.20)-15;
    }
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

  public function dailySummary(Request $request){
    $data = $request->all();
    $results = Delivery::where('created_at', '>=', $data['date'].' 00:00:00')
                    ->where('created_at', '<=', $data['date'].' 23:59:59')
                    ->where('rider', '=', $data['rider'])
                    ->groupBy('status')
                    ->get(array(
                        DB::raw('SUM(amount) as `total`'),
                        'status'
                    ));

    $this->response['data'] = $results;
    return $this->response();;
  }

  public function monthlySummary(Request $request){
    $data = $request->all();
    $results = Delivery::where('created_at', '>=', $data['date'].'-01')
                    ->where('created_at', '<=', $data['date'].'-31')
                    ->where('rider', '=', $data['rider'])
                    ->groupBy('date', 'status')
                    ->orderBy('date', 'ASC') // or ASC
                    ->get(array(
                        DB::raw('DATE(`created_at`) AS `date`'),
                        DB::raw('SUM(amount) as `total`'),
                        'status'
                    ));

    $completedSeries = array();
    $cancelledSeries = array();
    $categories = array();

    $numberOfDays = Carbon::createFromFormat('Y-m-d', $data['date'].'-01')->daysInMonth;
    for ($i = 1; $i <= $numberOfDays; $i++) {
      $completedSeries[] = 0;
      $cancelledSeries[] = 0;
      $categories[] = $i;
    }

    foreach ($results as $key) {
      $index = intval(substr($key->date, 8)) - 1;
      // echo $key->date.'/'.$index;
      if($key->status == 'completed'){
        $completedSeries[$index] = $key->total;
      }else if($key->status == 'cancelled'){
        $cancelledSeries[$index] = $key->total;
      }
    }

    $this->response['data'] = array(
      'series' => array(array(
        'name'  => 'Completed',
        'data'  => $completedSeries
      ), array(
        'name'  => 'Cancelled',
        'data'  => $cancelledSeries
      )),
      'categories' => $categories
    );
    return $this->response();;
  }

  public function getRiderId($column, $value) {
    $result = Delivery::where($column, '=', $value)->get();
    if(sizeof($result) > 0){
      return $result[0]['rider'];
    }
    return null;
  }

  public function getDeliveryName($column, $value){
    $result = Delivery::where($column, '=', $value)->get();
    if(sizeof($result) > 0){
      return $this->retrieveNameOnly($result[0]['rider']);
    }
    return null;
  }

  public function getDeliveryFee(Request $request){
    $data = $request->all();
    $distance = app('Increment\Imarket\Location\Http\LocationController')->getLongLatDistance($data['latFrom'], $data['longFrom'], $data['latTo'], $data['longTo']);
    $distanceCalc = intdiv($distance, 1);
    return ($distanceCalc * 10) + 8;
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