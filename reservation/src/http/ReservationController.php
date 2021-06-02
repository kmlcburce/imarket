<?php

namespace Increment\Imarket\Reservation\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use App\TopChoice;
use Increment\Imarket\Reservation\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class ReservationController extends APIController
{

	public $synqtClass = 'App\Http\Controllers\SynqtController';
    public $merchantClass = 'Increment\Imarket\Merchant\Http\MerchantController';
	public $messengerGroupClass = 'Increment\Messenger\Http\MessengerGroupController';
	public $ratingClass = 'Increment\Common\Rating\Http\RatingController';
	public $topChoiceClass = 'App\Http\Controllers\TopChoiceController';
	public $locationClass = 'Increment\Imarket\Location\Http\LocationController';
	public $emailClass = 'App\Http\Controllers\EmailController';

   	function __construct(){
   		$this->model = new Reservation();
   	}

	public function retrieve(Request $request){
		$data = $request->all();
		$con = $data['condition'];
		if(isset($data['filter'])){
		  $result = Reservation::where($con[0]['column'], $con[0]['clause'], $con[0]['value'])
			->where($con[1]['column'], $con[1]['clause'], $con[1]['value'])
			->where($con[2]['column'], $con[2]['clause'], $con[2]['value'])
			->offset($data['offset'])->limit($data['limit'])->orderBy(array_keys($data['sort'])[0], array_values($data['sort'])[0])->get();
		}else{
			$result = Reservation::where($con[0]['column'], $con[0]['clause'], $con[0]['value'])
				->where($con[1]['column'], $con[1]['clause'], $con[1]['value'])
				->where($con[2]['column'], $con[2]['clause'], $con[2]['value'])
				->offset($data['offset'])->limit($data['limit'])
				->orderBy(array_keys($data['sort'])[0], $data['sort'][array_keys($data['sort'])[0]])
				->get();
		}
		if(sizeof($result) > 0){
			$i = 0;
			foreach ($result as $key) {
				$result[$i]['reservee'] = $this->retrieveNameOnly($result[$i]['account_id']);
				$result[$i]['synqt'] = app($this->synqtClass)->retrieveByParams('id', $result[$i]['payload_value']);
                $result[$i]['merchant'] = app($this->merchantClass)->getByParams('id', $result[$i]['merchant_id']);
				$result[$i]['distance'] = app($this->locationClass)->getLocationDistance('account_id', $result[$i]['merchant']['account_id'], $con[0]['value']);
				$result[$i]['total_super_likes'] = app($this->topChoiceClass)->countByParams('synqt_id', $result[$i]['payload_value'], 'super-like');
				$result[$i]['rating'] = app($this->ratingClass)->getRatingByPayload('merchant_id', $result[$i]['merchant_id']);
				$result[$i]['date_time_at_human'] = Carbon::createFromFormat('Y-m-d H:i:s', $result[$i]['datetime'])->copy()->tz($this->response['timezone'])->format('F j, Y H:i A');
				$result[$i]['members'] = app($this->messengerGroupClass)->getMembersByParams('payload', $result[$i]['payload_value'], ['id', 'title']);
			 $i++;
			}
			$this->response['data'] = $result;
		}
		return $this->response();
	}

	public function create(Request $request){
		$data = $request->all();
		$data['code'] = $this->generateCode();
		$this->model = new Reservation();

		$this->insertDB($data);
		if($this->response['data'] !== null){
			TopChoice::where('synqt_id', '=', $data['payload_value'])->update(array(
				'deleted_at' => Carbon::now()
			));
		}

		return $this->response();

	}

	public function retrieveByParams($whereArray, $returns){
		$result = Reservation::where($whereArray)->get($returns);
		return sizeof($result) > 0 ? $result[0] : null;
	}

	public function generateCode(){
		$code = 'res_'.substr(str_shuffle($this->codeSource), 0, 60);
		$codeExist = Reservation::where('code', '=', $code)->get();
		if(sizeof($codeExist) > 0){
		  $this->generateCode();
		}else{
		  return $code;
		}
	}

	public function update(Request $request){
		$data = $request->all();
		$emailData = array();
		$reservation = Reservation::where('id', '=', $data['id'])->get();
		$noOfGuests = app($this->messengerGroupClass)->getMembersByParams('payload', $reservation[0]['payload_value'], ['id', 'title']);
		$emailData = array(
			'reservee' => $this->retrieveNameOnly($reservation[0]['account_id']),
			'date' =>  Carbon::createFromFormat('Y-m-d H:i:s', $reservation[0]['datetime'])->copy()->tz($this->response['timezone'])->format('F j, Y H:i A'),
			'number_of_guests' => $noOfGuests !== null ? sizeof($noOfGuests) : 0,
			'merchant' => app($this->merchantClass)->getByParams('id', $reservation[0]['merchant_id']),
			'code' => $reservation[0]['code'],
			'status' => ucfirst($data['status'])
		);
		$email = app($this->emailClass)->receipSynqt($emailData, $reservation[0]['account_id']);
		if($email !== null){
			$result = Reservation::where('id', '=', $data['id'])->update(array(
				'status' => $data['status']
			));
			$this->response['data'] = $result;
		}
		return $this->response();
	}
}
