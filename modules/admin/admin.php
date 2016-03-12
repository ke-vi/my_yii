<?php

namespace app\modules\admin;

class admin extends \yii\base\Module
{
    public $controllerNamespace = 'app\modules\admin\controllers';

    public function init()
    {
        parent::init();
        // custom initialization code goes here
        $this->SetContainer([
            'app\modules\admin\services\IUserServices'=>'app\modules\admin\services\UserServices',

        ]);
    }

    private function SetContainer($relation)
    {
        foreach ($relation as $key => $value)
        {
            \Yii::$container->set($key,$value);
        }
    }
}
