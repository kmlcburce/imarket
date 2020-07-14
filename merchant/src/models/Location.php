<?php

namespace Increment\Imarket\Merchant\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;

class Location extends APIModel
{
    //
    protected $table = 'locations';
    protected $fillable = ['account_id', 'latitude', 'longitude', 'route', 'locality', 'region', 'country'];
}
