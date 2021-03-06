<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2017 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 老猫 <thinkcmf@126.com>
// +----------------------------------------------------------------------
namespace app\pinglun\service;

use app\pinglun\model\PinglunPostModel;
use app\pinglun\model\PinglunJinciPostModel;
use app\pinglun\model\PinglunTwPostModel;
use app\pinglun\model\ZhidaotaskdataModel;

class PostService
{

    public function adminArticleList($filter)
    {
        return $this->adminPostList($filter);
    }
    public function adminJinciList($filter)
    {
        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0
        ];


        $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
        if (!empty($keyword)) {
            $where['a.post_title'] = ['like', "%$keyword%"];
        }

        $portalPostModel = new PinglunJinciPostModel();
        $articles        = $portalPostModel->alias('a')
            ->where($where)
            ->order('id', 'DESC')
            ->paginate(10);

        return $articles;
    }
    public function adminTiwenList($filter)
    {
        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0,
            'a.post_type' => 2
        ];
        $join = [
            ['__USER__ u', 'a.user_id = u.id'],['__ZHIDAOTASKDATA__ z','z.pinglun_id = a.id']
        ];
        $field = 'a.*,u.user_login,u.user_nickname,u.mobile,z.return_code,z.return_url,z.return_img';

        $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
        if (!empty($keyword)) {
            $where['a.post_title'] = ['like', "%$keyword%"];
        }

        $portalPostModel = new PinglunPostModel();
        $articles        = $portalPostModel->alias('a')->field($field)
            ->join($join)
            ->where($where)
            ->order('id', 'DESC')
            ->paginate(10);

        return $articles;
    }
    public function adminRizhiList($filter)
    {
        if(!empty($filter['pinglun_id'])){
            $where['pinglun_id']=$filter['pinglun_id'];
        }
        $where['create_time']=['>=', 0];
        $where['delete_time']=['=', 0];

        $portalPostModel = new ZhidaotaskdataModel();
        $articles        = $portalPostModel->where($where)
            ->order('id', 'DESC')
            ->paginate(10);

        return $articles;
    }
    public function adminPageList($filter)
    {
        return $this->adminPostList($filter, true);
    }

    public function adminPostList($filter, $isPage = false)
    {

        $where = [
            'a.create_time' => ['>=', 0],
            'a.delete_time' => 0,
            'a.post_type' => 1
        ];
        $join = [
            ['__USER__ u', 'a.user_id = u.id']
        ];
        $field = 'a.*,u.user_login,u.user_nickname,u.mobile';

        $startTime = empty($filter['start_time']) ? 0 : strtotime($filter['start_time']);
        $endTime   = empty($filter['end_time']) ? 0 : strtotime($filter['end_time']);
        if (!empty($startTime) && !empty($endTime)) {
            $where['a.create_time'] = [['>= time', $startTime], ['<= time', $endTime]];
        } else {
            if (!empty($startTime)) {
                $where['a.create_time'] = ['>= time', $startTime];
            }
            if (!empty($endTime)) {
                $where['a.create_time'] = ['<= time', $endTime];
            }
        }

        $keyword = empty($filter['keyword']) ? '' : $filter['keyword'];
        if (!empty($keyword)) {
            $where['a.post_title'] = ['like', "%$keyword%"];
        }

        $portalPostModel = new PinglunPostModel();
        $articles        = $portalPostModel->alias('a')->field($field)
            ->join($join)
            ->where($where)
            ->order('id', 'DESC')
            ->paginate(10);

        return $articles;

    }

    public function publishedArticle($postId, $categoryId = 0)
    {
        $portalPostModel = new PinglunPostModel();

        if (empty($categoryId)) {

            $where = [
                'post.post_type'      => 1,
                'post.published_time' => [['< time', time()], ['> time', 0]],
                'post.post_status'    => 1,
                'post.delete_time'    => 0,
                'post.id'             => $postId
            ];

            $article = $portalPostModel->alias('post')->field('post.*')
                ->where($where)
                ->find();
        } else {
            $where = [
                'post.post_type'       => 1,
                'post.published_time'  => [['< time', time()], ['> time', 0]],
                'post.post_status'     => 1,
                'post.delete_time'     => 0,
                'relation.category_id' => $categoryId,
                'relation.post_id'     => $postId
            ];

            $join    = [
                ['__PORTAL_CATEGORY_POST__ relation', 'post.id = relation.post_id']
            ];
            $article = $portalPostModel->alias('post')->field('post.*')
                ->join($join)
                ->where($where)
                ->find();
        }


        return $article;
    }

    public function publishedPage($pageId)
    {

        $where = [
            'post_type'      => 2,
            'published_time' => [['< time', time()], ['> time', 0]],
            'post_status'    => 1,
            'delete_time'    => 0,
            'id'             => $pageId
        ];

        $portalPostModel = new PinglunPostModel();
        $page            = $portalPostModel
            ->where($where)
            ->find();

        return $page;
    }

}