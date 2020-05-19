<?php

namespace Increment\IMarket\Coupon\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\IMarket\Coupon\Models\Coupon;
class CouponController extends APIController
{
   	function __construct(){
   		$this->model = new Coupon();
   	}

   	public function getCoupon($id){
      $result = Coupon::where('id', '=', $id)->get();
      return (sizeof($result) > 0) ? $result[0] : null;
    }
}
