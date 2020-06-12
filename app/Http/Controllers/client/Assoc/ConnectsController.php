<?php
namespace app\Http\Controllers\Assoc;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; 


class ConnectsController extends Controller
{
    private $request;
    private $iv = '1234567890123456'; //AES加密偏移量
    private $aes_code = 'xiaoa';   //AES加密密码
    private $client_user;
    private $check;
    
    public function __construct(Request $request){
        $this->request = $request;
        $this->check();
    }
    
    //还需要的对应关系表  （客户名与域名对应关系表   客户唯一标识、域名、域名唯一key值  ）
    
    //验证请求
    private function check(){
        $this->client_user = DB::table('api_client_user')
        ->select('request_domain','client_id')
        ->where('domain_key',$this->request->domain_key)
        ->first();
		dd(1);
    }
    
    //添加对应关系
    public function add(){
        $ai_user_name = $this->request->user_name;
        $custom_username = $this->request->c_username;
        $domain_key = $this->request->domain_key;
        $password = $this->request->password;   //加密传输  ，验证用户名密码是否匹配
        
        $pwd = openssl_decrypt(base64_decode($password),'aes-128-cbc',$this->aes_code,OPENSSL_RAW_DATA,$this->iv);  //解密成明文
        
        try {
            $url = $this->client_user->request_domain.'/assoc/connects';
            
            $data = json_encode(['user_name'=>$ai_user_name,'password'=>$password]);
            $userReturn = $this->curlPost($url, $data); 
            
            if ($userReturn->code != 0){
                return response()->json($userReturn);
            }
            
            $userInfo = $userReturn->data;
            //判断是否已经存在，根据客户用户名、请求域名、用户名 判断唯一性
            $userExist = DB::table('api_client_user_associate')
                            ->where('custom_username',$custom_username)
                            ->where('domain_key',$domain_key)
                            ->where('ai_user_name',$ai_user_name)
                            ->first();
            if ($userExist){
                return response()->json(['code'=>20003,'message'=>'重复绑定-绑定用户已存在']);
            }
            
            $pwden = base64_encode(openssl_encrypt($pwd,'aes-128-cbc',$this->aes_code,OPENSSL_RAW_DATA,$this->iv));
            //DB::enableQueryLog();
            $result = DB::table('api_client_user_associate')->insert([
                'ai_user_sn'=>$userInfo->user_sn,
                'ai_user_name'=>$ai_user_name,
                'custom_username'=>$custom_username,
                'domain_key'=>$domain_key,
                'ai_password'=>$pwden,
                'client_id'=>$this->client_user->client_id,
                'created_at'=>date('Y-m-d H:i:s'),
            ]);       
            //Log::info(var_dump(DB::getQueryLog()));
            if ($result){
                return response()->json(['code'=>0,'message'=>'绑定成功']);
            }
        }catch (\Exception $e){ //$e->getMessage();  //记录错误日志
            Log::error($e->getFile().'-'.$e->getLine().'-'.$e->getMessage());
            return response()->json(['code'=>500,'message'=>$e->getMessage()]);
        }
    }
    
    //删除对应关系  根据什么规则删除
    public function delete(){
        try {
            $result = DB::table('api_client_user_associate')
                        ->where('ai_user_name',$this->request->user_name)
                        ->where('custom_username',$this->request->c_username)
                        ->where('domain_key',$this->request->domain_key)
                        ->delete();       
            if ($result){
                return response()->json(['code'=>0,'message'=>'绑定关系解除成功']);
            }else{
                return response()->json(['code'=>30001,'message'=>'绑定关系解除失败']);
            }
        }catch (\Exception $e){
            Log::error($e->getMessage());
            return response()->json(['code'=>500,'message'=>$e->getMessage()]);
        }
    }
    
    //修改对应关系，修改密码
    public function update(){
        try {
            $c_username = $this->request->c_username;
            $user_name = $this->request->user_name;
            $domain_key = $this->request->domain_key;
            $old_pwd = openssl_decrypt(base64_decode($this->request->old_pwd),'aes-128-cbc',$this->aes_code,OPENSSL_RAW_DATA,$this->iv);
            $userCon = DB::table('api_client_user_associate')->select('ai_user_sn','ai_password')
                            ->where('custom_username',$c_username)
                            ->where('ai_user_name',$user_name)
                            ->where('domain_key',$domain_key)
                            ->first();
           if ($userCon){
               if ($this->request->old_pwd != $userCon->ai_password){
                   return response()->json(['code'=>20002,'message'=>'密码修改失败-原密码不匹配']);
               }else{
                   $url = $this->client_user->request_domain.'/assoc/connects';
                   $data = json_encode(['user_name'=>$user_name,'password'=>$this->request->new_pwd]);
                   $userReturn = $this->curlPost($url, $data);
                   
                   if ($userReturn->code != 0){
                       return response()->json($userReturn);
                   } 
                   
                   $return = DB::table('api_client_user_associate')
                                ->where('custom_username',$c_username)
                                ->where('ai_user_name',$user_name)
                                ->where('domain_key',$domain_key)
                                ->update(['ai_password'=>$this->request->new_pwd]);
                   if ($return){
                       return response()->json(['code'=>0,'message'=>'密码修改成功']);
                   }
                   
               }
           }else{
               return response()->json(['code'=>20001,'message'=>'不存在的绑定用户']);
           } 
            
        }catch (\Exception $e){
            Log::error($e->getMessage());
            return response()->json(['code'=>500,'message'=>$e->getMessage()]);
        }
    }
    
    //登录 
    public function login(){
        //请求->查询->登录
        $c_username = $this->request->c_username;
        $userInfo = DB::table('api_client_user_associate')->select('ai_password','ai_user_name')->where('client_id',$this->client_user->client_id)->where('custom_username',$c_username)->first();
        
        if(!$userInfo){
            return response()->json(['code'=>10002,'message'=>'登录失败-不存在绑定用户']);
        }
        //登录操作
        
        $token = base64_encode(openssl_encrypt($userInfo->ai_password."||".$userInfo->ai_user_name.'||'.time(),'aes-128-cbc','xiaoa_pwd',OPENSSL_RAW_DATA,$this->iv));
        $url = $this->client_user->request_domain.'/assoc/apilogin?&token='.$token;
        if (isset($this->request->redirect)){
            $url .= '&redirect='.$this->request->redirect;
        }
        
        //调接口设置token有效期
        $urlC = $this->client_user->request_domain.'/assoc/tokencache';
        $data = json_encode(['token'=>$token,'sign'=>md5('xiaoa')]);
        
        $cacheReturn = $this->curlPost($urlC, $data); //var_dump($userReturn);return '444';
        if ($cacheReturn->code == 0){
            return response()->json(['code'=>0,'data'=>['url'=>$url,'period'=>$cacheReturn->data],'message'=>'请求成功']);
        }else{
            Log::info('PA:'.var_export($cacheReturn,true));
            return response()->json(['code'=>500,'message'=>'请求失败']);
        }       
    }
      
    
    public function curlPost($url,$jsonStr){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length: ' . strlen($jsonStr)
        )
            );
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        return $ser_obj = json_decode($response);
        
    }
    
}

