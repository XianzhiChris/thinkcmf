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
     * 个人中心关键词管理
     */
    public function index()
    {
        $editData = new UserModel();
        $data = $editData->guanjianci();
        $user = cmf_get_current_user();
        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $data['lists']);
        return $this->fetch();
    }

    /**
     * 添加关键词
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
        $editData = new UserModel();
        $editData->guanjianciadd($data);

        $this->success('添加成功！', url('user/guanjianci/index'));
    }


}