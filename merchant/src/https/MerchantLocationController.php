<?php

namespace Increment\Imarket\Merchant\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Merchant\Models\MerchantLocation;
use Carbon\Carbon;

class MerchantLocationController extends APIController
{
    //
    function __construct(){
        $this->model = new MerchantLocation();
        // $this->notRequired = array(
        //     'name', 'address', 'prefix', 'logo', 'website', 'email'
        // );
      }

    public function create(Request $request){
        $data = $request->all();
        return $data;
        $this->model = new MerchantLocation();
        $this->insertDB($data);
        return $this->response();
    }

    public function retrieve(Request $request){
        $data = $request->all();
        $this->model = new MerchantLocation();
        $this->retrieveDB($data);
        $result = $this->response['data'];
        return $result;
    }
    
}
