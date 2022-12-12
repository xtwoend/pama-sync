<?php

namespace App\Model;

use App\Model\Hole;
use App\Model\Model;

class Site extends Model
{
    protected $table = 'tbl_sites';

    protected $fillable = [
        'site', 
        'pit', 
        'ewacs_location', 
        'burden', 
        'spacing'
    ];

    public function holes()
    {
        return $this->hasMany(Hole::class, 'site_key');
    }
}