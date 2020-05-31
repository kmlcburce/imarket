<?php

namespace Increment\Imarket\Rental\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Rental\Models\Rental;
class RentalController extends APIController
{
   	function __construct(){
   		$this->model = new Rental();
   	}
}
