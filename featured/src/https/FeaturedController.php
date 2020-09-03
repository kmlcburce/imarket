<?php

namespace Increment\Imarket\Featured\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Featured\Models\Featured;
use Increment\Imarket\Product\Models\Product;
use Increment\Imarket\Merchant\Models\Merchant;
use Carbon\Carbon;
use App\Jobs\Notifications;


class FeaturedController extends APIController
{
    //
    function __construct(){
    	$this->model = new Featured();
    }

    public function featuredProduct(Request $request){
        $data = $request->all();
        //check merchant based on code
        $ver = Merchant::select('id')
                ->where('code', '=', $data['code'])
                ->get();
        if (count($ver) == 0){
            return 0;
        }else{
            //check if product and merchant_id entry exists
            $product = Product::select()
                        ->where('merchant_id', '=', $ver[0]['id'])
                        ->where('id', '=', $data['product_id'])
                        ->get();
            if (count($product) == 0){
                return 0;
            }else{
                $this->model = new Featured;
                $this->insertDB($data);
                return $this->response();
            }
        }
    }
}
