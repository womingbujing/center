<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckAssoc
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            /* if(!$request->isMethod('post')){
                return response()->json(['code'=>30002,'message'=>'提交方式错误']);die();
            } */
            $clientUser = DB::table('api_client_user')
            ->select('request_domain','client_id')
            ->where('domain_key',$request->domain_key)
            ->first();
            if(!$clientUser){
                return response()->json(['code'=>10002,'message'=>'请求验证失败-域名key不存在']);
            }
            $this->client_user = $clientUser;
            //每次请求 验证签名请求（确定私钥 如 hello ）签名验证规则 如 md5(md5(timestamp.domainkey)) 截取从第9个字符开始取10个
            if (isset($request->timestamp) && isset($request->sign)){
                $md5Sign = md5(md5($request->timestamp.$request->domain_key));
                if (substr($md5Sign, 8,10) != $request->sign){
                    return response()->json(['code'=>10001,'message'=>'签名验证失败-签名不匹配']);
                }
            }else{
                return response()->json(['code'=>10000,'message'=>'非法请求参数-参数不匹配']);
            }
        }catch (\Exception $e){
            Log::error($e->getMessage());
            return response()->json(['code'=>500,'message'=>$e->getMessage()]);
        }
        return $next($request);
    }
}
