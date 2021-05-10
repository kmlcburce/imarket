<?php


namespace Increment\Imarket\Delivery\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Delivery\Models\DeliveryFee;
use Carbon\Carbon;
class DeliveryFeeController extends APIController
{
    function __construct(){
        $this->model = new DeliveryFee();
    }

    public function generateCode(){
        $code = 'del_'.substr(str_shuffle($this->codeSource), 0, 60);
        $codeExist = DeliveryFee::where('code', '=', $code)->get();
        if(sizeof($codeExist) > 0){
          $this->generateCode();
        }else{
          return $code;
        }
    }

    public function create(Request $request){
        $data = $request->all();
        $data['code'] = $this->generateCode();
        $this->model = new DeliveryFee();
        $this->insertDB($data);
        return $this->response();
    }

    public function retrieve(Request $request){
      $this->rawRequest = $request;
      if($this->checkAuthenticatedUser() == false){
        return $this->response();
      }

      $this->retrieveDB($request->all());
      return $this->response();
    //   public function retrieve(Request $request)
    // {
    //   $this->rawRequest = $request;
    //   $data = $request->all();
    //   if (Cache::has('deliveryfee'.$request['scope'])){
    //     return Cache::get('deliveryfee'.$request['scope']);
    //   }else{
    //     $this->retrieveDB($data);
    //     $lifespan = Carbon::now()->addMinutes(3600);
    //     $keyname = "deliveryfee".$request['scope'];
    //     $charges = DeliveryFee::where('code', '=', $data['code'])->get();
    //     if (sizeof($charges)>0){
    //       Cache::add($keyname, $charges, $lifespan);
    //       return $this->response();
    //     }
    //   }
    }
}

