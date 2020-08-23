<?php

namespace Increment\Imarket\Cart\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
class Checkout extends APIModel
{
    protected $table = 'checkouts';
    protected $fillable = ['code', 'account_id', 'payload', 'coupon_id', 'order_number', 'payment_type', 'payment_payload', 'payment_payload_value', 'sub_total', 'tax', 'discount', 'total', 'status', 'printing_status', 'tendered_amount', 'change', 'currency', 'shipping_fee', 'location_id', 'notes'];
}
