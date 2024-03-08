<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    use HasFactory;

    protected  $table='user_wallet_master';

    protected $fillable =['user_id','wallet_amt'];
}
