<?php

namespace Increment\Imarket\Location\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Location\Models\Location;
use Carbon\Carbon;

class LocationController extends APIController
{
    //
    function __construct(){
      $this->model = new Location();
      // $this->notRequired = array(
      //     'name', 'address', 'prefix', 'logo', 'website', 'email'
      // );
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

    public function getLongLatDistance($latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371){
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
      $a = pow(cos($latTo) * sin($lonDelta), 2) +
        pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
      $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
    
      $angle = atan2(sqrt($a), $b);
      return $angle * $earthRadius;
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

    public function getAppenedLocationByParams($column, $value, $merchantId){
      $distance = $this->getDistanceFromMerchant($merchantId, $value);
      $result = Location::where($column, '=', $value)->get();
      return sizeof($result) > 0 ? '('.$distance.'km)'.$result[0]['route'].', '.$result[0]['locality']: null;
    }
}
