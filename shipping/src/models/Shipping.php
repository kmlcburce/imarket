<?php

namespace Increment\Imarket\Shipping\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
class Shipping extends APIModel
{
    protected $table = 'shippings';
    protected $fillable = ['checkout_id', 'currency', 'amount'];
}
