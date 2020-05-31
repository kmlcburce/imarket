<?php

namespace Increment\Imarket\Installment\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Installment\Models\Installment;
class InstallmentController extends APIController
{
 	function __construct(){
 		$this->model = new Installment();
 	}
}
