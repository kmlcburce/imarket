<?php

namespace Increment\Imarket\Featured\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;

class Featured extends APIModel
{
    //
    protected $table = 'featureds';
    protected $fillable = ['product_id', 'start_date', 'end_date'];
}
