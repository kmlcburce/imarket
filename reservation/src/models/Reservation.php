<?php

namespace Increment\Imarket\Rental\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
use Carbon\Carbon;
class Reservation extends APIModel
{
    protected $table = 'reservations';
    protected $fillable = ['account_id', 'merchant_id', 'payload', 'payload_value', 'details', 'datetime', 'status'];
}
