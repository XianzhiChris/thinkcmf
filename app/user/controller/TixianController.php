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

class TixianController extends UserBaseController
{
    /**
     * 个人中心提现管理
     */
    public function index()
    {
        $editData = new UserModel();
        $data = $editData->tixian();
        $user = cmf_get_current_user();
        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $data['lists']);
        return $this->fetch();
    }

    /**
     * 添加提现
     */
    public function add()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        $userQuery=Db::name('user');
        $coin=$userQuery->field('coin')->where(array('id'=>$user['id']))->find();
        $this->assign('mycoin',$coin['coin']);
        return $this->fetch();
    }

    public function addPost()
    {
        $data = $this->request->param();
        $user = cmf_get_current_user();
//        $this->assign($user);
        if(intval($data['post_jine'])==0){
            $this->success('添加失败，提现金额不能为0！', url('user/tixian/index'));
        }
        if($data['post_jine']>$user['coin']){
            $this->success('添加失败，提现金额大于余额！', url('user/tixian/index'));
        }

        $editData = new UserModel();
        $editData->tixianadd($data);

        $this->success('添加成功！', url('user/tixian/index'));
    }
}