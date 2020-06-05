<?php

namespace Increment\Imarket\Installment\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
use Carbon\Carbon;
class InstallmentRequest extends APIModel
{
    protected $table = 'installment_requests';
    protected $fillable = ['product_id', 'merchant_id', 'account_id', 'size', 'color', 'qty', 'status'];
}
