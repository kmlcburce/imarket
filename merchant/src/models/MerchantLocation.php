<?php

namespace Increment\Imarket\Merchant\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;

class MerchantLocation extends APIModel
{
    //
    protected $table = 'merchant_locations';
    protected $fillable = ['merchant_id', 'latitude', 'longitude', 'route', 'locality', 'region', 'country'];
}
