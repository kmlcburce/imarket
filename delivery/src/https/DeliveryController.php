<?php


namespace Increment\Imarket\Delivery\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Delivery\Models\Delivery;
use Carbon\Carbon;
class DeliveryController extends APIController
{
  function __construct(){
    $this->model = new Delivery();
  }
}
