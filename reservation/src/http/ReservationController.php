<?php

namespace Increment\Imarket\Reservation\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Reservation\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class ReservationController extends APIController
{

	public $synqtClass = 'App\Http\Controllers\SynqtController';
    public $merchantClass = 'Increment\Imarket\Merchant\Http\MerchantController';

   	function __construct(){
   		$this->model = new Reservation();
   	}

	public function retrieve(Request $request){
		$data = $request->all();
		$con = $data['condition'];
		$result = Reservation::where($con[0]['column'], $con[0]['clause'], $con[0]['value'])->offset($data['offset'])->limit($data['limit'])->get();
		if(sizeof($result) > 0){
			$i = 0;
			foreach ($result as $key) {
				$result[$i]['reservee'] = $this->retrieveNameOnly($result[$i]['account_id']);
				$result[$i]['synqt'] = app($this->synqtClass)->retrieveByParams('id', $result[$i]['payload_value']);
                $result[$i]['merchant'] = app($this->merchantClass)->getByParams('id', $result[$i]['merchant_id']);

			 $i++;
			}
			$this->response['data'] = $result;
		}
		return $this->response();
	}
}
