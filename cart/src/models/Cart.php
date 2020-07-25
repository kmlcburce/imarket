<?php

namespace Increment\Imarket\Cart\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
class Cart extends APIModel
{
    protected $table = 'carts';
    protected $fillable = ['account_id', 'code', 'items'];
}
