<?php
/* *
 * 功能：支付宝服务器异步通知页面
 * 版本：2.0
 * 修改日期：2017-05-01
 * 说明：
 * 以下代码只是为了方便商户测试而提供的样例代码，商户可以根据自己网站的需要，按照技术文档编写,并非一定要使用该代码。

 *************************页面功能说明*************************
 * 创建该页面文件时，请留心该页面文件中无任何HTML代码及空格。
 * 该页面不能在本机电脑测试，请到服务器上做测试。请确保外部可以访问该页面。
 * 如果没有收到该页面返回的 success 信息，支付宝会在24小时内按一定的时间策略重发通知
 */

require_once 'config.php';
require_once 'pagepay/service/AlipayTradeService.php';
$mysqli = new mysqli('localhost','root','root','yumishe') or die('lianjieshibai');

$arr=$_POST;
$alipaySevice = new AlipayTradeService($config); 
$alipaySevice->writeLog(var_export($_POST,true));
$result = $alipaySevice->check($arr);
//file_put_contents('a.txt',json_encode($arr));

if($result) {//验证成功

    $user_pay=$mysqli->query('select * from cmf_user_pay_order where order_id='.$_POST['out_trade_no']);
    $user_pay_res=$user_pay->fetch_assoc();

    //验证数据准确性
    if($user_pay_res['coin']==intval($_POST['total_amount']) && $config['app_id']==$_POST['app_id'] && ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS')){

        //写入订单表
        $mysqli->query("update cmf_user_pay_order set status=1,trade_no='".$_POST['trade_no']."',pay_time=".time()." where order_id=".$_POST['out_trade_no']);

        switch ($user_pay_res['jifen']){
            //开户
            case 11: //体验者
                $group=1;
                $group_name='体验者';
                break;
            case 12: //消费者
                $group=2;
                $group_name='消费者';
                break;
            case 13: //合伙人
                $group=3;
                $group_name='合伙人';
                break;
        }

        $post_title='充值【'.$user_pay_res['score'].'】米币';
        if($user_pay_res['jifen']>10){//开户
            $mysqli->query("update cmf_user set user_group=".$group." where id=".$user_pay_res['user_id']);
            $post_title='开户【'.$group_name.'】'.$user_pay_res['score'].'米币';
        }
        //会员积分和金额调整
        $mysqli->query("update cmf_user set score=score+".$user_pay_res['score'].",coin=coin+".$user_pay_res['coin']." where id=".$user_pay_res['user_id']);

        //明细增加
        $mysqli->query("insert into cmf_user_money_log (user_id,create_time,type,post_title,score,jine,remark) value(".$user_pay_res['user_id'].",".time().",1,'".$post_title."',".$user_pay_res['score'].",".$user_pay_res['coin'].",'支付宝')");

        //当前用户信息
        $user_sql=$mysqli->query("select * from cmf_user where id=".$user_pay_res['user_id']);
        $user=$user_sql->fetch_assoc();

        if($user_pay_res['jifen']>11){//判断推荐者，进行返还

            $parent_id=$user['parent_id']; //上级id
            if($parent_id){
                $parent_sql=$mysqli->query("select user_group,parent_id from cmf_user where id=".$parent_id);
                $parent_group=$parent_sql->fetch_assoc();
//                    $parent_group=$userQuery->field('user_group,parent_id')->where('id', $parent_id)->find();//上级级别和上上级ID
                $parent_parent_sql=$mysqli->query("select user_group from cmf_user where id=".$parent_group['parent_id']);
                $parent_parent_group=$parent_parent_sql->fetch_assoc();
//                    $parent_parent_group=$userQuery->field('user_group')->where('id', $parent_group['parent_id'])->find();//上上级级别
                if($parent_group['user_group']==2){//如果上级是消费者
                    if($user_pay_res['jifen']==12){
                        $yongjin=680;
                    }
                    if($user_pay_res['jifen']==13){
                        $yongjin=980;
                    }

                    //上级推荐人数判断，更改上级级别————消费者推荐3人注册使用米神后，身份自动升级为合伙人
                    //查询已推荐人数
                    $tui_ren=$mysqli->query("select count(*) as count from cmf_user where parent_id=".$parent_id." and user_group in (2,3)")->fetch_object()->count;
//                        $tui_ren=$userQuery->where(['parent_id'=>$parent_id,'user_group'=>['in','2,3']])->count();
                    //已推荐人数大于等于2人，加上这个，是3个人以上
                    if($tui_ren>=2){//应该是2，防止上次升级失败，这里多次判断
                        $mysqli->query("update cmf_user set user_group=3 where id=".$user_pay_res['user_id']);
//                            $userQuery->where('id',$user_id)->update(['user_group'=>3]);
                        //增加消费明细，升级说明
                        $mysqli->query("insert into cmf_user_money_log (user_id,create_time,type,post_title,score,jine,remark) value(".$user_pay_res['user_id'].",".time().",1,'推荐已达3人，自动升级为合伙人',0,0,'')");

                    }

                }
                if($parent_group['user_group']==3 || $parent_group['user_group']==4){//如果上级是合伙人或运营中心
                    if($user_pay_res['jifen']==12){
                        $yongjin=3000;
                    }
                    if($user_pay_res['jifen']==13){
                        $yongjin=4000;
                    }
                }
                //上级佣金返还 $parent_id
                $mysqli->query("update cmf_user set score=score+".$yongjin." where id=".$parent_id);
//                    $userQuery->where('id',$parent_id)->setInc('score',$yongjin);
                //增加上级消费明细
                $mysqli->query("insert into cmf_user_money_log (user_id,create_time,type,post_title,score,jine,remark) value(".$parent_id.",".time().",1,'推荐用户【".$user['mobile']."】奖励佣金',".$yongjin.",0,'')");
//                    $moneyData=['user_id'=>$parent_id,'create_time'=>time(),'type'=>1,'post_title'=>'推荐用户【'.$user_mobile.'】奖励佣金','score'=>$yongjin];
//                    $usermoneyqueryresult=$userMoneyQuery->insert($moneyData);

                if($parent_parent_group['user_group']==3 || $parent_parent_group['user_group']==4){//如果上级的上级是合伙人或运营中心
                    $yongjin=1000;
                    //上上级佣金返还 $parent_group['parent_id']
                    $mysqli->query("update cmf_user set score=score+".$yongjin." where id=".$parent_group['parent_id']);
//                        $userQuery->where('id',$parent_group['parent_id'])->setInc('score',$yongjin);
                    //增加上上级消费明细
                    $mysqli->query("insert into cmf_user_money_log (user_id,create_time,type,post_title,score,jine,remark) value(".$parent_group['parent_id'].",".time().",1,'奖励推荐开户佣金',".$yongjin.",0,'')");
//                        $moneyData=['user_id'=>$parent_group['parent_id'],'create_time'=>time(),'type'=>1,'post_title'=>'奖励推荐开户佣金','score'=>$yongjin];
//                        $usermoneyqueryresult=$userMoneyQuery->insert($moneyData);
                }
            }
        }
        //普通充值，运营中心直属用户充值返现
        if($user_pay_res['jifen']<10){
            $parent_id=$user['parent_id']; //上级id
            $parent_sql=$mysqli->query("select user_group,parent_id from cmf_user where id=".$parent_id);
            $parent_group=$parent_sql->fetch_assoc();
//                $parent_group=$userQuery->field('user_group,parent_id')->where('id', $parent_id)->find();//上级级别
            if($parent_group['user_group']==4){//如果上级是运营中心
                $yongjin=$user_pay_res['score']*0.2;
                //充值返现 $parent_id
                $mysqli->query("update cmf_user set score=score+".$yongjin." where id=".$parent_id);
//                    $userQuery->where('id',$parent_id)->setInc('score',$yongjin);
                //增加上级消费明细
                $mysqli->query("insert into cmf_user_money_log (user_id,create_time,type,post_title,score,jine,remark) value(".$parent_id.",".time().",1,'奖励推荐开户充值佣金',".$yongjin.",0,'')");
//                    $moneyData=['user_id'=>$parent_id,'create_time'=>time(),'type'=>1,'post_title'=>'奖励推荐开户充值佣金','score'=>$yongjin];
//                    $usermoneyqueryresult=$userMoneyQuery->insert($moneyData);
            }
        }

        echo "success";
    }else{
        //验证失败
        //写入订单表
        $mysqli->query("update cmf_user_pay_order set status=2,trade_no='".$_POST['trade_no']."',pay_time=".time()." where order_id=".$_POST['out_trade_no']);
//    echo "<script>alert('充值失败');self.location.href='/user/chongzhi/index.html'</script>";
        echo "fail";
    }

}else {
    //验证失败
    //写入订单表
    $mysqli->query("update cmf_user_pay_order set status=2,trade_no='".$_POST['trade_no']."',pay_time=".time()." where order_id=".$_POST['out_trade_no']);
//    echo "<script>alert('充值失败');self.location.href='/user/chongzhi/index.html'</script>";
    echo "fail";

}
?>