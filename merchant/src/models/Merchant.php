<?php

namespace Increment\Imarket\Merchant\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
class Merchant extends APIModel
{
    protected $table = 'merchants';
    protected $fillable = ['code', 'account_id', 'name', 'prefix', 'logo', 'address', 'status'];
}
