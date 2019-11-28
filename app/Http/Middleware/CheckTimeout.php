<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use DB;
class CheckTimeout
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
        $cu_user = Auth::user();
        if (empty($cu_user)){
            if ($request->isMethod('post') ){
                return response()->json(['code'=>500,'msg'=>'登录超时']);
            }else{
                return redirect('/login');
            }
        }else{
            //在线统计记录
            $parent_sn = !empty($cu_user->parent_sn) ? $cu_user->parent_sn : $cu_user->user_name;
            $key = "usersInfo:online:".$parent_sn.':'.$cu_user->user_name;
            $userArr = [];
            /* if (Redis::exists($key)){
                $userJson = Redis::get($key);
                $userArr = json_decode($userJson,true);
                $userArr['last_modify'] = date('Y-m-d H:i:s');
            }else{ */
                $userArr['user_name'] = $cu_user->user_name;
                $userArr['wangwang'] = empty($cu_user->wangwang) ? '/packages/amazeui/assets/img/user.jpeg' : $cu_user->wangwang;
                $userArr['last_modify'] = date('Y-m-d H:i:s');
            //}
            $period = 60*60*2; //有效期两个小时  测试 分钟
            Redis::setex($key,$period,json_encode($userArr));

            $k = "sessList:". $parent_sn . ':' . $cu_user->user_name .':'. substr($request->session()->getId(), 0, 8);
            if(Redis::exists($k)) {
                Redis::setex($k, $period, $request->session()->getId());
            }
        }
        // 对所有的用户输入过滤
        $all = $request->all();
        foreach ($all as $key => $value) {
            $request->offsetSet($key,$this->handleHtmlOfArray($value));
        }
        $request->attributes->add(['user'=>$cu_user]);//添加参数

        return $next($request);

    }

    private function handleHtmlOfArray($data){
        switch(true){
            case is_string($data):
                //return htmlspecialchars($data);
                return str_replace(["'","&","<",">"], ["&#039;","&amp;","&lt;","&gt;"], $data);//替换转译字符串，数组一一对应  //&quot;双引号
                break;
            case is_array($data);
                foreach ($data as $key => $value) {
                    $data[$key] = $this->handleHtmlOfArray($value);
                }
                return $data;
                break;
            case is_object($data);

                foreach ($data as $key => $value) {
                    $data->$key =$this->handleHtmlOfArray($value);
                }
                return $data;
                break;
            default:
                return $data;
                break;
        }
    }
}
