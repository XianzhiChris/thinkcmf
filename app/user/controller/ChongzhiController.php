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

class ChongzhiController extends UserBaseController
{
    function _initialize()
    {
        parent::_initialize();
    }
    /**
     * 个人中心账户充值
     */
    public function index()
    {
        $user = cmf_get_current_user();
        $this->assign($user);
        $user_id = cmf_get_current_user_id();
        $this->assign('user_id',$user_id);
        $userQuery=Db::name('user');
        $coin=$userQuery->field('score')->where(array('id'=>$user_id))->find();
        $this->assign('score',$coin['score']);

        return $this->fetch();
    }
    /**
     * 进行积分充值
     */
    public function chongzhi()
    {
        $user_id = cmf_get_current_user_id();
        $data = $this->request->param();
        switch ($data['jifen']){
            case 1:
                $jifen=2200;
                $jine=200;
                break;
            case 2:
                $jifen=6000;
                $jine=500;
                break;
            case 3:
                $jifen=12000;
                $jine=1000;
                break;
            case 4:
                $jifen=25000;
                $jine=2000;
                break;
            case 5:
                $jifen=70000;
                $jine=5000;
                break;
            case 6:
                $jifen=160000;
                $jine=10000;
                break;
        }

        switch ($data['type']){
            case 'zfb':
                $type='支付宝';
                $pay_url='http://www.alipay.com';
                break;
            case 'wx':
                $type='微信';
                $pay_url='http://www.weixin.com';
                break;
            case 'yhk':
                $type='银行卡';
                $pay_url='http://www.pay.com';
                break;
        }

        //todo:跳转到支付接口
        $pay_result='ok';  //支付接口返回结果

        $userQuery = Db::name('user');
        $userMoneyQuery=Db::name('user_money_log');
        $moneyData=['user_id'=>$user_id,'create_time'=>time(),'type'=>1,'post_title'=>'充值【'.$jifen.'】积分','score'=>$jifen,'jine'=>$jine,'remark'=>$type];

        if($pay_result=='ok') {

            $userqueryresult=$userQuery->where('id',$user_id)->setInc('score',$jifen);
            $userqueryresult2=$userQuery->where('id',$user_id)->setInc('coin',$jine);
            $usermoneyqueryresult=$userMoneyQuery->insert($moneyData);
            if($userqueryresult&&$usermoneyqueryresult&&$userqueryresult2) {
                return 'ok';
            }
        }
    }

}