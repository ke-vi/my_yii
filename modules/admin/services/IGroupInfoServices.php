<?php


namespace app\modules\admin\services;
// use app\modules\api\models\GroupEvent;
// use app\modules\api\models\GroupInfo;
// use app\modules\api\models\GroupRela;
// use app\modules\api\models\EventInfoDetail;
// use yii\helpers\ArrayHelper;
// use yii\base\Exception;
// use app\modules\api\services\UserServices;
// use app\modules\api\services\FriendServices;

interface IGroupInfoServices
{
    public function AddGroup($arr);
//    public function RobotStar($group_id,$arry);
    public function GetGroupEventList($pageNo,$pageSize);
    public function GetGroupsByeventId($pageNo,$pageSize,$eventid);
    public function GetGroupByeventId($eventid);
    public function GetEvent($pageNo,$pageSize);
    public function RandomChat();
    public function VisitGroup($event_id,$groupId,$visit_uid);
    public function RandomGetUid($groupId);
    public function Join_group($group_rela,$event_id);
    public function Search_group($group_val);
    public function GetGroupByGroupId($groupId);
    public function Visitor_Quit_Group($event_id,$groupId,$userId);
    public function checkGroupEventId($event_id,$group_id);
    public function MebQuitGroup($event_id,$groupId,$userId,$type);
    public function GetGroupList($userId,$type,$pageNo,$pageSize,$myId);
    public function PostMsg($groupId);
    public function Random_question();
    public function GetGroupMem($groupId);
    public function GetAllGroup($event_id);
    public function AddGroupQueue($groupId,$userId);
    public function GetGroupQueueMemberId($groupId);
    public function IncrEventOnlineRecord($event_id);
    public function DecrEventOnlineRecord($event_id);
    public function QuitAllopenGroupByUserID($userId,$type);
    public function IsUserInGroup($userId,$groupId);
    public function DelMeberQueue($groupId,$userId);
    public function AddMemberPic($userId,$userPicture);
    public function getRobotUserContentByGroupId($groupId,$eventId);
    public function getRobotGroup();
    public function deleteOrtherOpenGroup($userId,$groupId);
    public function Is_no_speak();
    public function CountGroupMember($group_id,$number);
    public function UpdateUserGroupOnline($user_id);
    public function getGroupOnlineCountFromGr($groupId);
    public function GetGroupOnlineCount($groupId);
    public function getEventIdByGid($groupId);
    public function GetPublicGroupId($userId);
    public function getGroupOfflineUsers($groupId);
    public function getRobotByRand($groupId);
    public function getRobotConetentRand();
    public function isRobotGroup($groupId);
    public function getRandomGroups();
    public function get_group_info($group_id);
}

class GroupInfoServices implements IGroupInfoServices
{
    private $GroupRange = 15;//共有群最大容量

    public function AddGroup($arr)
    {
        $GroupInfo = new GroupInfo();
        $GroupInfo->open_status = $arr["open_status"];
        $GroupInfo->group_nick = $arr["group_nick"];
        $GroupInfo->school_name = $arr["school_name"];
        $GroupInfo->group_picture = $arr["group_picture"];
        $GroupInfo->event_id = $arr["event_id"];
        $GroupInfo->group_address = $arr["group_address"];
        $GroupInfo->owner_id = $arr["owner_id"];
        $GroupInfo->create_time = date("Y-m-d H:i:s");
        
        if($GroupInfo->save())
        {
            $group = ArrayHelper::toArray($GroupInfo);
            try {
                $this->Join_group(["group_id"=>$group["group_id"],"is_msg_remind"=>1,"user_id"=>$arr["owner_id"],"open_status"=>$GroupInfo["open_status"]],$group["event_id"]);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
            return ['msg'=>$group,
                'err'=>'',
            ];
        }
        else
        {
            return ['msg'=>'','err'=>'save failure'];
        }
    }

    public function GetGroupEventList($pageNo,$pageSize)
    {
        $res = GroupEvent::find()->asArray()->offset(($pageNo-1)*$pageSize)->limit($pageSize)->all();
        foreach($res as $k=>$v)
        {
            $res[$k]["online"] = $this->GetEventOnlineCount($res[$k]["event_id"]);
        }
        return $res;
    }

    public function GetGroupsByeventId($pageNo,$pagesize,$eventid)
    { 
        $res=GroupInfo::findBySql("SELECT * FROM group_info WHERE event_id = {$eventid} ORDER BY RAND() LIMIT {$pagesize}")->asArray()->all(); 
        
        foreach($res as $k=>$v)
        {
            $res[$k]["members"] = $this->GetGroupMem($res[$k]["group_id"]);
        }
        return $res;
    }
    
    /**
     * 根据活动id获取随机一个群组
     * @param type $eventid
     * @return type  $res[array_rand($res,1)]
     */
    public function GetGroupByeventId($eventId) {
        $count = 99;
        $flag = 1;
        while ($count == 0 || $count >= 15) {
            $res = GroupInfo::findBySql("SELECT * FROM group_info WHERE open_status=1 and event_id = {$eventId} order by rand() limit 1")->asArray()->one();
            //判断是否满群或空群
            $count = $this->GetGroupOnlineCount($res["group_id"]);
            if ($count != 0 && $count < 15) {
                $res["members"] = $this->GetGroupMem($res["group_id"]);
                return $res;
            }else{
                $flag=$flag+1;
                if($flag == 10){
                    return -1;
                }
            }
        }
    }
    //获取活动总在线人数
    private function GetEventOnlineCount($event_id)
    {
        $redis = \yii::$app->redis;
        $res = $redis->executeCommand("get",["event:{$event_id}:online"]);
        if(empty($res))
        {
            return 0;
        }
        else
        {
            return $res;
        }
    }
    //活动总人数加一
    public function IncrEventOnlineRecord($event_id)
    {
        $redis = \yii::$app->redis;
        $redis->executeCommand("incr",["event:{$event_id}:online"]);
    }
    //活动总人数减一
    public function DecrEventOnlineRecord($event_id)
    {
        $redis = \yii::$app->redis;
        $redis->executeCommand("decr",["event:{$event_id}:online"]);
    }
    //获取群的在线总人数
    public function GetGroupOnlineCount($group_id)
    {
        $redis = \yii::$app->redis;
        $count = $redis->executeCommand("zcard",["group:{$group_id}:member"]);
        if(empty($count))
        {
            return 0;
        }
        else
        {
            return intval($count);
        }
    }

    //获得所有活动
    public function GetEvent($pageNo,$pageSize)
    {
        return GroupEvent::find()->asArray()->offset(($pageNo-1)*$pageSize)->limit($pageSize)->all();
    }
    //随机获取一个群
    public function RandomChat()
    {
        $pageNo = 1;
        $pageSize = 20;
        $res = GroupInfo::find()->asArray()->offset(($pageNo-1)*$pageSize)->limit($pageSize)->all();

        foreach($res as $k=>$v)
        {
            $res[$k]["members"] = $this->GetGroupMem($res[$k]["group_id"]);
        }
        arsort($res);
        return $res[array_rand($res,1)];
    }

    //获取所有共有群,并且都是活动 1 和 2
    public function GetAllGroup($event_id)
    {
        $res = GroupInfo::find()->where("open_status = 1 and event_id = :eid",[':eid'=>$event_id])->asArray()->all();
        return $res;
    }

    //访问群组
    public function VisitGroup($event_id,$group_id,$visit_uid)
    {
        $this->AddGroupMember($group_id,$visit_uid);
        //添加活动计数
        GroupInfo::IncrOnlineRecord($event_id,$group_id,$visit_uid);
    }
    
    public function AddMemberPic($userId,$userPicture)
    {
        $redis = \yii::$app->redis;
        $redis->executeCommand('hset',["user_member_pic",$userId,$userPicture]);
    }
    //用户加入群的排队中
    public function AddGroupQueue($group_id,$user_id)
    {
        $redis = \yii::$app->redis;
        $redis->executeCommand('zadd',["group:{$group_id}:member_queue",time(),$user_id]);
    }
    //删除队列
    public function DelMeberQueue($group_id,$user_id)
    {
        $redis = \yii::$app->redis;
        $redis->executeCommand('zrem',["group:{$group_id}:member_queue",$user_id]);
    }
    
    //redis添加群组成员
    private function AddGroupMember($group_id,$user_id)
    {
        //查询是否是女神玩法
        $redis = \yii::$app->redis;
        $GroupInfo = new GroupInfo();
        $gi = $GroupInfo->findBySql("select count(*) as 'count' from group_info where group_id = :gid and event_id = :eid",[':gid'=>$group_id,':eid'=>'3'])->asArray()->one();
        if($gi["count"] == 1)
        {
            //女神玩法
            if($this->IsGroupOverflow($group_id,5))
            {
                $redis->executeCommand('zadd',["group:{$group_id}:member",time(),$user_id]);
            }else{
                $redis->executeCommand('zadd',["group:{$group_id}:member_queue",time(),$user_id]);
            }
        }else{
            $redis = \yii::$app->redis;
            if($this->IsGroupOverflow($group_id,$this->GroupRange))
            {
                $redis->executeCommand('zadd',["group:{$group_id}:member",time(),$user_id]);
            }else{
                $redis->executeCommand('zadd',["group:{$group_id}:member_queue",time(),$user_id]);
            }
        }
    }
    //判断是否3分钟未发言
    public function Is_no_speak()
    {
        $redis = \yii::$app->redis;
        $arr = $redis->executeCommand('zrange',["user_last_online",0,-1]);
        if($arr)
            return $arr;
        else
            return null;

    }
    /*
     * 随机获取群成员uid
     *
     *
     * */
    public function RandomGetUid($groupId)
    {
//        $res = $this->GetGroupMem($group_id);
        $res = $this->getNoRobotUid($groupId);
        if($res ==null)
        {
            return 0;
        }
        else
        {
            return $res[array_rand($res,1)];
        }
    }

    //用户加入群
    public function Join_group($group_rela,$event_id)
    {   
        $gr = new GroupRela();
        $id = $gr->findBySql('select id from group_rela where group_id = :gid and user_id = :uid',
            [':gid'=>$group_rela["group_id"],':uid'=>$group_rela["user_id"]])
            ->asArray()->one()["id"];
        if (empty($id)) {
            $gr = new GroupRela();
            $gr->event_id = $event_id;
            $gr->group_id = $group_rela["group_id"];
            $gr->user_id = $group_rela["user_id"];
            $gr->open_status = $group_rela['open_status'];
            $gr->is_msg_remind = $group_rela["is_msg_remind"];
            $gr->create_time = date("Y-m-d H:i:s");
            $gr->save();
            //如果加之前群里一个人没有,加入后将群设为有人
            $user_count = $this->GetGroupOnlineCount($group_rela["group_id"]);
            if(!$user_count)
            {
                $connection = \Yii::$app->db;
                $command = $connection->createCommand('UPDATE group_info SET number =1 WHERE group_id = :group_id',[':group_id'=>$group_rela["group_id"]]);
                $command->execute();
            }
            $this->AddGroupMember($group_rela["group_id"], $group_rela["user_id"]);
            $this->IncrEventOnlineRecord($event_id);
        }       
    }
    //判断群在线人数
    public function CountGroupMember($group_id,$number)
    {
        $gr = new GroupRela();
        $count = $gr->findBySql("select count(*) as count from group_rela where group_id={$group_id}  and is_online=1")->asArray()->one()['count'];
        if($count==$number)
            return true;
        else
            return false;
    }
    public function Search_group($group_val)
    {
        if(is_numeric($group_val) == 1)
        {
            //群号查找
            $gi = new GroupInfo();
            $group = $gi->findBySql('select * from group_info where group_id = :gid', [':gid' => $group_val])->asArray()->all();
            if (intval($group[0]['open_status']) == 0) {
                $onlineCount = $this->getGroupOnlineCountFromGr($group[0]['group_id']);
            } else {
                $onlineCount = $this->GetGroupOnlineCount($group[0]['group_id']);
            }
            $group[0]['online'] = $onlineCount;
            return $group;
        }
        else
        {
            //群组查找
            $gi = new GroupInfo();
            $group = $gi->findBySql('select * from group_info where group_nick = :gnick', [':gnick' => $group_val])->asArray()->all();

            foreach ($group as $key => $i) {
                if ($group[$key]['open_status'] == 0) {
                    $onlineCount = $this->getGroupOnlineCountFromGr($group[$key]['group_id']);
                } else {
                    $onlineCount = $this->GetGroupOnlineCount($group[$key]['group_id']);
                }
                $group[$key]['online'] = $onlineCount;
                return $group;
            }
        }
    }
    
    public function GetGroupByGroupId($group_id)
    {
        $gi=new GroupInfo();
        $group=$gi->findBySql('select * from group_info where group_id = :gid',
            [':gid'=>$group_id])->asArray()->one();
        if($group['open_status']==0){
            $onlineCount = $this->getGroupOnlineCountFromGr($group_id);
        }else{
            $onlineCount = $this->GetGroupOnlineCount($group_id);
        }
        $group['online'] = $onlineCount;
        return $group;
    }


    //访客离开频道
    public function Visitor_Quit_Group($event_id,$group_id,$user_id)
    {
        $this->RemGroupMem($event_id,$group_id,$user_id);
        $this->DecrEventOnlineRecord($event_id);
    }

    //检查event_id 和 group_info 是否匹配
    public function checkGroupEventId($event_id,$group_id)
    {
        $group = $this->GetGroupByGroupId($group_id);
        if(!isset($group["event_id"]))
            return false;
        if($event_id != $group["event_id"]){
            return FALSE;
        }
        return TRUE;
    }
    //成员退出群(mysql)  $type 1-主动退 2-被强制退
    public function MebQuitGroup($event_id,$group_id,$user_id,$type)
    {
        //redis中删除user
        $this->RemGroupMem($event_id,$group_id,$user_id);

        $connection = \Yii::$app->db;
        $command = $connection->createCommand('select group_id,open_status from group_info where group_id = :gid and owner_id = :uid',[':gid'=>$group_id,':uid'=>$user_id]);
        $post = $command->queryOne();
        //判断是否为群主
        if($post){
            if ($post['open_status'] == 0) { //私有群
                //群主改为其他人
                $userId = 0;
                $command = $connection->createCommand("select user_id from group_rela where group_id={$group_id} limit 1");
                $user = $command->queryOne();
                if ($user) {
                    $userId = $user['user_id'];
                }
                $command = $connection->createCommand("UPDATE group_info SET owner_id={$userId} WHERE group_id ={$group_id}");
                $command->execute();
            }
        }
        //退群关系
        $gr = new GroupRela();
        $id = $gr->findBySql('select id from group_rela where group_id = :gid and user_id = :uid',
            [':gid'=>$group_id,':uid'=>$user_id])
            ->asArray()->one()["id"];

        if(empty($id))
        {
            return ['msg'=>'','err'=>'Your user is out of the group'];
        }
        $res = GroupRela::findOne($id);
        $res->delete();
        //如果是群主,主动退,且是最后一个退群,删群
//        if($post && $type==1)
//        {
//            $command = $connection->createCommand("select id from group_rela where group_id={$group_id} and user_id!={$user_id} limit 1");
//            $id = $command->queryOne();
//            if(!$id)
//            {
//                $res = GroupInfo::findOne($group_id);
//                $res->delete();
//                return ['msg'=>'quit_succ','err'=>''];
//            }
//        }
        //如果是最后一个人,将群人数设为0
        $user_count = $this->GetGroupOnlineCount($group_id);
        if(!$user_count)
        {
            $command = $connection->createCommand('UPDATE group_info SET number =0 WHERE group_id = :group_id',[':group_id'=>$group_id]);
            $command->execute();
        }
        return ['msg'=>'quit_succ','err'=>''];


    }
    //获取群中正在排队的第一个人
    public function GetGroupQueueMemberId($group_id) 
    {
        $redis = \yii::$app->redis;
        $member_queue = $redis->executeCommand('zrange', ["group:{$group_id}:member_queue", 0, 0]);
        if(empty($member_queue)){
            return $member_queue;
        }
        $userId=$member_queue[0];
        return $userId;
    }

    //删除群成员（redis）
    private function RemGroupMem($event_id,$group_id,$user_id)
    {
        $redis = \yii::$app->redis;
        $redis->executeCommand('zrem',["group:{$group_id}:member",$user_id]);
        $redis->executeCommand("decr",["event:{$event_id}:online"]);
    }

    public function GetGroupList($user_id,$type,$pageNo,$pageSize,$myId)
    {
        $gr = new GroupRela();
        $limit = "limit ".(($pageNo-1)*$pageSize).",{$pageSize}";
        if($type == 2){
            $type_tem = $type;
            $open_status = " ";
        }elseif($type==3){
            $type_tem = $type;
            $type = 1;
            $open_status = "gi.open_status = {$type} AND ";
        }else
        {
            $type_tem = $type;
            $open_status = "gi.open_status = {$type} AND ";
        }
        $res = $gr->findBySql("SELECT  gi.group_id,gi.owner_id,gi.group_nick,gi.group_address,gi.open_status,gi.event_id,gr.user_id,gi.group_picture, ".
            "CASE WHEN gr.user_id = owner_id THEN 'true' ELSE 'false' END AS 'isowner' ".
            "FROM group_info AS gi LEFT JOIN group_rela AS gr ON gr.group_id = gi.group_id ".
            "WHERE ".$open_status." gr.user_id = :uid  ORDER BY gr.create_time DESC {$limit}",[':uid'=>$user_id])->asArray()->all();
        if($type_tem!=3 && $user_id==$myId)
        {
            $gi = new GroupInfo();
            $ress = $gi->findBySql("select group_id,owner_id,group_nick,group_address,open_status,event_id,owner_id as user_id,group_picture,'isowner'=true from group_info gi where $open_status owner_id={$myId} and number=0")->asArray()->all();
            foreach($ress as $k=>$v)
            {
                $res[] = $ress[$k];
            }
            foreach($res as $k=>$v)
            {
                $res[$k]["online"] = $this->getGroupOnlineCountFromGr($res[$k]["group_id"]);
                $res[$k]["group_rev"] = $this->GetGroupRevenues($res[$k]["group_id"]);
            }
        }
        return $res;
    }
    //获得群信息
    public function get_group_info($group_id)
    {
        $gi = new GroupInfo();
        $re = $gi->findBySql("select group_id,owner_id,group_nick,group_address,open_status,event_id,group_picture from group_info where group_id={$group_id}")->asArray()->one();
        if(!$re)
            return false;
        $re['online'] = $this->getGroupOnlineCountFromGr($re['group_id']);
        $re['group_rev'] = $this->GetGroupRevenues($re["group_id"]);

        return $re;
    }
    public function PostMsg($group_id)
    {
        $redis = \yii::$app->redis;
        $redis->executeCommand("incr",["group:{$group_id}:revenues"]);
    }

    //获取群收益
    private function GetGroupRevenues($group_id)
    {
        $rev_count = 10000;
        $redis = \yii::$app->redis;
        $res = $redis->executeCommand("get",["group:{$group_id}:revenues"]);
        if(!empty($res))  //&&$res >= $rev_count)
        {
            return $res/$rev_count;
        }
        else
        {
            return 0;
        }
    }

    private function UpdateGroupMember()
    {
//        $res = $redis->executeCommand("get",["group:{$group_id}:revenues"]);
    }

    public function Random_question()
    {
        $gr = new EventInfoDetail();
//        $res = $gr->findBySql("SELECT t1.eid_id,event_id,content,create_time,`status` FROM event_info_detail  as t1 ".
//            "JOIN (SELECT ROUND(RAND() *".
//            "((SELECT MAX(eid_id) FROM event_info_detail))-(SELECT MIN(eid_id) ".
//            "FROM event_info_detail))+(SELECT MIN(eid_id) FROM event_info_detail)".
//            "AS eid_id) AS t2 WHERE t1.eid_id >= t2.eid_id ORDER BY t1.eid_id LIMIT 1")->asArray()->one();
//        
        $res = $gr->findBySql("SELECT * FROM event_info_detail "
                . "WHERE eid_id >= ((SELECT MAX(eid_id) FROM event_info_detail)-(SELECT MIN(eid_id) "
                . "FROM event_info_detail)) * RAND() + (SELECT MIN(eid_id) FROM event_info_detail)  LIMIT 1")->asArray()->one();
        return $res;
    }

    // 判断当前群是否在允许的范围内如果在 范围内 true 如果不在false
    private function IsGroupOverflow($group_id,$range)
    {
        $res = GroupInfo::find()->where("group_id = :gid",[':gid'=>$group_id])->asArray()->one();
        if($res['open_status'] == 1)
        {
            $redis = \yii::$app->redis;
            $len = $redis->executeCommand('zcard',["group:{$group_id}:member"]);
            if($len<$range)
            {
                return true;
            }else{
                return false;
            }
        }
        return true;
    }

    /**
    * 获取在线人的信息
    * @param type $group_id
    * @return type     /
    */

    public function GetGroupMem($group_id)
    {
        $redis = \yii::$app->redis;
        $arr = $redis->executeCommand('zrange',["group:{$group_id}:member",0,-1]);
        $obj = [];
        foreach($arr as $k=>$v)
        {
         $userServices =new UserServices();   
         $user = $userServices->getUserByUserId(0, $v, 0);
         
            $obj[$k]["user_id"] = $v;
            $obj[$k]["user_name"] = $user["user_name"];
            $obj[$k]["user_picture"] = $redis->executeCommand('hget',["user_member_pic",$v]);
            $obj[$k]["is_online"] = \Yii::$app->db->createCommand('select is_online from group_rela where user_id = :uid and group_id = :gid',[':uid'=>$v,':gid'=>$group_id])->queryScalar();
            
            if(empty($obj[$k]["user_picture"])){
               $obj[$k]["user_picture"] = \Yii::$app->db->createCommand('select user_picture from user where user_id = :uid ',[':uid'=>$v])->queryScalar();
               $redis->executeCommand('hset',["user_member_pic",$v,$obj[$k]["user_picture"]]);
            }
        }
        return $obj;
    }


    public function rules()
    {
        return [
            [['group_id', 'owner_id', 'open_status', 'group_address'], 'required'],
        ];
    }
    
    public function IsUserInGroup($user_id,$group_id)
    {
        $gr = new GroupRela();
        $ret = $gr->findBySql('select id from group_rela where group_id = :gid and user_id = :uid',[':gid'=>$group_id,':uid'=>$user_id])->asArray()->one();
        if(empty($ret)){
            return $ret;
        }
        $connection = \Yii::$app->db;
        $command = $connection->createCommand("UPDATE group_rela SET is_online =1 WHERE id = {$ret['id']}");
        $command->execute();
        
        $online = $this->getGroupOnlineCountFromGr($group_id);
        
        $group=$this->GetGroupByGroupId($group_id);
        $group['online'] = $online;
        
        return $group;      
    }
    
    //用户退出所有共有群
    public function QuitAllopenGroupByUserID($user_id,$type)
    {
        $group_rela = new GroupRela();
        $gr_ids=$group_rela->findBySql('select gr.id,gr.group_id,gi.event_id FROM group_rela as gr LEFT JOIN group_info as gi ON gr.group_id=gi.group_id WHERE gr.user_id = :user_id and gi.open_status=1',[':user_id'=>$user_id])->asArray()->all();
        if(empty($gr_ids)){
            return ["msg"=>"This user not open_group"];
        }
        for($i=0;$i<count($gr_ids);$i++)
        {

            //退mysql
            $ret = $this->MebQuitGroup($gr_ids[$i]["event_id"],$gr_ids[$i]["group_id"],$user_id,$type);
        }
        if(empty($ret["msg"])){
            return ["msg"=>"quit error"];
        }
        return $gr_ids;
    }
    //私有群离线
    public function UpdateUserGroupOnline($user_id)
    {
        $connection = \Yii::$app->db;
        $command = $connection->createCommand("UPDATE group_rela SET is_online =0 WHERE user_id=$user_id and open_status=0");
        $command->execute();
    }
    
    //获取机器人群列表
    public function getRobotGroup()
    {
        $redis = \yii::$app->redis;
        //获的robot_massage_group_flag中的所有群
        $groups=$redis->executeCommand('hkeys',["robot_massage_group_flag"]);
        return $groups;
    }
    
    //判断是否是机器人群组
    public function isRobotGroup($groupId)
    {
        $redis = \yii::$app->redis;
        //获的robot_massage_group_flag中的所有群
        $groups=$redis->executeCommand('hkeys',["robot_massage_group_flag"]);
        
        if(in_array($groupId,$groups)){
            return TRUE;
        }else{
            return FALSE;
        }
    }
    
    //获取redis中机器人的说话内容
    public function getRobotUserContentByGroupId($groupId,$eventId)
    {
        $redis = \yii::$app->redis;
        if ($eventId == 1) {
            //发自拍
            //获取robot_massage_photo总条数
            $count = $redis->executeCommand('LLEN', ["robot_massage:photo:{$groupId}"]);
            //获得群的robot_massage_group_flag
            $flag = $redis->executeCommand('hget', ["robot_massage_group_flag", $groupId]);
            $content = $redis->executeCommand('lrange', ["robot_massage:photo:{$groupId}", $flag - 1, $flag - 1]);
        } else if ($eventId == 2) {
            //真心话
            //获取robot_massage总条数
            $count = $redis->executeCommand('LLEN', ["robot_massage:heart:{$groupId}"]);
            //获得群的robot_massage_group_flag
            $flag = $redis->executeCommand('hget', ["robot_massage_group_flag", $groupId]);
            //获取robot_massage内容
            $content = $redis->executeCommand('lrange', ["robot_massage:heart:{$groupId}", $flag - 1, $flag - 1]);
        }
        $arr = split(":", $content[0]);
        if($flag<$count){
            $redis->executeCommand('hset',["robot_massage_group_flag",$groupId,$flag+1]);
        }else{
            $redis->executeCommand('hset',["robot_massage_group_flag",$groupId,1]);
        }
        return $arr;      
    }
    //删除之前加入的群
    public function deleteOrtherOpenGroup($userId,$groupId)
    {
        //判断要加的是公有还是私有群
        $gr = new GroupRela();
        $groupinfo = $gr->findBySql('SELECT id,open_status FROM group_rela WHERE group_id = :gid',
            [':gid'=>$groupId])->asArray()->one();
        if($groupinfo)
        {
            if($groupinfo['open_status']==0)
                return true;
        }
        //退群关系
        $gr = new GroupRela();
        $groupRela = $gr->findBySql('SELECT id,event_id,group_id FROM group_rela WHERE group_id != :gid AND user_id = :uid AND open_status = 1',
            [':gid'=>$groupId,':uid'=>$userId])->asArray()->one();
        
        if(empty($groupRela)){
            return true;
        }
        $res = GroupRela::findOne($groupRela['id']);
        $res->delete();
        $this->RemGroupMem($groupRela['event_id'], $groupRela['group_id'], $userId);
        //如果是最后一个人,将群人数设为0
        $user_count = $this->GetGroupOnlineCount($groupRela['group_id']);
        if(!$user_count)
        {
            $connection = \Yii::$app->db;
            $command = $connection->createCommand('UPDATE group_info SET number =0 WHERE group_id = :group_id',[':group_id'=>$groupRela['group_id']]);
            $command->execute();
        }
        return true;
    }
    
    //获取群中非机器人的uid
    private function getNoRobotUid($groupId)
    {
        $redis = \yii::$app->redis;
        //获取群中所有uid
        $arr = $redis->executeCommand('zrange', ["group:{$groupId}:member", 0, -1]);
        //去除机器人uid
        foreach ($arr as $key => $value) {
            if ($value < 10000){
                unset($arr[$key]);
            }
        }
        return $arr;
    }
    
    //随机获取群中机器人
    public function getRobotByRand($groupId){
        $redis = \yii::$app->redis;
        //获取群中所有uid
        $arr = $redis->executeCommand('zrange', ["group:{$groupId}:member", 0, -1]);
        foreach ($arr as $key => $value) {
            if ($value > 10000){
                unset($arr[$key]);
            }
        }
        return $arr[array_rand($arr,1)];
    }
    //随机获取群中机器人欢迎新人的话
    public function getRobotConetentRand(){
        
        //从数据库中获取欢迎语句
        $content = \Yii::$app->db->createCommand("SELECT content FROM welcome_content ORDER BY RAND() LIMIT 1")->queryScalar();
        return $content;
    }
    
    //从group_rela表中获取群在线人数
    public function getGroupOnlineCountFromGr($groupId)
    {
        $gr = new GroupRela();
        $ret = $gr->findBySql('select id from group_rela where group_id = :gid and is_online = 1 ',[':gid'=>$groupId])->asArray()->all();
        return count($ret);
    }
    public function getEventIdByGid($groupId)
    {
        $group = $this->GetGroupByGroupId($groupId);
        return $group["event_id"];
    }
    //获得用户的公有群Id
    public function GetPublicGroupId($userId)
    {
        $ret = null;
        $gr = new GroupRela();
        $ret = $gr->findBySql("select group_id,event_id from group_rela where user_id={$userId} and open_status=1")->asArray()->one();
        return $ret;
    }
    
    //获取私有群中不在线的所有userId
    public function getGroupOfflineUsers($groupId){
//        $groupRela = new GroupRela();
//        $userList = $groupRela->findBySql("SELECT user_id FROM group_rela where group_id ={$groupId} and is_online = 0")->asArray()->all();
        
        $connection = \Yii::$app->db;
        $command = $connection->createCommand("SELECT user_id FROM group_rela where group_id ={$groupId} and is_online = 0");
        $userIds= $command->queryColumn();
        return $userIds;
    }
    
    //随机获取9个公有群
    public function getRandomGroups(){
        
         $connection = \Yii::$app->db;
        $groupIdList = $connection->createCommand("SELECT group_id FROM group_info WHERE open_status=1 AND group_id > 19999 and number>0 ORDER BY RAND() LIMIT 7")->queryColumn();
        //判断用户群是否达到9个，否：剩余的去获取机器人群
        if(count($groupIdList)<9){
            $robotCount = 9 - count($groupIdList);
            $robotGroupList = $connection->createCommand("SELECT group_id FROM group_info WHERE group_id < 19999 and number>0 ORDER BY RAND() LIMIT {$robotCount}")->queryColumn();
            $groupIdList=array_merge($robotGroupList,$groupIdList);     
        }
        
        $groupList = implode(',', $groupIdList);
        $gi = new GroupInfo();
        $res = $gi->findBySql("SELECT * FROM group_info WHERE group_id in ({$groupList}) ORDER BY RAND() ")->asArray()->all();
        
        foreach ($res as $key =>$value){
            $res[$key]['online'] = $this->GetGroupOnlineCount($res[$key]['group_id']);
        }
        return $res;
    }
}