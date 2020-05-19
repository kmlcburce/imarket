<?php


namespace Increment\IMarket\Bundled\Models;
use Illuminate\Database\Eloquent\Model;
use App\APIModel;
class BundledSetting extends APIModel
{
    protected $table = 'bundled_settings';
    protected $fillable = ['bundled', 'product_id', 'qty'];
}
