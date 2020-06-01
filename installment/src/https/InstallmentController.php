<?php

namespace Increment\Imarket\Installment\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Installment\Models\Installment;
class InstallmentController extends APIController
{
 	function __construct(){
 		$this->model = new Installment();

    $this->notRequired = array(
      'product_id', 'merchant_id'
    );
 	}

  public function getByParams($column, $value){
    $result = Installment::where($column, '=', $value)->get();
    return (sizeof($result) > 0) ? $result[0] : null;
  }
}
