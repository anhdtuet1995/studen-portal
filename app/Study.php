<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;
class Study extends Model
{
    //
    public function user(){
    	return $this->belongsTo('App\User');
    }

    public function setDateAttribute($value)
   {
       $this->attributes['publication_date'] = Carbon::createFromFormat('d/m/Y', $value);
   }
}
