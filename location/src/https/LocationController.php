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
}
