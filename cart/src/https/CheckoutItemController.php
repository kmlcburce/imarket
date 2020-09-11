<?php

namespace Increment\Imarket\Cart\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Cart\Models\CheckoutItem;
use Increment\Imarket\Cart\Models\Checkout;
use App\CheckoutTemplate;
use Illuminate\Support\Facades\DB;
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

    public function summaryOfInventory(Request $request){
        $data = $request->all();
        $results = CheckoutItem::where('created_at', '>=', $data['date'].'-01')
                    ->where('created_at', '<=', $data['date'].'-31')
<<<<<<< HEAD
                    ->where('merchant_id', '=', $data['merchant_id'])
                    ->groupBy('date' , 'status')
                    ->orderBy('date' , 'ASC')
                    ->get(array(
                      DB::raw('DATE(`created_at`) AS `date`'),
                    ));
=======
                    ->where('account_id', '=', $data['merchant_id'])
                    ->orderBy('created_at' , 'ASC')
                    ->get();
>>>>>>> e628a7d2f5dfc0cf88899b56a43cb0d6b1475789
        $this->response['data'] = $results;
        //get number of checkouts on the spec date
        $results1 = Checkout::where('created_at', '>=', $data['date'].'-01')
                    ->where('created_at', '<=', $data['date'].'-31')
                    ->where('merchant_id', '=', $data['merchant_id'])
                    ->orderBy('created_at', 'ASC')
                    ->get();
        $last = 0;
        for ($i=0; $i <= count($results1)-1; $i++){
            $products = [];
            for ($x=0; $x <=count($results)-2; $x++){
                $productitems = [];
                if ($results1[$i]['id'] == $results[$last]['checkout_id']){
                    $last = $last + 1;
                    array_push($productitems, $results[$last]);
                }else{
                    $last = $x;
                    break;
                }
                array_push($products, $productitems);
            }
            $results1[$i]["products"] = $products;
        }
        return $results1;
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
