<?php


namespace Increment\Imarket\Delivery\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Delivery\Models\DeliveryFees;
use Carbon\Carbon;
class DeliveryController extends APIController
{
    function __construct(){
        $this->model = new DeliveryFees();
        // $this->notRequired = array(
        //     'name', 'address', 'prefix', 'logo', 'website', 'email'
        // );
    }

    public function generateCode(){
        $code = 'del_'.substr(str_shuffle($this->codeSource), 0, 60);
        $codeExist = DeliveryFees::where('code', '=', $code)->get();
        if(sizeof($codeExist) > 0){
          $this->generateCode();
        }else{
          return $code;
        }
    }

    public function create(Request $request){
        $data = $request->all();
        $data['code'] = $this->generateCode();
        $this->model = new DeliveryFees();
        $this->insertDB($data);
        return $this->response();
    }
}
