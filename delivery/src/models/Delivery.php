<?php


namespace Increment\Imarket\Delivery\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
class Delivery extends APIModel
{
    protected $table = 'deliveries';
    protected $fillable = ['code', 'account_id', 'checkout_id', 'merchant_id', 'rider','history', 'status'];
}





