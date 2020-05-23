<?php

namespace Increment\Imarket\Wishlist\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
class Wishlist extends APIModel
{
    protected $table = 'wishlists';
    protected $fillable = ['account_id', 'payload', 'payload_value'];
}
