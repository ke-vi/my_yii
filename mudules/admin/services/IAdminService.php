<?php


namespace app\modules\admin\services;
use app\modules\core\models\User;
use app\modules\api\models\MicroblogComment;
use app\modules\admin\models\AdminRecommendUser;
use app\modules\api\models\Friend;
use yii\base\Exception;
use app\modules\api\models\GroupInfo;
use app\modules\api\models\GroupRela;



interface IAdminService
{
    public function getUsers($params,$username,$pageNo,$pageSize);
    public function addUser($userdata);
    public function updateUser($userdata);
    public function deleteUser($userId);
    public function getMicroblogComment($conditions,$pageNo,$pageSize);
    public function deleteMicroblogComment($comId);
    public function getRecommendList();
    public function deleteRecommend($id);
    public function updateRecommend($id,$arr);
    public function addRecommend($userId);
    public function recallfr();
    public function getGroups($pageNo,$pageSize,$searchValue);
    public function getGroupMembers($pageNo,$pageSize,$searchValue,$groupId);
    public function searchUserForGroup($pageNo, $pageSize,$searchValue,$groupId);
    public function joinGroup($groupId,$userId);
    public function batchJoinGroup($groupId, $num);
    public function batchDeleteMembers($groupId, $num);
    
}

class AdminService implements IAdminService
{
    public function getUsers($params,$username,$pageNo,$pageSize){
        $user = new User();
        $where = "1=1";
        foreach ($params as $key => $value){
            $where.=" and `{$key}` = '{$value}'";
        }
        if(!empty($username))$where.=" and (user_name like '%{$username}%' or account_name like '%{$username}%')";
        return $user->find()->asArray()->where($where)->offset(($pageNo-1)*$pageSize)->limit($pageSize)->all();
    }
    public function addUser($userdata){
        
        $user = new User();
        foreach ($userdata as $key=>$value){
            if($value===0||!empty($value)) $user->$key = $value;
        }
        if(!$user->validate())throw new Exception("缺少必要的参数。");
        $user->create_time = date("Y-m-d H:i:s");
        $user->user_type = 2;
        $user->save();
        $user = $user->find()->asArray()->where("user_id = {$user->user_id}")->one();
        return $user;
        
    }
    public function updateUser($userdata){
        $user = new User();
        $user_id = $userdata["user_id"];
        unset($userdata["user_id"]);
        $user->updateAll($userdata,"user_id = {$user_id}");
        $user = $user->find()->asArray()->where("user_id = {$user_id}")->one();
        
        return $user;
    }
    public function deleteUser($userId){
        $user = new User();
        $user->deleteAll("user_id= {$userId}");
        return true;
        
    }
    
    public function getMicroblogComment($usename,$pageNo,$pageSize){
        $comment = new MicroblogComment();
        $where = "1=1";
        $pageStart = $pageSize*($pageNo-1);
        $limit = " {$pageStart},{$pageSize}";
        $command = \Yii::$app->db->createCommand("select u.user_name ,c.* from user u join microblog_comment c on c.com_user_id = u.user_id where u.user_name like '%{$usename}%' order by c.com_id desc limit {$limit}");
        return $command->query()->readAll();
    }
    public function deleteMicroblogComment($comId){
        $comment = new MicroblogComment();
        $comment->deleteAll("com_id = {$comId}");
        return true;
    }
    public function getRecommendList(){
        $recModel = new AdminRecommendUser();
        return $recModel->find()->asArray()->joinWith("user")->all();
    }
    public function deleteRecommend($id){
        $recModel = new AdminRecommendUser();
        $recModel->deleteAll("id={$id}");
        return true;
    }
    public function updateRecommend($id,$arr){
        $recModel = new AdminRecommendUser();
        $recModel->updateAll($arr,"id={$id}");
        return true;
    }
    public function addRecommend($userId){
        $model = new AdminRecommendUser();
        $ret = $model->find()->asArray()->where("user_id={$userId}")->one();
        if(!empty($ret))return $ret;
        else{
            $model->user_id = $userId;
            $model->save();
            $id= $model->id;
            return $ret = $model->find()->asArray()->where("id={$id}")->one();
        }
    }
    public function recallfr(){
        $friend = new Friend();
        $allFriend = $friend->find()->asArray()->all();
        
        foreach ($allFriend as $fr){
            $userId= $fr["user_id"];
            $friendId = $fr["friend_id"];
            
            \Yii::$app->redis->executeCommand("zadd",["friend:{$friendId}:fans",time(),$userId]);
            $ret = \Yii::$app->redis->executeCommand("zadd",["friend:{$userId}:concern",time(),$friendId]);
        }
        return count($allFriend);
    }
    
    public function getGroups($pageNo,$pageSize,$searchValue){
        $group  = new GroupInfo();
        $where = " {{%group_info}}.group_nick like '%{$searchValue}%' or {{%group_info}}.group_topic like '%{$searchValue}%' ";        
        if(is_numeric($searchValue))$where.=" or {{%group_info}}.group_id = '{$searchValue}'";
        $list = $group->find()->asArray()->offset(($pageNo-1)*$pageSize)->limit($pageSize)->where($where)->joinWith("event")->joinWith("owner")->all();
        return $list;
    }
    public function getGroupMembers($pageNo,$pageSize,$searchValue,$groupId){
        $grouprela  = new GroupRela();
        $userids = $grouprela->find()->select("user_id")->asArray()->where("group_id={$groupId}")->column();
        if(empty($userids))$userids=[0];
        $userids=implode(",", $userids);
        $where = " ({{%user}}.user_name like '%{$searchValue}%' or {{%user}}.account_name like '%{$searchValue}%' )";
        $where.=" and {{%user}}.user_id  in ({$userids})";
        $user = new User();
        $list = $user->find()->asArray()->offset(($pageNo-1)*$pageSize)->limit($pageSize)->where($where)->all();
        return $list;
    }
    public function searchUserForGroup($pageNo, $pageSize,$searchValue,$groupId){
        
        $grouprela  = new GroupRela();
        $userids = $grouprela->find()->select("user_id")->asArray()->where("group_id={$groupId}")->column();
        if(empty($userids))$userids=[0];
        $userids=implode(",", $userids);
        $where = " ({{%user}}.user_name like '%{$searchValue}%' or {{%user}}.account_name like '%{$searchValue}%' )";
        $where.=" and {{%user}}.user_id not in ({$userids})";
        $user = new User();
        $list = $user->find()->asArray()->offset(($pageNo-1)*$pageSize)->limit($pageSize)->where($where)->all();
        return $list;
        
    }
    public function joinGroup($groupId,$userId){
        $gr = new GroupRela();
        $isexist = $gr->findOne("user_id = {$userId} and group_id={$groupId}");
        if($isexist)return true;
        $gr = new GroupRela();
        
        $gr->group_id =$groupId;
        $gr->user_id = $userId;
        $gr->is_msg_remind = 0;
        $gr->create_time = date("Y-m-d H:i:s");
        $gr->save();
        return true;
    }
    
    public function batchJoinGroup($groupId, $num){
        
        $grouprela  = new GroupRela();
        $userids = $grouprela->find()->select("user_id")->asArray()->where("group_id={$groupId}")->column();
        if(empty($userids))$userids=[0];
        $userids=implode(",", $userids);
        $where="{{%user}}.user_id not in ({$userids})";
        $user = new User();
        $list = $user->find()->asArray()->limit($num)->where($where)->all();
        foreach ($list as $user ){
            $gr = new GroupRela();
            
            $gr->group_id =$groupId;
            $gr->user_id = $user["user_id"];
            $gr->is_msg_remind = 0;
            $gr->create_time = date("Y-m-d H:i:s");
            $gr->save();
        }
       return true;
    }
    
    public function batchDeleteMembers($groupId, $num){
        $grouprela  = new GroupRela();
        $userids = $grouprela->find()->select("user_id")->asArray()->where("group_id={$groupId}")->column();
        if(empty($userids))return true;
        $userids = array_slice($userids, 0,$num);
        $userids = implode(",", $userids);
        $grouprela->deleteAll("group_id = {$groupId} and user_id in ({$userids})");
        return true;
    }
}