<?php

namespace Increment\Imarket\Reward\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Reward\Models\Reward;
use Increment\Imarket\Checkout\Models\Checkout;
use Increment\Account\Models\Account;
use Carbon\Carbon;

class FeaturedController extends APIController
{
    //
    function __construct(){
    	$this->model = new Reward();
    }

    public function calculateReward(Request $request){
        $data = $request->all();
        $account_id = Account::select('id')->where('account_code', '=', $data['account_code']);
        if (sizeof($checker) > 0){
            $code = $this->generateCode();
            $total = Checkout::select("total")->where("id", $data["checkout_id"])->get();
            $points = ($total/100);
            $this->model = new Reward();
            $this->insertDB($entry);
            return $this->response();
        }
    }

    public function generateCode(){
        $code = 'rew_'.substr(str_shuffle($this->codeSource), 0, 60);
        $codeExist = Reward::where('code', '=', $code)->get();
        if(sizeof($codeExist) > 0){
          $this->generateCode();
        }else{
          return $code;
        }
    }
    
}
