<?php

namespace Increment\Imarket\Installment\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Installment\Models\InstallmentRequest;
use Carbon\Carbon;
class InstallmentRequestController extends APIController
{
  public $installmentClass = 'Increment\Imarket\Installment\Http\InstallmentController';
  public $productClass = 'Increment\Imarket\Product\Http\ProductController';
  public $notificationClass = 'Increment\Common\Notification\Http\NotificationController';
  public $merchantController = 'Increment\Imarket\Merchant\Http\MerchantController';
  public $messengerCustom = 'App\Http\Controllers\MessengerGroupController';
 	
  function __construct(){
 		$this->model = new InstallmentRequest();

    $this->notRequired = array(
      'size', 'color', 'status'
    );
 	}

  public function create(Request $request){
    $data = $request->all();
    $data['code'] = $this->generateCode();
    $this->insertDB($data);
    $merchant = app($this->merchantController)->getByParams('id', $data['merchant_id']);
    if($this->response['data'] > 0 && $merchant != null){
      $parameter = array(
        'to' => $merchant['account_id'],
        'from' => $data['account_id'],
        'payload' => 'installmentRequests',
        'payload_value' => $data['code'],
        'route' => '/installments',
        'created_at' => Carbon::now()
      );
      app($this->notificationClass)->createByParams($parameter);
    }
    return $this->response();
  }


  public function retrieve(Request $request){
    $data = $request->all();
    $this->retrieveDB($data);
    $result = $this->response['data'];
    if(sizeof($result) > 0){
      $i = 0;
      foreach ($result as $key => $value) {
        $result[$i]['installment'] = app($this->installmentClass)->getByParams('product_id', $result[$i]['product_id']);
        $result[$i]['account']     = $this->retrieveAccountDetails($result[$i]['account_id']);
        $result[$i]['product']     = app($this->productClass)->getProductByParamsInstallment('id', $result[$i]['product_id']);
        $result[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y H:i A');
        $result[$i]['thread']      = app($this->messengerCustom)->getByParamsTwoColumns('payload', 'installment', 'title', $result[$i]['code']);
        $i++;
      }
    }
    $this->response['data'] = $result;
    return $this->response();
  }

  public function generateCode(){
    $code = 'INS-'.substr(str_shuffle($this->codeSource), 0, 60);
    $codeExist = InstallmentRequest::where('code', '=', $code)->get();
    if(sizeof($codeExist) > 0){
      $this->generateCode();
    }else{
      return $code;
    }
  }

  public function getByParams($column, $value){
    $result = InstallmentRequest::where($column, '=', $value)->where('status', '=', 'pending')->orderBy('created_at', 'desc')->get();

    if(sizeof($result) > 0){
      $i = 0;
      foreach ($result as $key => $value) {
        $result[$i]['installment'] = app($this->installmentClass)->getByParams('product_id', $result[$i]['product_id']);
        $result[$i]['product']      = app($this->productClass)->getProductByParamsInstallment('id', $result[$i]['product_id']);
        $result[$i]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y H:i A');
        $i++;
      }
      return $result;
    }

    return null;
  }
}
