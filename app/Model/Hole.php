<?php

namespace App\Model;

class Hole extends Model
{
    protected $table = 'tbl_chargingreport';

    protected $fillable = [
        'site_id', 
        'site_key',
        'truck_id',
        'hole_code', 
        'deep',
        'volume', 
        'condition', 
        'charge_started_at', 
        'charge_finished_at', 
        'charge_duration', 
        'an_weight', 
        'fo_weight', 
        'plan_weight',
        'actual_weight', 
        'stemming_height', 
        'an_leftover', 
        'fo_leftover'
    ];
    
    /**
     * ,[site_id]
      ,[hole_code]
      ,[deep]
      ,[charge_started_at]
      ,[charge_finished_at]
      ,[charge_duration]
      ,[an_weight]
      ,[fo_weight]
      ,[plan_weight]
      ,[actual_weight]
      ,[stemming_height]
      ,[an_leftover]
      ,[fo_leftover]
      ,[plan_pf]
      ,[actual_pf]
     */
    // 'tbl_downtimereport';
    // 'tbl_p2h_pemeriksaan';
    
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_key');
    }
}