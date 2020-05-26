<?php

namespace Increment\Imarket\Shipping\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
class ShippingRate extends APIModel
{
    protected $table = 'shipping_rates';
    protected $fillable = ['merchant_id', 'locality', 'region', 'country', 'currency', 'amount'];
}
