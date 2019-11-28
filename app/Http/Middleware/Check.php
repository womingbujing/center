<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\models\users;
use Illuminate\Support\Facades\Log;
class Check
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

        }catch (\Exception $e){
            Log::info($e->getFile().'-'.$e->getLine().'-'.$e->getMessage());
            return response()->json(array('code'=> 500,'msg'=>$e->getMessage().'-'.$e->getLine()));
        }
        return $next($request);
    }
}
