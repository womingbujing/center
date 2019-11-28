<?php 
namespace App\Repositories;

use App\Repositories\Repository; 
use Illuminate\Support\Facades\DB;
/** 
 * Class Repository 
 * @package App\Repositories\Eloquent 
 */ 
class UsersRepository extends Repository{

    public function getOne($user_sn,$selectRaw="*"){
        return DB::table('users')->selectRaw($selectRaw)->where('user_sn',$user_sn)->first();
    }
    



}