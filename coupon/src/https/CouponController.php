<?php

namespace Increment\Imarket\Coupon\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Coupon\Models\Coupon;
class CouponController extends APIController
{
   	function __construct(){
   		$this->model = new Coupon();
   		$this->notRequired = array('scope');
   	}

   	public function getCoupon($id){
      $result = Coupon::where('id', '=', $id)->get();
      return (sizeof($result) > 0) ? $result[0] : null;
    }
}
