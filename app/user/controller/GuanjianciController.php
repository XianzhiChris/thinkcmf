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

class GuanjianciController extends UserBaseController
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
        $user = cmf_get_current_user();
        $userQuery=Db::name('user');
        $editData = new UserModel();
        $data = $editData->guanjianci();
        //查询任务执行情况
        $list=[];
        $taskQuery=Db::name('taskdjdata');
        foreach($data['lists'] as $v){
            $task_ok=$taskQuery->where(['renwu_id'=>$v['id'],'return_ip'=>['neq','']])->cache(600)->count();
            $v['task_ok_num']=$task_ok;
            $list[]=$v;

            //todo:判断是否结束，然后进行费用结算
            $zsl=$v['post_dianjicishu'] * $v['post_tianshu'];
            if($task_ok==$zsl){ //如果结束
//                $this->jiesuan($v['id']);
                $chushi=intval($v['post_chushipaiming']);//初始排名
                $shishi=intval($v['shishipaiming']);//当前排名
                $feiyong=$v['feiyong'];//当前费用
                $jiesuan=0;
                if($shishi>=$chushi){//排名没有变化或下降
                    $jiesuan=$feiyong;//费用全额退回
                }
                if($shishi>=90){//第10页及以后
                    $jiesuan=$feiyong;//费用全额退回
                }
                if($shishi>=80){//第9页
                    $jiesuan=$feiyong*0.8;//费用20%，退回80%
                }
                if($shishi>=70){//第8页
                    $jiesuan=$feiyong*0.7;//费用30%，退回70%
                }
                if($shishi>=60){//第7页
                    $jiesuan=$feiyong*0.6;//费用40%，退回60%
                }
                if($shishi>=50){//第6页
                    $jiesuan=$feiyong*0.5;//费用50%，退回50%
                }
                if($shishi>=40){//第5页
                    $jiesuan=$feiyong*0.4;//费用60%，退回40%
                }
                if($shishi>=30){//第4页
                    $jiesuan=$feiyong*0.3;//费用70%，退回30%
                }
                if($shishi>=20){//第3页
                    $jiesuan=$feiyong*0.2;//费用80%，退回20%
                }
                if($shishi>=10){//第2页
                    $jiesuan=$feiyong*0.1;//费用90%，退回10%
                }
                //费用退回操作
                if($jiesuan>0){
                    //米币增加
                    $where['id']=$user['id'];
                    $userQuery->where($where)->setInc('score', $jiesuan);
                    //增加消费明细记录
                    $userMoneyQuery            = Db::name("user_money_log");
                    $data2=[];
                    $data2['user_id']=$user['id'];
                    $data2['create_time']=time();
                    $data2['type']=1;
                    $data2['post_title']='关键词【'.$v['post_title'].'】点击任务费用结算';
                    $data2['score']=$jiesuan;
                    $userMoneyQuery->insert($data2);
                }
            }
        }


        $coin=$userQuery->field('score')->where(array('id'=>$user['id']))->cache(true)->find();
        $this->assign('myscore',$coin['score']);
        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $list);
        $jiageQuery=Db::name('user_jiage')->field('guanjianci')->where('id',1)->cache(true)->find();
        $this->assign("jiage", $jiageQuery['guanjianci']);
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
        $coin=$userQuery->field('score')->where(array('id'=>$user['id']))->find();
        $this->assign('myscore',$coin['score']);
        $jiageQuery=Db::name('user_jiage')->field('guanjianci')->where('id',1)->find();
        $this->assign("jiage", $jiageQuery['guanjianci']);
        return $this->fetch();
    }

    public function piliangadd()
    {
        $user = cmf_get_current_user();
        $this->assign($user);

        return $this->fetch();
    }
    public function piliangadd2()
    {
        $data = $this->request->param();
        $user = cmf_get_current_user();
        $this->assign($user);
        if(!isset($data['check'])){
            $this->error('请选择关键词',url('user/guanjianci/piliangadd'));
        }
        $userQuery=Db::name('user');
        $coin=$userQuery->field('score')->where(array('id'=>$user['id']))->cache(true)->find();
        $this->assign('myscore',$coin['score']);
        $this->assign('domain',$data['domain']);
        $this->assign('check',$data['check']);
        $this->assign('tal',0);
        $jiageQuery=Db::name('user_jiage')->field('guanjianci')->where('id',1)->cache(true)->find();
        $this->assign("jiage", $jiageQuery['guanjianci']);
        return $this->fetch();
    }
    public function piliangadd3()
    {
        $data = $this->request->param();
        $userId               = cmf_get_current_user_id();
        $userGuanjianciQuery            = Db::name("guanjianci_post");
        $userQuery            = Db::name("user");
        $renwuQuery=Db::name('taskdjdata');
        $cookieQuery=Db::name('baiducookie');

        //进行循环
        $tal=0;
        $count=count($data['post_biaoti']);
        //计算价格
        for($i=0;$i<$count;$i++){
            $mey=0;
            if($data['post_zhishu'][$i]<1){ //无指数
                if($data['post_chushipaiming'][$i]<100){
                    $mey=$data['post_chushipaiming'][$i]*0.11;
                }
                if($data['post_chushipaiming'][$i]<50){
                    $mey=($data['post_chushipaiming'][$i]-1)*0.104+1;
                }
            }else{
                if($data['post_chushipaiming'][$i]<100){
                    $mey=($data['post_chushipaiming'][$i]-50)*0.061+12;
                }
                if($data['post_chushipaiming'][$i]<50){
                    $mey=($data['post_chushipaiming'][$i]-1)*0.062+9;
                }
            }
            $mey*=$data['post_tianshu'][$i];
            $mey=round($mey);
            $data['feiyong'][$i]=$mey;  //当前费用
            $tal+=$mey;
        }
        //合计
        $jiageQuery=Db::name('user_jiage')->field('guanjianci')->where('id',1)->cache(true)->find();
        $xiaofei=$tal*$jiageQuery['guanjianci'];

        //查询现有积分
        $coin=$userQuery->field('score')->where(array('id'=>$userId))->cache(true)->find();
        if($coin['score']<$xiaofei){
            $this->error('米币不足！');
        }

        //积分减少
        $where=[];
        $where['id']=$userId;
        $userQuery->where($where)->setDec('score', $xiaofei);
//        $userQuery->where($where)->update(array('score'=>$coin['score']-$xiaofei));

        //加入任务
        for($i=0;$i<$count;$i++){
            $d=[];
            $d['post_type']=$data['post_type'][$i];
            $pt=$data['post_biaoti'][$i];
            $d['post_title']=$data['post_title'][$i];
            //2018.5.18 对标题符号进行过滤
            if(substr($pt,0,3) == '...'){//过滤开头...
                $pt=substr($pt,3);
            }
            if(substr($pt,-3,3) == '...'){//过滤结尾...
                $pt=substr_replace($pt,'',-3);
            }
            if(substr($pt,-1,1) == ']'){//过滤结尾]
                $pt=substr_replace($pt,'',-1);
            }
            if(substr($pt,-1,1) == '!'){//过滤结尾!
                $pt=substr_replace($pt,'',-1);
            }
            if(substr($pt,-1,1) == '_'){//过滤结尾_
                $pt=substr_replace($pt,'',-1);
            }
            if(substr($pt,-1,1) == ')'){//过滤结尾)
                $pt=substr_replace($pt,'',-1);
            }
            if(substr($pt,-1,1) == '#'){//过滤结尾#
                $pt=substr_replace($pt,'',-1);
            }
            $d['post_biaoti']=$pt;
            //
            $d['post_url']=$data['post_url'][$i];
            $d['post_chushipaiming']=$d['shishipaiming']=$data['post_chushipaiming'][$i];
            $d['post_dianjicishu']=$data['post_dianjicishu'][$i];
            $d['post_tianshu']=$data['post_tianshu'][$i];
            $d['post_zhishu']=$data['post_zhishu'][$i];
            $d['feiyong']=$data['feiyong'][$i];//当前费用
            for($t=0;$t<24;$t++) {
                $d['txt_time'.$t]=0;
            }
            if ($d['post_dianjicishu'] <= 24) {
                for ($t = 0; $t < $d['post_dianjicishu']; $t++) {
                    $d["txt_time".$t]=1;
                }
            }else {
                $pj = intval($d['post_dianjicishu'] / 24);
                for ($t=0;$t<24;$t++) {
                    $d["txt_time".$t]=$pj;
                }
                $shengyu= $d['post_dianjicishu']-$pj * 24;
                for ($t=0;$t<$shengyu;$t++){
                    $current_val=$d["txt_time".$t];
                    $d["txt_time".$t]=intval($current_val)+1;
                }
            }

            //加入任务表
            $d['user_id']     = $userId;
            $d['create_time']     = time();
//            $d['task_num']=$d['post_dianjicishu']*$d['post_tianshu'];
            $userGuanjianciQuery->insert($d);
            $renwu_id=$userGuanjianciQuery->getLastInsID();

            //生成任务列表
            switch ($d['post_type']){
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
            $baidu_cookie_list = $cookieQuery->field('baidu_cookie')->where(['cookie_fail'=>['lt', 10],'delete_time'=>0])->order('id desc')->cache(true)->select();
            foreach($baidu_cookie_list as $v){
                $baidu_arr[]=$v;
            }
            $rand_key=array_rand($baidu_arr,1);
            $baidu_cookie=$baidu_arr[$rand_key]['baidu_cookie'];
//var_dump($baidu_cookie);exit;
            for($x=0;$x<$d['post_tianshu'];$x++){
                $rq=strtotime(date('Y-m-d' , strtotime('+'.$x.' day')));
                for($t=0;$t<24;$t++) {
                    $renwudata=[];
                    for ($j = 0; $j < $d['txt_time' . $t]; $j++) {
                        $xs = $t * 3600;
                        //随机百度cookie
//                        $baidu_cookie = $cookieQuery->field('baidu_cookie')->where(['cookie_fail'=>['lt', 10],'delete_time'=>0])->order('rand()')->limit(1)->find();
//                        $rand_key=array_rand($baidu_arr,1);
//                        $baidu_cookie=$baidu_arr[$rand_key[0]]['baidu_cookie'];

                        $renwudata[] = ['renwu_id' => $renwu_id, 'task_time' => $rq + $xs, 'sou' => base64_encode($sou), 'key' => base64_encode($d['post_title']), 'title' => base64_encode($d['post_biaoti']), 'baidu_cookie' => $baidu_cookie, 'create_time' => $time];
                    }
                    $renwuQuery->insertAll($renwudata);
                }
            }
        }

        //增加消费明细记录
        $userMoneyQuery            = Db::name("user_money_log");
        $data2=[];
        $data2['user_id']=$userId;
        $data2['create_time']=time();
        $data2['type']=2;
        $data2['post_title']='关键词点击任务';
        $data2['score']=$xiaofei;
        $userMoneyQuery->insert($data2);

        $this->success('添加成功！', url('user/guanjianci/index'));
    }

    public function addPost()
    {
        $data = $this->request->param();
        $editData = new UserModel();
        $editData->guanjianciadd($data);

        $this->success('添加成功！', url('user/guanjianci/index'));
    }

    public function zhishu()
    {
        $data = $this->request->param();
        $title=urlencode(strtolower($data['post_title']));
        $key_json=file_get_contents("http://api.91cha.com/index?key=34909fe411a14cbd9f0539cf27fbf6a3&kws=".$title);
        $key=json_decode($key_json,true);
        if($key['state']==1){
            echo "百度指数：".$key['data'][0]['allindex']."，&nbsp;&nbsp;360指数：".$key['data'][0]['so360index']."&nbsp;&nbsp;(此处显示仅为参考，请以实际为准。)";
        }else{

            echo "未查询到指数信息";
        }
//        echo $key_json;
//        echo $str;
        //$this->success('添加成功！', url('user/fapiao/index'));
    }

    public function listbaidu()
    {
        $data = $this->request->param();
        $key=urlencode($data['post_title']);
        $pram=['key'=>$key,'ssyq'=>1];
        $result = hook("sousuoyinqing",$pram);
//        var_dump($result);
        echo $result[0];
    }
    public function listsogou()
    {
        $data = $this->request->param();
        $key=urlencode($data['post_title']);
        $pram=['key'=>$key,'ssyq'=>2];
        $result = hook("sousuoyinqing",$pram);
//        var_dump($result);
        echo $result[0];
    }
    public function listso()
    {
        $data = $this->request->param();
        $key=urlencode($data['post_title']);
        $pram=['key'=>$key,'ssyq'=>3];
        $result = hook("sousuoyinqing",$pram);
//        var_dump($result);
        echo $result[0];
    }

    public function paiming()
    {
        //todo:来源判断，网址，防止其他人恶意调用

        $data = $this->request->param();
        $title=urlencode($data['post_title']);
        $url=$data['post_url'];
        $ssyq_value=$data['ssyq'];
        $num=isset($data['num'])?$data['num']:"";
        switch ($ssyq_value){
            case 1:
                $ssyq="baidu";
                break;
            case 2:
                $ssyq="sogou";
                break;
            case 3:
                $ssyq="so360";
                break;
        }
        if($ssyq_value==1){
            $jieguo=file_get_contents("http://api.91cha.com/bdsort?key=3aaeff25baa645b49657cec54982b4c1&host=$url&wd=$title");
            $data_jieguo=json_decode($jieguo,true);
            $data=str_replace("名以外","",$data_jieguo['data']['sort']);
            echo $data;
            if($num!=''){
                echo ",".$num;
            }
            exit;
        }
        $key_json="";
        $i=0;
        $headers = array(
            'User-Agent: Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3',
            'Accept-Encoding: gzip, deflate'
        );
        while(!$key_json){
            if($i==3){
                echo "查询超时,".$num.",".$ssyq_value.",".$title.",".$url;
                exit;
            }

            $key_json=$this->curl_get_contents_get("http://if.aizhan.com:9010/AizhanSEO/keywordrank_request/%E5%B9%BF%E4%B8%9C/%E6%B7%B1%E5%9C%B3/".$ssyq."/".$title."/".$url."/10",$headers);
            $i++;
        }
        $key=json_decode($key_json);

        $paiming_json='';
        $i=0;
        while(!$paiming_json){
            if($i==3){
                echo "查询超时,".$num.",".$ssyq_value.",".$title.",".$url;
                exit;
            }
            $paiming_json=$this->curl_get_contents_get("http://if.aizhan.com:9010/AizhanSEO/keywordrank_response/".$key."/%E5%B9%BF%E4%B8%9C/%E6%B7%B1%E5%9C%B3/".$ssyq."/".$title."/".$url."/0",$headers);
            $i++;
        }
        $paiming=json_decode(json_decode($paiming_json),true);
$i=0;
        $headers = array(
            'User-Agent: Mozilla/5.0 (Windows NT 6.1;) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2398.0 Safari/537.36',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
            'Accept-Language: zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3',
            'Accept-Encoding: gzip, deflate'
        );
        while($paiming['IsCompleted']==false){
//            sleep(1);
//            $key_json=file_get_contents("http://if.aizhan.com:9010/AizhanSEO/keywordrank_request/%E5%B9%BF%E4%B8%9C/%E6%B7%B1%E5%9C%B3/".$ssyq."/".$title."/".$url."/10");
//            $key=json_decode($key_json);

            $paiming_json=$this->curl_get_contents_get("http://if.aizhan.com:9010/AizhanSEO/keywordrank_response/".$key."/%E5%B9%BF%E4%B8%9C/%E6%B7%B1%E5%9C%B3/".$ssyq."/".$title."/".$url."/0",$headers);
            $paiming=json_decode(json_decode($paiming_json),true);
//            echo $i;
            if($i==3){
                echo "未查询到,".$num.",".$ssyq_value.",".$title.",".$url;
                exit;
            }
            $i++;

        }
//        var_dump($paiming);
        $str=$paiming['ResponseList'][0]['Rank'];
        $str=str_replace("名","",str_replace("第","",$str));
        echo $str;
        if($num!=''){
            echo ",".$num;
        }

    }
    //5118
    public function paiming2()
    {
        //todo:来源判断，网址，防止其他人恶意调用
        $data = $this->request->param();
        $key=$data['post_url'];
        $pram=['key'=>$key,'ssyq'=>5];
        $result = hook("sousuoyinqing",$pram);
//        var_dump($result);exit;
        $jieguo=json_decode($result[0],true);
        array_shift($jieguo);
        echo json_encode($jieguo);
    }
    //5118 api 排名列表
    public function paiming3()
    {
        //todo:来源判断，网址，防止其他人恶意调用
        $data = $this->request->param();
        $url=$data['post_url'];
        //过滤http://
        $url=str_replace('http://','',$url);
        $tt=time();
        $userId               = cmf_get_current_user_id();
        $gc_query=Db::name('guanjianci_cache');
        $cache_f=$gc_query->field('id,s_data,s_time')->where('domain',$url)->cache(true)->find();
        if(!empty($cache_f)){
            if($tt-$cache_f['s_time']<604800) {
                $result = $cache_f['s_data'];
            }else{
                $header = array(
                    "Content-Type:application/x-www-form-urlencoded", //post请求
                    'Authorization: APIKEY A85D6AD070C54CA996A5101DA41BD47E'
                );
                $result = $this->curl_get_contents_post("http://apis.5118.com/keyword/baidupc","url=".$url,$header);
                //结果存入cmf_guanjianci_cache表
                $data=['s_data'=>$result,'s_time'=>$tt];
                $gc_query->where('id',$cache_f['id'])->update($data);
            }
        }else{
            $header = array(
                "Content-Type:application/x-www-form-urlencoded", //post请求
                'Authorization: APIKEY A85D6AD070C54CA996A5101DA41BD47E'
            );
            $result = $this->curl_get_contents_post("http://apis.5118.com/keyword/baidupc","url=".$url,$header);
            //结果存入cmf_guanjianci_cache表
            $data=['domain'=>$url,'s_data'=>$result,'s_time'=>$tt,'addtime'=>$tt];
            $gc_query->insert($data);
        }

        $jieguo=json_decode($result,true);
        //对结果进行禁词检测
        $jinci = Db::name('guanjianci_jinci_post')->field('post_title')->where(['delete_time'=>0])->cache(true)->select();
        //已添加检测
        $tianjia=Db::name('guanjianci_post')->field('post_title')->where(['delete_time'=>0,'user_id'=>$userId])->cache()->select();

        $data=array();
        foreach ($jieguo['data']['baidupc'] as $v) {
//            if (strlen($v['keyword']) > 1) {
                //内容禁词检测
                $v['jin']=0;
                if ($jinci) {
                    foreach ($jinci as $val) {
                        if (strpos($v['keyword'], $val['post_title']) !== false) {
//                            return "err:" . $val['post_title'];
                            $v['jin']=1;
                        }
                    }
                }
                $v['jia']=0;
                if($tianjia){
                    foreach ($tianjia as $val) {
                        if ($v['keyword']==$val['post_title']) {
                            $v['jia']=1;
                        }
                    }
                }
                $data[]=$v;
//            }
        }


        if($jieguo['errcode']==0){
//            echo json_encode($jieguo['data']['baidupc']);
            echo json_encode($data);
        }else{
            echo 0;
        }
        //var_dump($jieguo['data']['baidupc']);

    }
    //5118 api 单个关键词排名
    public function paiming4(){
        $data = $this->request->param();
        $url=$data['post_url'];
        $key=$data['post_title'];
        $type=$data['post_type'];
        //禁词检测
        $jinci = Db::name('guanjianci_jinci_post')->where(['delete_time'=>0])->cache()->select();
        foreach ($jinci as $val) {
            if (strpos($key, $val['post_title']) !== false) {
                echo 3;
                exit;
            }
        }
        //
        if($type==1){ //baidu
            $apikey="FB9AAB8384A4440698E31A4EAA9918C0";
            $posturl="http://apis.5118.com/morerank/baidupc";
        }
        if($type==2){
            $apikey="";
            $posturl="";
        }
        if($type==3){ //360
            $apikey="439D85D3CAA24A00821CF6C2417937E2";
            $posturl="http://apis.5118.com/morerank/haosou";
        }
        $header = array(
            "Content-Type:application/x-www-form-urlencoded", //post请求
            'Authorization: APIKEY '.$apikey
        );
        $result = $this->curl_get_contents_post($posturl,"url=".$url."&keywords=".$key."&checkrow=100",$header);
        $jieguo=json_decode($result,true);
        if($jieguo['errcode']==0) {
            $taskid = $jieguo['data']['taskid'];
            $i=1;
            do{
                $pm=$this->paiming4_1($posturl,$taskid,$header);
//                var_dump($pm);
                $i++;
                if($i>20){
                    $pm=1;
                }
            }while(!$pm);
            echo $pm;
        }else{
            echo 1;
        }
    }
    function paiming4_1($posturl,$taskid,$header){
        sleep(3);
        $result = $this->curl_get_contents_post($posturl, "taskid=" . $taskid, $header);
        $jieguo = json_decode($result, true);
        if ($jieguo['errcode'] == '0') {
            $d=$jieguo['data']['keywordmonitor'][0]['ranks'];
            if($d){
                return json_encode($d[0]);
            }else{
                return 1;
            }
//            return json_encode($jieguo['data']['keywordmonitor'][0]['ranks']);
//        }elseif($jieguo['errcode'] == '200104'){
//            echo "200104";
        }else{
            return false;
        }
    }
    //实时排名
    function paiming5(){
        $data = $this->request->param();
        $id=$data['id'];
        $num=$data['num'];
        $userGuanjianciQuery            = Db::name("guanjianci_post");
        $gjc=$userGuanjianciQuery->field('shishipaiming')->where('id',$id)->cache(true)->find();
        echo $gjc['shishipaiming'].','.$num;
    }
    //续费
    public function xufei(){
        $userId               = cmf_get_current_user_id();
        $data = $this->request->param();
        $renwu_id=$data['id'];
        $guanjianciQuery=Db::name('guanjianci_post');
        $result=$guanjianciQuery->where('id',$renwu_id)->find();
        $guanjianciQuery->where('id',$renwu_id)->update(['create_time'=>time()]);

        //生成任务列表
        $renwuQuery=Db::name('taskdjdata');
        $renwudata=[];
        switch ($result['post_type']){
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
        for($i=0;$i<$result['post_tianshu'];$i++){
            $rq=strtotime(date('Y-m-d' , strtotime('+'.$i.' day')));
            for($t=0;$t<24;$t++) {
                for ($j = 0; $j < $result['txt_time' . $t]; $j++) {
                    $xs = $t * 3600;
                    //随机百度cookie
                    $baidu_cookie = Db::name('baiducookie')->field('baidu_cookie')->where(['cookie_fail'=>['<',10]])->order('rand()')->limit(1)->find();

                    $renwudata[] = ['renwu_id' => $renwu_id, 'task_time' => $rq + $xs, 'sou' => base64_encode($sou), 'key' => base64_encode($result['post_title']), 'title' => base64_encode($result['post_biaoti']), 'baidu_cookie' => $baidu_cookie['baidu_cookie'], 'create_time' => $time];
                }
            }
        }
        $renwuQuery->insertAll($renwudata);

        //积分减少
        $jiageQuery=Db::name('user_jiage')->field('guanjianci')->where('id',1)->find();
        $jiage=$jiageQuery['guanjianci'];
        $userQuery            = Db::name("user");
        $where=[];
        $where['id']=$userId;
        $xiaofei=$result['post_dianjicishu']*$result['post_tianshu']*$jiage;
        $coin=$userQuery->where($where)->find();
        $userQuery->where($where)->update(array('score'=>$coin['score']-$xiaofei));

        //增加明细记录
        $userMoneyQuery            = Db::name("user_money_log");
        $data2=[];
        $data2['user_id']=$userId;
        $data2['create_time']=time();
        $data2['type']=2;
        $data2['post_title']='关键词【'.$result['post_title'].'】点击任务续费';
        $data2['score']=$xiaofei;
        $userMoneyQuery->insert($data2);
        $this->success('续费成功！', url('user/guanjianci/index'));
    }

    function curl_get_contents_get($url,$headers)
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);            //设置访问的url地址
        curl_setopt($ch,CURLOPT_HEADER,0);            //是否显示头部信息
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);           //设置超时
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);      //跟踪301
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        //返回结果
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   //HTTS请求
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
    function curl_get_contents_post($url,$curlPost='',$headers='')
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);            //设置访问的url地址
        curl_setopt($ch,CURLOPT_HEADER,0);            //是否显示头部信息
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);           //设置超时
        curl_setopt($ch,CURLOPT_FOLLOWLOCATION,1);      //跟踪301
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);        //返回结果
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);   //HTTS请求
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $r = curl_exec($ch);
        curl_close($ch);
        return $r;
    }
}