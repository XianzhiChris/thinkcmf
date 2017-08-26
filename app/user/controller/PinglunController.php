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
namespace app\user\controller;

use cmf\controller\UserBaseController;
use app\user\model\UserModel;
use think\Db;
use think\Model;

class PinglunController extends UserBaseController
{
    function _initialize()
    {
        parent::_initialize();
    }
    /**
     * 个人中心评论管理
     */
    public function index()
    {
        $editData = new UserModel();
        $data = $editData->pinglun();
        $user = cmf_get_current_user();

        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $data['lists']);
        return $this->fetch();
    }

    /**
     * 添加评论
     */
    public function add()
    {
        $tiwen_url='';
        //从提问转过来
        $data = $this->request->param();
        if(isset($data['tiwen_id'])) {
            $tiwen_id = $data['tiwen_id'];
            $taskQuery = Db::name('zhidaotaskdata');
            $task = $taskQuery->field('return_url')->where('pinglun_id', $tiwen_id)->find();
            $tiwen_url=$task['return_url'];
        }

        $user = cmf_get_current_user();
        $this->assign($user);
        $userQuery=Db::name('user');
        $coin=$userQuery->field('score')->where(array('id'=>$user['id']))->find();
        $this->assign('myscore',$coin['score']);
        $this->assign('tiwen_url',$tiwen_url);
        return $this->fetch();
    }

    public function addPost()
    {
        $data = $this->request->param();
        $editData = new UserModel();
        $res=$editData->pinglunadd($data);
        if(strpos($res, "err:") !== false){
            $re=explode(':',$res);
            $this->error('添加失败！内容包含禁止词语【'.$re[1].'】');
        }
        $this->success('添加成功！', url('user/pinglun/index'));
    }

    public function pinglunzhixing()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        $data = $this->request->param();
        //当前任务名称
        $PinglunQuery            = Db::name("pinglun_post");
        $title=$PinglunQuery->field('post_title')->where('id',$data['id'])->find();
        $this->assign('title',$title['post_title']);

        $editData = new UserModel();
        $data = $editData->pinglunzhixing($data);
        $user = cmf_get_current_user();
        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $data['lists']);
        return $this->fetch();
    }

    public function pinglunshibai(){
        $data = $this->request->param();
        //获取失败评论信息
        $renwuQuery=Db::name('zhidaotaskdata');
        $pinglun=$renwuQuery->field('id,pinglun_id,content_id,get_url,content')->where(['pinglun_id'=>$data['id'],'delete_time'=>0,'return_code'=>[['neq',1],['neq','']]])->select();
        $CookieQuery=Db::name('zhidaobaiducook');
        foreach($pinglun as $v) {
            //标记失败任务
           $renwuQuery->where('id', $v['id'])->update(['delete_time'=>time()]);
            //随机百度cookie
            $baidu_cookie = $CookieQuery->field('baidu_cookie')->where('cookie_fail','<',10)->order('rand()')->limit(1)->find();

            //生成任务列表
            $renwudata = ['pinglun_id' => $v['pinglun_id'], 'content_id' => $v['content_id'], 'zhidao' => 'hd', 'get_url' => $v['get_url'], 'content' => $v['content'], 'baidu_cookie' => $baidu_cookie['baidu_cookie'], 'create_time' => time()];
            $renwuQuery->insert($renwudata);
        }

        $this->success('添加成功！', url('user/pinglun/index'));
    }
    /**
     * 评论内容数量
     */
    public function content_count()
    {
        $data = $this->request->param();
        $text_path=$data['text_path'];  //txt文件路径

        $myFile = file('.'.$text_path);
        $count=count($myFile);
        $data=[];
        foreach($myFile as $v){
            $str = mb_convert_encoding($v, 'utf-8', 'gbk');
            $str = trim($str);
            $str = str_replace(array("\r\n", "\r", "\n", "\t", "　","'"), "", $str);//去除换行符

            if ($str) {
                $data[]=$str;
            }
        }
        $result=['count'=>count($data),'data'=>$data];
        return json_encode($result);
    }
    /**
     * 个人中心提问管理
     */
    public function tiwen()
    {
        $editData = new UserModel();
        $data = $editData->pinglunTiwen();
//        //获取任务执行情况
//        $taskQuery=Db::name('zhidaotaskdata');
//        $list=[];
//        foreach($data['lists'] as $v){
//            $return_code=$taskQuery->field('return_code,return_url')->where(['pinglun_id'=>$v['id'],'delete_time'=>0])->order('id desc')->limit(1)->find();
//            $v['return_code']=$return_code['return_code'];
//            $v['return_url']=$return_code['return_url'];
//            $list[]=$v;
//        }

        $user = cmf_get_current_user();
        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $data['lists']);
        return $this->fetch();
    }
    /**
     * 添加提问
     */
    public function tiwenadd()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        $userQuery=Db::name('user');
        $coin=$userQuery->field('score')->where(array('id'=>$user['id']))->find();
        $this->assign('myscore',$coin['score']);
        return $this->fetch();
    }
    public function tiwenaddPost()
    {
        $data = $this->request->param();
        $editData = new UserModel();
        $res=$editData->pingluntiwenadd($data);
        if(strpos($res, "err:") !== false){
            $re=explode(':',$res);
            $this->error('添加失败！内容包含禁止词语【'.$re[1].'】');
        }
        $this->success('添加成功！', url('user/pinglun/tiwen'));
    }

    public function tiwenshibai(){
        $data = $this->request->param();
        //获取评论信息
        $renwuQuery=Db::name('pinglun_post');
        $pinglun=$renwuQuery->field('post_title,post_content,post_cookie')->where('id',$data['id'])->find();
        //失败任务标记
        $renwuQuery=Db::name('zhidaotaskdata');
        $renwuQuery->where('pinglun_id',$data['id'])->update(['delete_time',time()]);
        //插入新评论任务
        $renwudata = ['pinglun_id' => $data['id'], 'zhidao' => 'tw', 'title' => base64_encode($pinglun['post_title']), 'content'=>base64_encode($pinglun['post_content']),'baidu_cookie' => base64_encode($pinglun['post_cookie']), 'create_time' => time()];
        $renwuQuery->insert($renwudata);

        $this->success('添加成功！', url('user/pinglun/tiwen'));
    }
}