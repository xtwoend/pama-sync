<?php

namespace App\Model;

class BinCapacity extends Model
{
    protected $table = 'tbl_bincapacity';

    protected $fillable = [];
    
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
    // 'tbl_bincapacity';
    
    public function site()
    {
        return $this->belongsTo(Site::class, 'site_id');
    }
}