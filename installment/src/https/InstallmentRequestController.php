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
 	
  function __construct(){
 		$this->model = new InstallmentRequest();

    $this->notRequired = array(
      'size', 'color', 'status'
    );
 	}

  public function getByParams($column, $value){
    $result = InstallmentRequest::where($column, '=', $value)->get();

    if(sizeof($result) > 0){
      $result[0]['installment'] = app($this->installmentClass)->getByParams('product_id', $result[0]['product_id']);
      $result[0]['product']     = app($this->productClass)->getProductByParamsInstallment('id', $result[0]['product_id']);
      $result[0]['created_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[0]['created_at'])->copy()->tz($this->response['timezone'])->format('F j, Y H:i A');
      return $result;
    }

    return null;
  }
}
