<?php
namespace App\Http\models;
use Illuminate\Database\Eloquent\Model;

class users  extends Model{

	    protected $table = 'users';
	    protected $dateFormat ='Y-m-d H:i:s';
        //public $timestamps = false;//此处设置false 表示表中可以不需要created_at updated_at
        protected $connection = 'mysql';
		protected $fillable =['id','user_sn','user_name','phone','mark','address'];
		
	}
?>
