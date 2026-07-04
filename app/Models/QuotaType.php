<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotaType extends Model
{   
    use HasFactory;
    protected $connection = 'adms_jshb';
    protected $table = 'quota_types';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'code',
    ];

    public function schemeQuotas()
    {
        return $this->hasMany(SchemeUnitQuota::class, 'quota_type_id');
    }
}