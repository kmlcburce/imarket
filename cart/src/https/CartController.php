<?php

namespace Increment\Imarket\Cart\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Cart\Models\Cart;
class CartController extends APIController
{

  function __construct(){
    $this->model = new Cart();
  }

  public function create(Request $request){
    $data = $request->all();
    $data['code'] = $this->generateCode();
    $this->model = new Cart();
    $this->insertDB($data);
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

}
