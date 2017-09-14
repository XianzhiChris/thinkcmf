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
        $editData = new UserModel();
        $data = $editData->guanjianci();
        //查询任务执行情况
        $list=[];
        $taskQuery=Db::name('taskdjdata');
        foreach($data['lists'] as $v){
            $task_ok=$taskQuery->field(array('count(*)'=>'count'))->where(['renwu_id'=>$v['id'],'return_ip'=>['neq','']])->find();
            $v['task_ok_num']=$task_ok['count'];
            $list[]=$v;
        }
        $user = cmf_get_current_user();
        $userQuery=Db::name('user');
        $coin=$userQuery->field('score')->where(array('id'=>$user['id']))->find();
        $this->assign('myscore',$coin['score']);
        $this->assign($user);
        $this->assign("page", $data['page']);
        $this->assign("lists", $list);
        $jiageQuery=Db::name('user_jiage')->field('guanjianci')->where('id',1)->find();
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
        $userQuery=Db::name('user');
        $coin=$userQuery->field('score')->where(array('id'=>$user['id']))->find();
        $this->assign('myscore',$coin['score']);
        $this->assign('url',$data['url']);
        $this->assign('guanjianci',$data['guanjianci']);
        return $this->fetch();
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
}