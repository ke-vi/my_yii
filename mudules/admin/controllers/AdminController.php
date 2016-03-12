<?php

namespace app\mudules\admin\controllers;

use yii\web\Controller;

class AdminController extends Controller
{
    
    public function actionIndex()
    {
        return $this->render('login');
    }
    public function actionRegister()
    {
        return $this->render('register');
    }
}
