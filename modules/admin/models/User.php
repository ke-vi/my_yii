<?php

namespace app\modules\admin\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $user_id
 * @property string $user_name
 * @property string $user_tel
 * @property integer $user_age
 * @property string $user_address
 * @property string $user_nick
 * @property string $password
 * @property string $user_email
 * @property integer $user_sexy
 * @property integer $user_type
 * @property string $token
 * @property string $authority_time
 * @property string $create_time
 * @property string $modify_time
 */
class User extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_tel', 'password','user_name'], 'required'],
            [['user_id', 'user_age', 'user_sexy', 'user_type'], 'integer'],
            [['authority_time', 'create_time', 'modify_time'], 'safe'],
            [['user_name'], 'string', 'max' => 30],
            [['user_tel'], 'string', 'max' => 18],
            [['user_address', 'password'], 'string', 'max' => 100],
            [['user_nick'], 'string', 'max' => 45],
            [['user_email'], 'string', 'max' => 50],
            [['token'], 'string', 'max' => 200]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'user_id' => 'User ID',
            'user_name' => 'User Name',
            'user_tel' => 'User Tel',
            'user_age' => 'User Age',
            'user_address' => 'User Address',
            'user_nick' => 'User Nick',
            'password' => 'Password',
            'user_email' => 'User Email',
            'user_sexy' => 'User Sexy',
            'user_type' => 'User Type',
            'token' => 'Token',
            'authority_time' => 'Authority Time',
            'create_time' => 'Create Time',
            'modify_time' => 'Modify Time',
        ];
    }
}
