<?php

namespace App\Models\Legacy;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GenBanco extends Model
{
    protected $connection = 'legacy';
    protected $table = 'gen_banco';
    protected $primaryKey = 'cc_banco';
    public $timestamps = false;

    protected $guarded = [];

    public function cuentas(): HasMany
    {
        return $this->hasMany(GenCuenta::class, 'cc_banco', 'cc_banco');
    }
}
