<?php

namespace Increment\Imarket\Installment\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Installment\Models\InstallmentRequest;
class InstallmentRequestController extends APIController
{
 	function __construct(){
 		$this->model = new InstallmentRequest();

    $this->notRequired = array(
      'size', 'color'
    );
 	}
}
