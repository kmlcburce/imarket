<?php

namespace Increment\Imarket\Shipping\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Shipping\Models\ShippingRate;
class ShippingRateController extends APIController
{
  function __construct(){
    $this->model = new ShippingRate();
  }
}
