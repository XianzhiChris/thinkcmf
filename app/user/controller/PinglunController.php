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
        $user = cmf_get_current_user();
        $this->assign($user);
        $userQuery=Db::name('user');
        $coin=$userQuery->field('score')->where(array('id'=>$user['id']))->find();
        $this->assign('myscore',$coin['score']);
        return $this->fetch();
    }

    public function addPost()
    {
        $data = $this->request->param();
        $editData = new UserModel();
        $editData->pinglunadd($data);

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


}