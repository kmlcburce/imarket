<?php

namespace Increment\Imarket\Installment\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
use Carbon\Carbon;
class Installment extends APIModel
{
    protected $table = 'installments';
    protected $fillable = ['product_id', 'merchant_id', 'months', 'interest', 'requirements'];
}
