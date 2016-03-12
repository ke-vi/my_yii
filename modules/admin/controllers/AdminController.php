<?php

namespace app\modules\admin\controllers;

//use app\mudules\admin\models\LoginForm;
//use app\mudules\admin\models\User;
use app\modules\admin\services\IUserServices;

use yii\web\Controller;

class AdminController extends Controller
{
    public $enableCsrfValidation = false;
    
    private $userService;

    
    public function __construct($id, $module,IUserServices $userService)
    {
        $this->userService = $userService;
        parent::__construct($id, $module);
    }
    
    public function actionIndex()
    {
        if(empty(\Yii::$app->session['user'])){
            return $this->render('login');
        }else {
//            unset(\Yii::$app->session['user']);
            return $this->jsonResult(600, '欢迎来到主页');
        }
        
    }
    public function actionToregister()
    {
        return $this->render('register');
    }
    
    public function actionRegister(){
        
        $request = \Yii::$app->request;
        $userName = $request->post('username');
        $password = md5($request->post("password"));
        $userTel = $request->post('phone_number');
        $email = $request->post('email');
        
        $userData = array(
            "user_name" => $userName,
            "password" => $password,
            "user_tel" => $userTel,
            "user_email" => $email,        
        );
        $user = $this->userService->register($userData);
        
        \Yii::$app->session['user']=$user;
        
        return $this->jsonResult(600, $user);

    }
    
    public function actionLogin(){
        $request = \Yii::$app->request;
        $userName = $request->post('username');
        $password = md5($request->post('password'));
        
        
        //判断是否为空
        
        
        //到数据库中核对登录信息
        $user = $this->userService->getUser($userName, $password);
        if(!empty($user)){
            \Yii::$app->session['user']=$user;
             return $this->jsonResult(600, $user);
        }
        else{
            //返回登录页面
            return $this->render('login');
        }
        
    }
    public function actionLogout(){
        unset(\Yii::$app->session['user']);
        return $this->render('login');
    }
}
