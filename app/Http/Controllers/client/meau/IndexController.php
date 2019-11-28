<?php

namespace App\Http\Controllers\client\meau;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Excel;
use App\Service\UsersService;

class IndexController extends Controller
{
    /**
     * 为指定用户显示详情
     *
     * @param int $id
     * @return Response
     */
    public function index(UsersService $usersService)
    {
        echo env('Host');
       dd($usersService->getUser('2222'));

    }

}