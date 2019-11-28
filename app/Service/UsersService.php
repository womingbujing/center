<?php
namespace App\Service;
use App\Http\Controllers\UiddController;
use DB;
use App\Repositories\UsersRepository;

class UsersService
{
    private $UsersRepository;
    public function __construct(UsersRepository $UsersRepository){
        $this->UsersRepository = $UsersRepository;
    }

    /**
     * 根据用户名获取信息
     * @param $username
     * @param $fields
     * @return mixed
     */
    public function getUser($username,$fields="*"){
        return $this->UsersRepository->getOne($username,$fields);
    }

}

