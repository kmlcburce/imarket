<?php

namespace Increment\Imarket\Location\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Cart\Models\Checkout;
use Illuminate\Support\Facades\DB;
use Increment\Imarket\Location\Models\Location;
use Carbon\Carbon;

class LocationController extends APIController
{
    //
    function __construct(){
      $this->model = new Location();
      $this->notRequired = array(
          'code', 'merchant_id'
      );
    }
    
    public function addLocationScope(Request $request){
      $locations = $this->getLocationScope($request);
      if (in_array($request["location_code"], $locations)){
        return json_encode((object)["message" => "Location code exists"]);
      }else{
        array_push($locations, $request["location_code"]);
        $updated = "";
        for($i = 0; $i < count($locations); $i++){
          $updated = $updated . $locations[$i];
          if( $i != count($locations)-1){
            $updated = $updated . ",";
          }
        }
        $response = Location::where("id", $request["location_id"])
        ->update(["code" => $updated]);
        return $response;
      }
    }

    //Broadcasting for Rider remaining distance
    public function getRemainingDistance(Request $request){
      $resultval = [];
      $data = $request->all();
      $user = DB::table('checkouts')->select('location_id')->where('id','=', $data['checkout_id'])->get();
      $result = json_decode($user, true);
      $merchant = Location::select('latitude','longitude')->where('merchant_id','=', $data['merchant_id'])->get();
      $location = Location::select('latitude', 'longitude')->where('id', '=', $result[0]['location_id'])->get();
      $resultval['user_distance'] = $this->getLongLatDistance($location[0]['latitude'], $location[0]['longitude'], $data['latitude'], $data['longitude']);
      $resultval['merchant_distance'] = $this->getLongLatDistance($merchant[0]['latitude'], $merchant[0]['longitude'], $data['latitude'], $data['longitude']);
      $this->response['data'] = $resultval;
      return $this->response();
    }

    public function getLongLatDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371){
      
      //$latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371
      if (is_null($latitudeFrom) || is_null($longitudeFrom) || is_null($latitudeTo) || is_null($longitudeTo)) {
        return null;
      }
      $latitudeFrom = floatval($latitudeFrom);
      $longitudeFrom = floatval($longitudeFrom);
      $latitudeTo = floatval($latitudeTo);
      $longitudeTo = floatval($longitudeTo);
      // convert from degrees to radians
      $latFrom = deg2rad($latitudeFrom);
      $lonFrom = deg2rad($longitudeFrom);
      $latTo = deg2rad($latitudeTo);
      $lonTo = deg2rad($longitudeTo);
      $lonDelta = $lonTo - $lonFrom;
      $a = pow(cos($latTo) * sin($lonDelta), 2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
      $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
      $angle = atan2(sqrt($a), $b);
      return $angle * $earthRadius;
    }

    public function getDistance($from, $to, $earthRadius = 6371) {
      $latitudeFrom = $from['latitude'];
      $longitudeFrom = $from['longitude'];
      $latitudeTo = $to['latitude'];
      $longitudeTo = $to['longitude'];
      $latitudeFrom = floatval($latitudeFrom);
      $longitudeFrom = floatval($longitudeFrom);
      $latitudeTo = floatval($latitudeTo);
      $longitudeTo = floatval($longitudeTo);
      // convert from degrees to radians
      $latFrom = deg2rad($latitudeFrom);
      $lonFrom = deg2rad($longitudeFrom);
      $latTo = deg2rad($latitudeTo);
      $lonTo = deg2rad($longitudeTo);
      $lonDelta = $lonTo - $lonFrom;
      $a = pow(cos($latTo) * sin($lonDelta), 2) + pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
      $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
      $angle = atan2(sqrt($a), $b);
      return number_format($angle * $earthRadius, 2);
    }

    public function getDistanceFromMerchant($merchantId, $id){
      $from = $this->getByParams('merchant_id', $merchantId);
      $to = $this->getByParams('id', $id);
      if($from && $to){
        return $this->getLongLatDistance($from['latitude'], $from['longitude'], $to['latitude'], $to['longitude']);
      }
      return null;
    }

    public function getLocationScope(Request $request){
      $scope = Location::select("code")
      ->where("id", $request["location_id"])
      ->get();
      if (count($scope) == 0){
        return [];
      }
      $scope_array = explode(',',$scope[0]["code"]);
      return $scope_array;
    }


    public function getByParams($column, $value){
      $result = Location::where($column, '=', $value)->get();
      return sizeof($result) > 0 ? $result[0] : null;
    }

    public function getByParamsWithCode($column, $value){
      $result = Location::where($column, '=', $value)->where('code', '!=', null)->limit(1)->get();
      return sizeof($result) > 0 ? $result[0] : null;
    }

    public function getByParamsWithCodeScope($column, $value){
      $result = Location::select('id')->where($column, '=', $value)->where('code', '!=', null)->limit(1)->get();
      return sizeof($result) > 0 ? $result[0] : null;
    }


    public function getColumnValueByParams($column, $value, $returnColumn){
      $result = Location::where($column, '=', $value)->get();
      return sizeof($result) > 0 ? $result[0][$returnColumn] : null;
    }

    public function getCodeByLocalityAndCountry($id){
      $result = Location::where('id', '=', $id)->get();
      if(sizeof($result) > 0){
        $location = Location::where('locality', 'like', '%'.$result[0]['locality'].'%')->where('country', 'like', '%'.$result[0]['country'].'%')->where('code', '!=', null)->limit(1)->get();
        return sizeof($location) > 0 ? $location[0]['code'] : null;
      }else{
        return null;
      }
    }

    public function getAppenedLocationByParams($column, $value, $merchantId){
      $from = $this->getByParams('merchant_id', $merchantId);
      $to = $this->getByParams($column, $value);
      $distance = null;
      if($to){
        if($from){
          $distance = $this->getLongLatDistance($from['latitude'], $from['longitude'], $to['latitude'], $to['longitude']);
          $distance = round($distance, 1);
        }
        return '('.$distance.'km)'.$to['route'].', '.$to['locality'];
      }else{
        return null;
      }
    }

    public function getLocationDistance($column, $value, $accountId){
      $from = $this->getByParams('account_id', $accountId);
      $to = $this->getByParams($column, $value);
      $distance = null;
      if($to){
        if($from){
          $distance = $this->getLongLatDistance($from['latitude'], $from['longitude'], $to['latitude'], $to['longitude']);
          $distance = round($distance, 1);
        }
        return $distance.'km';
      }else{
        return null;
      }
    }

    public function getLocationDistanceByMerchant($from, $to){
      $distance = null;
      if($to){
        if($from){
          $distance = $this->getLongLatDistance($from->latitude, $from->longitude, $to->latitude, $to->longitude);
          $distance = round($distance, 1);
        }
        return $distance.'km';
      }else{
        return null;
      }
    }

    public function getAndManageLocation($column, $value, $merchantId){
      $from = $this->getByParams('merchant_id', $merchantId);
      $to = $this->getByParams($column, $value);
      $distance = 0;

      if($to){

        if($from){
          $distance = $this->getLongLatDistance($from['latitude'], $from['longitude'], $to['latitude'], $to['longitude']);
          $distance = round($distance, 1);
        }

      }
      return array(
        'merchant_location' => $from,
        'location'          => $to,
        'distance'          => $distance.' km',
      );
    }

    public function getLocation(Request $request){
      $data = $request->all();
      Location::where('account_id', '=', $data['account_id'])
              ->update(array(
                'code' => $data['code'],
                'updated_at' => Carbon::now()
              ));
      if($this->response['error'] == []){
        $this->response['data'] = 'true';
      };
      return $this->response();
    }

    public function getAllLocation(){
      return Location::get();
    }
}
