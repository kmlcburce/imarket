<?php


namespace Increment\Imarket\Product\Http;

use Illuminate\Http\Request;
use App\Http\Controllers\APIController;
use Increment\Imarket\Product\Models\ProductExclusiveLocation;
use Carbon\Carbon;
class ProductExclusiveLocationController extends APIController
{
  function __construct(){
    $this->model = new ProductExclusiveLocation();
  }

  public function getByParams($productId, $location){
    $result = ProductExclusiveLocation::where('product_id', '=', $productId)->where('locality', 'like', $location.'%')->get();
    return sizeof($result) > 0 ? $result[0] : null;
  }
}
