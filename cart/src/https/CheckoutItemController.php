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
    public $productClass = 'Increment\Imarket\Product\Http\ProductController';
    function __construct(){
    	$this->model = new CheckoutItem();
        $this->notRequired = array(
            'color', 'size'
        );
    }

    public function retrieveOnOrder(Request $request){
        $data = $request->all();
        $this->model = new CheckoutItem();
        $this->retrieveDB($data);
        $result = $this->response['data'];
        if(sizeof($result) > 0){
          $i = 0;
          $array = array();
          foreach ($result as $key) {
            $item = array(
                'id'        => $key['id'],
                'title'     => app($this->productClass)->getByParamsReturnByParam('id', $key['payload_value'], 'title'),
                'qty'       => $key['qty'],
                'price'     => $key['price'],
                'size'      => $key['size'],
                'color'     => $key['color'],
                'status'     => $key['status'],
            );
            $array[] = $item;
            $i++;
          }
          $this->response['data'] = $array;
        }
        return $this->response();
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
