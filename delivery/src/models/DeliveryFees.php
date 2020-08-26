<?php


namespace Increment\Imarket\Delivery\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
class DeliveryFees extends APIModel
{
    protected $table = 'delivery_fees';
    protected $fillable = ['code', 'scope', 'minimum_charge', 'minimum_distance', 'addition_charge_per_distance','currency'];
}


