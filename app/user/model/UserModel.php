<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\user\model;

use think\Db;
use think\Model;

class UserModel extends Model
{
    public function doMobile($user)
    {
        $userQuery = Db::name("user");

        $result = $userQuery->where('mobile', $user['mobile'])->find();

        if (!empty($result)) {
            if (cmf_compare_password($user['user_pass'], $result['user_pass'])) {
                session('user', $result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $userQuery->where('id', $result["id"])->update($data);
                return 0;
            }
            return 1;
        }
        return 2;
    }

    public function doName($user)
    {
        $userQuery = Db::name("user");

        $result = $userQuery->where('user_login', $user['user_login'])->find();

        if (!empty($result)) {
            if (cmf_compare_password($user['user_pass'], $result['user_pass'])) {
                session('user', $result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $userQuery->where('id', $result["id"])->update($data);
                return 0;
            }
            return 1;
        }
        return 2;
    }

    public function doEmail($user)
    {
        $userQuery = Db::name("user");

        $result = $userQuery->where('user_email', $user['user_email'])->find();

        if (!empty($result)) {
            if (cmf_compare_password($user['user_pass'], $result['user_pass'])) {
                session('user', $result);
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $userQuery->where('id', $result["id"])->update($data);
                return 0;
            }
            return 1;
        }
        return 2;
    }

    public function registerEmail($user)
    {
        $userQuery = Db::name("user");
        $result    = $userQuery->where('user_email', $user['user_email'])->find();

        $userStatus = 1;

        if (cmf_is_open_registration()) {
            $userStatus = 2;
        }

        if (empty($result)) {
            $data   = [
                'user_login'      => '',
                'user_email'      => $user['user_email'],
                'mobile'          => '',
                'user_nickname'   => '',
                'user_pass'       => cmf_password($user['user_pass']),
                'last_login_ip'   => get_client_ip(0, true),
                'create_time'     => time(),
                'last_login_time' => time(),
                'user_status'     => $userStatus,
                "user_type"       => 2,
            ];
            $userId = $userQuery->insertGetId($data);
            $date   = $userQuery->where('id', $userId)->find();
            cmf_update_current_user($date);
            return 0;
        }
        return 1;
    }

    public function registerMobile($user)
    {
        $result = Db::name("user")->where('mobile', $user['mobile'])->find();

        $userStatus = 1;

        if (cmf_is_open_registration()) {
            $userStatus = 2;
        }

        if (empty($result)) {
            $data   = [
                'user_login'      => '',
                'user_email'      => '',
                'mobile'          => $user['mobile'],
                'user_nickname'   => '',
                'user_pass'       => cmf_password($user['user_pass']),
                'last_login_ip'   => get_client_ip(0, true),
                'create_time'     => time(),
                'last_login_time' => time(),
                'user_status'     => $userStatus,
                "user_type"       => 2,//会员
                "parent_id"       => $user['parent_id'],//推荐者ID
            ];
            $userId = Db::name("user")->insertGetId($data);
            $data   = Db::name("user")->where('id', $userId)->find();
            cmf_update_current_user($data);
            return 0;
        }
        return 1;
    }

    /**
     * 通过邮箱重置密码
     * @param $email
     * @param $password
     * @return int
     */
    public function emailPasswordReset($email, $password)
    {
        $result = $this->where('user_email', $email)->find();
        if (!empty($result)) {
            $data = [
                'user_pass' => cmf_password($password),
            ];
            $this->where('user_email', $email)->update($data);
            return 0;
        }
        return 1;
    }

    /**
     * 通过手机重置密码
     * @param $mobile
     * @param $password
     * @return int
     */
    public function mobilePasswordReset($mobile, $password)
    {
        $userQuery = Db::name("user");
        $result    = $userQuery->where('mobile', $mobile)->find();
        if (!empty($result)) {
            $data = [
                'user_pass' => cmf_password($password),
            ];
            $userQuery->where('mobile', $mobile)->update($data);
            return 0;
        }
        return 1;
    }

    public function editData($user)
    {
        $userId           = cmf_get_current_user_id();
//        $user['birthday'] = strtotime($user['birthday']);
        $userQuery        = Db::name("user");
        if ($userQuery->where('id', $userId)->update($user)) {
            $data = $userQuery->where('id', $userId)->find();
            cmf_update_current_user($data);
            return 1;
        }
        return 0;
    }

    /**
     * 用户密码修改
     * @param $user
     * @return int
     */
    public function editPassword($user)
    {
        $userId    = cmf_get_current_user_id();
        $userQuery = Db::name("user");
        if ($user['password'] != $user['repassword']) {
            return 1;
        }
        $pass = $userQuery->where('id', $userId)->find();
        if (!cmf_compare_password($user['old_password'], $pass['user_pass'])) {
            return 2;
        }
        $data['user_pass'] = cmf_password($user['password']);
        $userQuery->where('id', $userId)->update($data);
        return 0;
    }
    public function tuandui()
    {
        $userId               = cmf_get_current_user_id();
        $userMoneyQuery            = Db::name("user_money_log");

        $userQuery            = Db::name("user");
        $where['parent_id']     = $userId;
        $favorites            = $userQuery->where($where)->order('id desc')->paginate(10);
        $data['page']         = $favorites->render();

        $list=[];
        foreach($favorites as $v){
            $je= $userMoneyQuery->field(array('sum(jine)'=>'je'))->where(array('user_id'=>$v['id'],'type'=>1))->find();
            $v['xiaofei']=$je['je'];
            $list[]=$v;
        }
        $data['lists']        = $list;
        return $data;
    }
    public function tixian()
    {
        $userId               = cmf_get_current_user_id();
        $userTixianQuery            = Db::name("tixian_post");

        $where['user_id']     = $userId;
        $favorites            = $userTixianQuery->where($where)->order('id desc')->paginate(10);
        $data['page']         = $favorites->render();

        $data['lists']        = $favorites->items();
        return $data;
    }
    public function pinglun()
    {
        $userId               = cmf_get_current_user_id();
        $userPinglunQuery            = Db::name("pinglun_post");

        $where['user_id']     = $userId;
        $where['post_type']     = 1;
        $favorites            = $userPinglunQuery->where($where)->order('id desc')->paginate(10);
        $list=[];

        $userPinglunTaskQuery            = Db::name("zhidaotaskdata");
        foreach($favorites as $v){

            $task_ok_count=$userPinglunTaskQuery->field(array('count(*)'=>'count'))->where(['pinglun_id'=>$v['id'],'return_code'=>1])->select();
            $task_err_count=$userPinglunTaskQuery->field(array('count(*)'=>'count'))->where(['pinglun_id'=>$v['id'],'return_code'=>[['neq',1],['neq','']],'delete_time'=>0])->select();

            $v['task_ok_count']=$task_ok_count[0]['count'];
            $v['task_err_count']=$task_err_count[0]['count'];
            $v['task_ok']=0;
            if($v['post_content_num']==($v['task_ok_count']+$v['task_err_count'])) {//数量相等，任务完成
                $v['task_ok']=1;
            }
            $list[]=$v;
        }
        $data['page']         = $favorites->render();

        $data['lists']        = $list;
        return $data;
    }
    public function pinglunadd($data)
    {
        $userId               = cmf_get_current_user_id();
        $userPinglunQuery            = Db::name("pinglun_post");

        $str = str_replace(array("\r\n", "\r", "\n", "\t"), "###", $data['pinglun_content']);
        $content_data=explode('###',$str);
        $j=0;
        foreach($content_data as $v){
            if(strlen($v)>1){
                $j++;
            }
        }
        $content_num=$j;

        $pinglun_data=['post_type'=>1,'post_title'=>$data['post_title'],'post_content'=>'','post_url'=>$data['post_url'],'post_content_num'=>$content_num,'user_id'=>$userId,'create_time'=>time()];


        $userPinglunQuery->insert($pinglun_data);
        $renwu_id=$userPinglunQuery->getLastInsID();

        //存入内容表
        $time=time();
        $contentQuery=Db::name('pinglun_content_post');
        $renwuQuery=Db::name('zhidaotaskdata');
        $CookieQuery=Db::name('zhidaobaiducook');
        foreach($content_data as $v) {
            //todo:内容禁词检测

            $content_data = ['post_title'=>$v,'pinglun_id'=>$renwu_id,'create_time'=>$time];
            $contentQuery->insert($content_data);
            $content_id=$contentQuery->getLastInsID();
            //随机百度cookie
            $baidu_cookie = $CookieQuery->field('baidu_cookie')->where('cookie_fail','<',10)->order('rand()')->limit(1)->find();
            //生成任务列表
            $renwudata = ['pinglun_id' => $renwu_id, 'content_id'=>$content_id,'zhidao' => 'hd', 'get_url' => $data['post_url'], 'content'=>base64_encode($v),'baidu_cookie' => $baidu_cookie['baidu_cookie'], 'create_time' => $time];
            $renwuQuery->insert($renwudata);
        }

        //积分减少
        $userQuery            = Db::name("user");
        $where=[];
        $where['id']=$userId;
        $xiaofei=$content_num*1;
        $coin=$userQuery->where($where)->find();
        $userQuery->where($where)->update(array('score'=>$coin['score']-$xiaofei));

        //增加明细记录
        $userMoneyQuery            = Db::name("user_money_log");
        $data2=[];
        $data2['user_id']=$userId;
        $data2['create_time']=time();
        $data2['type']=2;
        $data2['post_title']='百度知道【'.$data['post_title'].'】评论任务';
        $data2['score']=$xiaofei;
        $userMoneyQuery->insert($data2);

        return $userMoneyQuery;
    }
    public function pinglunzhixing($data){
        $pinglunTaskQuery            = Db::name("zhidaotaskdata");

        $favorites            = $pinglunTaskQuery->field('content,return_code,return_img')->where(['pinglun_id'=>$data['id'],'delete_time'=>0])->order('id desc')->paginate(10);

        $data['page']         = $favorites->render();

        $data['lists']        = $favorites->items();
        return $data;
    }
    public function pinglunTiwen()
    {
        $userId               = cmf_get_current_user_id();
        $userPinglunQuery            = Db::name("pinglun_post");

        $where['user_id']     = $userId;
        $where['post_type']     = 2;
        $where['delete_time']     = 0;
        $join = [
            ['zhidaotaskdata u', 'a.id = u.pinglun_id']
        ];
        $field = 'a.*,u.return_code';

        $favorites            = $userPinglunQuery->alias('a')->join($join)->field($field)->where($where)->order('id desc')->paginate(10);

        $data['page']         = $favorites->render();

        $data['lists']        = $favorites->items();
        return $data;
    }
    public function pingluntiwenadd($data)
    {
        $userId               = cmf_get_current_user_id();
        $userPinglunQuery            = Db::name("pinglun_post");
        //todo:内容禁词检测
        $post_title=$data['post_title'];
        $post_content=$data['post_content'];

        $pinglun_data=['post_type'=>2,'post_title'=>$post_title,'post_content'=>base64_encode($post_content),'post_cookie'=>base64_encode($data['post_cookie']),'user_id'=>$userId,'create_time'=>time()];


        $userPinglunQuery->insert($pinglun_data);
        $renwu_id=$userPinglunQuery->getLastInsID();


        //生成任务列表
        $renwuQuery=Db::name('zhidaotaskdata');
        $renwudata = ['pinglun_id' => $renwu_id, 'zhidao' => 'tw', 'title' => base64_encode($data['post_title']), 'content'=>base64_encode($post_content),'baidu_cookie' => base64_encode($data['post_cookie']), 'create_time' => time()];
        $renwuQuery->insert($renwudata);


        //积分减少
        $userQuery            = Db::name("user");
        $where=[];
        $where['id']=$userId;
        $xiaofei=1;
        $coin=$userQuery->where($where)->find();
        $userQuery->where($where)->update(array('score'=>$coin['score']-$xiaofei));

        //增加明细记录
        $userMoneyQuery            = Db::name("user_money_log");
        $data2=[];
        $data2['user_id']=$userId;
        $data2['create_time']=time();
        $data2['type']=2;
        $data2['post_title']='百度知道【'.$data['post_title'].'】提问任务';
        $data2['score']=$xiaofei;
        $userMoneyQuery->insert($data2);

        return $userMoneyQuery;
    }
    public function mingxi()
    {
        $userId               = cmf_get_current_user_id();
        $userTixianQuery            = Db::name("user_money_log");

        $where['user_id']     = $userId;
        $favorites            = $userTixianQuery->where($where)->order('id desc')->paginate(10);
        $data['page']         = $favorites->render();

        $data['lists']        = $favorites->items();
        return $data;
    }
    public function guanjianciadd($data)
    {
        $userId               = cmf_get_current_user_id();
        $userGuanjianciQuery            = Db::name("guanjianci_post");
        $data['user_id']     = $userId;
        $data['create_time']     = time();
        $userGuanjianciQuery->insert($data);
        $renwu_id=$userGuanjianciQuery->getLastInsID();

        //生成任务列表
        $renwuQuery=Db::name('taskdjdata');
        $renwudata=[];
        switch ($data['post_type']){
            case 1:
                $sou="baidu";
                break;
            case 2:
                $sou="sogou";
                break;
            case 3:
                $sou="so";
                break;
        }
        $time=time();
        for($i=0;$i<$data['post_tianshu'];$i++){
            $rq=strtotime(date('Y-m-d' , strtotime('+'.$i.' day')));
            for($t=0;$t<24;$t++) {
                for ($j = 0; $j < $data['txt_time' . $t]; $j++) {
                    $xs = $t * 3600;
                    //随机百度cookie
                    $baidu_cookie = Db::name('baiducookie')->field('baidu_cookie')->where(['cookie_fail'=>['<',10]])->order('rand()')->limit(1)->find();
                    $renwudata[] = ['renwu_id' => $renwu_id, 'task_time' => $rq + $xs, 'sou' => base64_encode($sou), 'key' => base64_encode($data['post_title']), 'title' => base64_encode($data['post_biaoti']), 'baidu_cookie' => $baidu_cookie['baidu_cookie'], 'create_time' => $time];
                }
            }
        }
        $renwuQuery->insertAll($renwudata);

        //积分减少
        $userQuery            = Db::name("user");
        $where=[];
        $where['id']=$userId;
        $xiaofei=$data['post_dianjicishu']*$data['post_tianshu'];
        $coin=$userQuery->where($where)->find();
        $userQuery->where($where)->update(array('score'=>$coin['score']-$xiaofei));

        //增加明细记录
        $userMoneyQuery            = Db::name("user_money_log");
        $data2=[];
        $data2['user_id']=$userId;
        $data2['create_time']=time();
        $data2['type']=2;
        $data2['post_title']='关键词【'.$data['post_title'].'】点击任务';
        $data2['score']=$xiaofei;
        $userMoneyQuery->insert($data2);

        return $userGuanjianciQuery;
    }
    public function tixianadd($data)
    {
        $userId               = cmf_get_current_user_id();
        $userTixianQuery            = Db::name("tixian_post");
        $data['user_id']     = $userId;
        $data['create_time']     = time();
        $userTixianQuery->insert($data);

        //余额减少
        $userQuery            = Db::name("user");
        $where=[];
        $where['id']=$userId;
        $coin=$userQuery->where($where)->find();
        $userQuery->where($where)->update(array('coin'=>$coin['coin']-$data['post_jine']));

        //增加明细记录
        $userMoneyQuery            = Db::name("user_money_log");
        $data2=[];
        $data2['user_id']=$userId;
        $data2['create_time']=time();
        $data2['type']=3;
        $data2['post_title']='申请提现';
        $data2['jine']=$data['post_jine'];
        $userMoneyQuery->insert($data2);

        return $userQuery;
    }
    public function guanjianci()
    {
        $userId               = cmf_get_current_user_id();
        $userFapiaoQuery            = Db::name("guanjianci_post");

        $where['user_id']     = $userId;
        $favorites            = $userFapiaoQuery->where($where)->order('create_time desc')->paginate(10);
        $data['page']         = $favorites->render();

        $data['lists']        = $favorites->items();
        return $data;
    }
    public function fapiao()
    {
        $userId               = cmf_get_current_user_id();
        $userFapiaoQuery            = Db::name("user_fapiao_post");

        $where['user_id']     = $userId;
        $favorites            = $userFapiaoQuery->where($where)->order('id desc')->paginate(10);
        $data['page']         = $favorites->render();

        $data['lists']        = $favorites->items();
        return $data;
    }
    public function jiangjin()
    {
        $userId               = cmf_get_current_user_id();
        $userMoneyQuery            = Db::name("user_money_log");
        $wh =[];
        $userQuery            = Db::name("user");
        $where['parent_id']     = $userId;
        $favorites            = $userQuery->field("id")->where($where)->select();
        $Y=date('Y');
        $Y2=date('Y',strtotime('+1 year'));
        $start=strtotime($Y.'-1-1');
        $end=strtotime($Y2.'-1-1');

        $list=0;
        foreach($favorites as $v){
            $wh['user_id'] = ['=',$v['id']];
            $wh['type'] = ['=',1];
            $wh['create_time'] = [['>= time', $start], ['<= time', $end]];
            $jine = $userMoneyQuery->where($wh)->field('sum(jine) as je')->select();
            $list+=$jine[0]['je'];
        }

        return $list;
    }

    public function comments()
    {
        $userId               = cmf_get_current_user_id();
        $userQuery            = Db::name("Comment");
        $where['user_id']     = $userId;
        $where['delete_time'] = 0;
        $favorites            = $userQuery->where($where)->order('id desc')->paginate(10);
        $data['page']         = $favorites->render();
        $data['lists']        = $favorites->items();
        return $data;
    }

    public function fapiaoadd($data)
    {
        $userId               = cmf_get_current_user_id();
        $userQuery            = Db::name("user_fapiao_post");
        $data['user_id']     = $userId;
        $data['create_time']     = time();
        $userQuery->insert($data);
        return $userQuery;
    }
    public function deleteTuandui($id)
    {
//        $userId              = cmf_get_current_user_id();
        $userQuery           = Db::name("user");
        $where['id']         = $id;
//        $where['user_id']    = $userId;
        $data['parent_id'] = 0;
        $userQuery->where($where)->update($data);
        return $data;
    }

    public function deleteComment($id)
    {
        $userId              = cmf_get_current_user_id();
        $userQuery           = Db::name("Comment");
        $where['id']         = $id;
        $where['user_id']    = $userId;
        $data['delete_time'] = time();
        $userQuery->where($where)->update($data);
        return $data;
    }

    /**
     * 绑定用户手机号
     */
    public function bindingMobile($user)
    {
        $userId      = cmf_get_current_user_id();
        $mobileCount = $this->where('mobile', $user['mobile'])->count();
        if ($mobileCount > 0) {
            return 2; //手机已经存在
        } else {
            Db::name("user")->where('id', $userId)->update($user);
            $data = Db::name("user")->where('id', $userId)->find();
            cmf_update_current_user($data);
        }

        return 0;
    }

    /**
     * 绑定用户邮箱
     */
    public function bindingEmail($user)
    {
        $userId     = cmf_get_current_user_id();
        $emailCount = $this->where('user_email', $user['user_email'])->count();
        if ($emailCount > 0) {
            return 2; //邮箱已经存在
        } else {
            Db::name("user")->where('id', $userId)->update($user);
            $data = Db::name("user")->where('id', $userId)->find();
            cmf_update_current_user($data);
        }

        return 0;
    }
}
