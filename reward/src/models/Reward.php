<?php

namespace Increment\Imarket\Reward\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
use Carbon\Carbon;
class Reward extends APIModel
{
    protected $table = 'rewards';
    protected $fillable = ['code', 'account_code', 'account_id,', 'checkout_id', 'points'];
}
