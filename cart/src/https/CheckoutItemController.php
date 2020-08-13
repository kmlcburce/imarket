<?php

namespace Increment\Imarket\Cart\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Cart\Models\CheckoutItem;
use Increment\Imarket\Cart\Models\Checkout;
use App\CheckoutTemplate;
use Carbon\Carbon;
class CheckoutItemController extends APIController
{
    public $merchantClass = 'Increment\Imarket\Merchant\Http\MerchantController';
    function __construct(){
    	$this->model = new CheckoutItem();
        $this->notRequired = array(
            'color', 'size'
        );
    }

    public function getByParams($column, $value){
        $result = Checkout::where($column, '=', $value)->get();
        return sizeof($result) > 0 ? $result[0] : null;
    }

    public function insertInArray($array){
        CheckoutItem::insert($array);
    }

    public function getQty($payload, $payloadValue){
        return CheckoutItem::where('payload', '=', $payload)->where('payload_value', '=', $payloadValue)->where('status', '!=', 'cancelled')->sum('qty');
    }
}
