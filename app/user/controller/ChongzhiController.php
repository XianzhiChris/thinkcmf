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
        $coin=$userQuery->field('score,user_group')->where(array('id'=>$user_id))->find();
        $this->assign('score',$coin['score']);
        $this->assign('usergroup',$coin['user_group']);

        return $this->fetch();
    }
    //支付状态
    public function status(){
        $data = $this->request->param();
        $status=Db::name('user_pay_order')->field('status')->where('order_id',$data['WIDout_trade_no'])->find();
        if($status['status']===1){
            echo "ok";
        }

    }
    /**
     * 进行积分充值
     */
    public function chongzhi()
    {
        $user_id = cmf_get_current_user_id();
        $user = cmf_get_current_user();
        $data = $this->request->param();
        $user_mobile=$user['mobile'];
        switch ($data['jifen']){
            //充值
            case 1:
                $jifen=1000;
                $jine=1000;
                break;
            case 2:
                $jifen=2000;
                $jine=1960;
                break;
            case 3:
                $jifen=5000;
                $jine=4750;
                break;
            case 4:
                $jifen=10000;
                $jine=8800;
                break;
//            case 5:
//                $jifen=70000;
//                $jine=5000;
//                break;
//            case 6:
//                $jifen=160000;
//                $jine=10000;
//                break;
        //开户
            case 11: //体验者
                $jifen=200;
                $jine=200;
                $group=1;
                $group_name='体验者';
                break;
            case 12: //消费者
                $jifen=3000;
                $jine=6800;
                $group=2;
                $group_name='消费者';
                break;
            case 13: //合伙人
                $jifen=3000;
                $jine=9800;
                $group=3;
                $group_name='合伙人';
                break;
        }

        switch ($data['type']){
            case 'zfb':
                $type='支付宝';
                $pay_url='/alipay/pagepay/pagepay.php';
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
        $post_title='充值【'.$jifen.'】米币';
        if($data['jifen']>10){//开户
            $post_title='开户【'.$group_name.'】'.$jifen.'米币';
        }

        $WIDout_trade_no=$data['WIDout_trade_no']; //订单号
        $WIDsubject=$post_title;   //订单名称
        $WIDtotal_amount=$jine;    //订单金额
        $WIDbody='';                //订单描述

        //写入订单信息 user_pay_order
        $user_pay=Db::name('user_pay_order')->insert(['user_id'=>$user_id,'order_id'=>$WIDout_trade_no,'score'=>$jifen,'coin'=>$jine,'type'=>$data['type'],'create_time'=>time(),'jifen'=>$data['jifen']]);

        //todo:跳转到支付接口
        if($user_pay) {
            $this->redirect('http://' . $_SERVER['HTTP_HOST'] . $pay_url . '?WIDout_trade_no=' . $WIDout_trade_no . '&WIDsubject=' . $WIDsubject . '&WIDtotal_amount=' . $WIDtotal_amount . '&WIDbody=' . $WIDbody);
        }else{
            $this->error('提交失败');
        }
        exit;


        $pay_result='ok';  //支付接口返回结果

        $userQuery = Db::name('user');
        $userMoneyQuery=Db::name('user_money_log');
        $userGroupQuery=Db::name('user_group');

        if($pay_result=='ok') {
            $post_title='充值【'.$jifen.'】米币';
            if($data['jifen']>10){//开户
                $userQuery->where('id',$user_id)->update(['user_group'=>$group]);
                $post_title='开户【'.$group_name.'】'.$jifen.'米币';
            }
            //会员积分和金额调整
            $userqueryresult=$userQuery->where('id',$user_id)->setInc('score',$jifen);
            $userqueryresult2=$userQuery->where('id',$user_id)->setInc('coin',$jine);
            //明细增加
            $moneyData=['user_id'=>$user_id,'create_time'=>time(),'type'=>1,'post_title'=>$post_title,'score'=>$jifen,'jine'=>$jine,'remark'=>$type];
            $usermoneyqueryresult=$userMoneyQuery->insert($moneyData);

            if($data['jifen']>11){//判断推荐者，进行返还
                $parent_id=$user['parent_id']; //上级id
                if($parent_id){
                    $parent_group=$userQuery->field('user_group,parent_id')->where('id', $parent_id)->find();//上级级别和上上级ID
                    $parent_parent_group=$userQuery->field('user_group')->where('id', $parent_group['parent_id'])->find();//上上级级别
                    if($parent_group['user_group']==2){//如果上级是消费者
                        if($data['jifen']==12){
                            $yongjin=680;
                        }
                        if($data['jifen']==13){
                            $yongjin=980;
                        }

                        //上级推荐人数判断，更改上级级别————消费者推荐3人注册使用米神后，身份自动升级为合伙人
                        //查询已推荐人数
                        $tui_ren=$userQuery->where(['parent_id'=>$parent_id,'user_group'=>['in','2,3']])->count();
                        //已推荐人数大于等于2人，加上这个，是3个人以上
                        if($tui_ren>=2){//应该是2，防止上次升级失败，这里多次判断
                            $userQuery->where('id',$user_id)->update(['user_group'=>3]);
                            //增加消费明细，升级说明
                            $moneyData=['user_id'=>$user_id,'create_time'=>time(),'type'=>1,'post_title'=>'推荐已达3人，自动升级为合伙人'];
                            $usermoneyqueryresult=$userMoneyQuery->insert($moneyData);
                        }

                    }
                    if($parent_group['user_group']==3 || $parent_group['user_group']==4){//如果上级是合伙人或运营中心
                        if($data['jifen']==12){
                            $yongjin=3000;
                        }
                        if($data['jifen']==13){
                            $yongjin=4000;
                        }
                    }
                    //上级佣金返还 $parent_id
                    $userQuery->where('id',$parent_id)->setInc('score',$yongjin);
                    //增加上级消费明细
                    $moneyData=['user_id'=>$parent_id,'create_time'=>time(),'type'=>1,'post_title'=>'推荐用户【'.$user_mobile.'】奖励佣金','score'=>$yongjin];
                    $usermoneyqueryresult=$userMoneyQuery->insert($moneyData);

                    if($parent_parent_group['user_group']==3 || $parent_parent_group['user_group']==4){//如果上级的上级是合伙人或运营中心
                        $yongjin=1000;
                        //上上级佣金返还 $parent_group['parent_id']
                        $userQuery->where('id',$parent_group['parent_id'])->setInc('score',$yongjin);
                        //增加上上级消费明细
                        $moneyData=['user_id'=>$parent_group['parent_id'],'create_time'=>time(),'type'=>1,'post_title'=>'奖励推荐开户佣金','score'=>$yongjin];
                        $usermoneyqueryresult=$userMoneyQuery->insert($moneyData);
                    }
                }
            }
            //普通充值，运营中心直属用户充值返现
            if($data['jifen']<10){
                $parent_id=$user['parent_id']; //上级id
                $parent_group=$userQuery->field('user_group,parent_id')->where('id', $parent_id)->find();//上级级别
                if($parent_group['user_group']==4){//如果上级是运营中心
                    $yongjin=$jifen*0.2;
                    //充值返现 $parent_id
                    $userQuery->where('id',$parent_id)->setInc('score',$yongjin);
                    //增加上级消费明细
                    $moneyData=['user_id'=>$parent_id,'create_time'=>time(),'type'=>1,'post_title'=>'奖励推荐开户充值佣金','score'=>$yongjin];
                    $usermoneyqueryresult=$userMoneyQuery->insert($moneyData);
                }
            }
            //获取会员级别条件
//            $userGroup=$userGroupQuery->field('id,post_jine')->order('post_jine desc')->select();

            //会员级别调整
//            $coin=$userQuery->field('coin')->where('id',$user_id)->find();
//            $zje=$coin['coin']+$jine;
//            $user_group=1;
//            if($zje>50000){
//                $user_group=4;
//            }elseif($zje>20000){
//                $user_group=3;
//            }elseif($zje>5000){
//                $user_group=2;
//            }
//            foreach($userGroup as $v){
//                if($zje>$v['post_jine']){
//                    $user_group=$v['id'];
//                    break;
//                }
//            }


            //给上级会员返点---返积分
//            $parent_id=$user['parent_id'];
//            if(isset($parent_id)) {
//                $parent_group=$userQuery->field('user_group')->where('id', $parent_id)->find();
//                $fenchengbili=$userGroupQuery->field('fenchengbili')->where('id', $parent_group['user_group'])->find();
////                switch ($parent_group['user_group']){
////                    case 1:
////                        $fandian=0.03;
////                        break;
////                    case 2:
////                        $fandian=0.06;
////                        break;
////                    case 3:
////                        $fandian=0.09;
////                        break;
////                    case 4:
////                        $fandian=0.12;
////                        break;
////                }
//                $fandian=$fenchengbili['fenchengbili'];
//                $userQuery->where('id',$parent_id)->setInc('score',$jifen*$fandian);
//                //明细增加,赠送为4
//                $moneyData2=['user_id'=>$parent_id,'create_time'=>time(),'type'=>4,'post_title'=>'团队成员【'.$user['user_nickname'].'】充值赠送米币','score'=>$jifen*$fandian];
//                $userMoneyQuery->insert($moneyData2);
//            }


            if($userqueryresult&&$usermoneyqueryresult&&$userqueryresult2) {
                return 'ok';
            }
        }
    }

}