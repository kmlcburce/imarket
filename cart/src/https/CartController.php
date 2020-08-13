<?php

namespace Increment\Imarket\Cart\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Cart\Models\Cart;
use Carbon\Carbon;
class CartController extends APIController
{

  function __construct(){
    $this->model = new Cart();
  }

  public function create(Request $request){
    $data = $request->all();
    $this->model = new Cart();

    $hasCart = Cart::where('account_id', '=', $request['account_id'])->first();
    if (sizeof($hasCart) > 0) {
      $updated_data = array(
        'items'  => $data['items'],
        'updated_at'  => Carbon::now()
      );
      Cart::where('code', '=', $hasCart['code'])->update($updated_data);
      $this->response['data'] = true;
    } else {
      $data['code'] = $this->generateCode();
      $this->insertDB($data);
    }
    return $this->response();
  }

  public function generateCode(){
    $code = 'CRT-'.substr(str_shuffle($this->codeSource), 0, 60);
    $codeExist = Cart::where('code', '=', $code)->get();
    if(sizeof($codeExist) > 0){
      $this->generateCode();
    }else{
      return $code;
    }
  }

  public function retrieveByAccountId($accountId) {
    $cart = Cart::where('account_id', '=', $accountId)->where('deleted_at', '=', null)->orderBy('updated_at', 'DESC')->get();
    if (sizeof($cart) > 0) {
      return $cart[0];
    }
  }

  public function getItemsInArray($column, $value){
    $result = Cart::where($column, '=', $value)->get();
    if(sizeof($result) > 0){
      return json_decode($result[0]['items']);
    }else{
      return null;
    }
  }
}
