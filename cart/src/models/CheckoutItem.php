<?php

namespace Increment\Imarket\Cart\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
class CheckoutItem extends APIModel
{
    protected $table = 'checkout_items';
    protected $fillable = ['checkout_id', 'account_id', 'payload', 'payload_value', 'product_attribute_id', 'qty', 'price', 'status'];
}
