<?php

namespace Increment\Imarket\Shipping\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Shipping\Models\Shipping;
class ShippingController extends APIController
{
  function __construct(){
    $this->model = new Shipping();
  }
}
