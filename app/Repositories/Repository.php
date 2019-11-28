<?php 
namespace App\Repositories;

use DB;
/** 
 * 此仓库主要针对分表的情况
 * Class Repository 
 * @package App\Repositories\Eloquent 
 */ 

class Repository{ 
    
   

    /** 
     * @return Model 
     * @throws RepositoryException 
     */ 
    public function makeModel($table) { 
        return DB::table($table);
    } 
    
    
    //判断表是否存在
    public function checkTableExist($table){
        $isexist_tables = DB::table('pg_tables')->select('tablename')->where('schemaname','public')->where('tablename',$table)->first();
        if (!empty($isexist_tables)){
            return true;
        }else{
            return false;
        }
    }
    
    
} 