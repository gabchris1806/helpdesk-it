<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterLapor extends Model
{
    protected $fillable = ['nik', 'nama', 'email', 'no_hp'];

    public function user()
    {
        return $this->hasOne(User::class, 'master_lapors_id');
    }
    //
}
