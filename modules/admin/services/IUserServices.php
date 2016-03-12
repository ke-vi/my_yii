<?php

namespace app\modules\admin\services;

use app\modules\admin\models\User;
use yii\base\Exception;

interface IUserServices
{
    public function register($userdata);
    public function getUser($userName,$password);

}

class UserServices implements IUserServices
{

    public function register($userdata){

        $user = new User();
        foreach ($userdata as $key=>$value){
            if($value===0||!empty($value)) $user->$key = $value;
        }
       if(!$user->validate())throw new Exception("缺少必要的参数。");

       $user->create_time = date("Y-m-d H:i:s");
       $user->user_type = 2;
//        return $user;
       $user->save();
        $user = $user->find()->asArray()->where("user_id = {$user->user_id}")->one();
        return $user;   
    }

    public function getUser($userName, $password) {
        $user = new User();
        $user = $user->find()->asArray()->where(['user_name' => $userName,'password'=>$password])->one();
    
        return $user;
    }

}