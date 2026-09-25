<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;

class FinComprobante extends Model
{
    protected $connection = 'legacy';
    protected $table = 'fin_comprobante';
    protected $primaryKey = 'cc_comprobante';
    public $timestamps = false;

    protected $guarded = [];
}
