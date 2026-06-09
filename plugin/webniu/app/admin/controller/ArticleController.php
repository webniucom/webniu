<?php

namespace plugin\webniu\app\admin\controller;

use plugin\webniu\app\model\Article;
use plugin\webniu\app\model\ArticleGroup;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 文章管理 
 */
class ArticleController extends Crud
{

    /**
     * @var Article
     */
    protected $model = null;

    /**
     * 构造函数
     * @return void
     */
    public function __construct()
    {
        $this->model = new Article;
    }

    /**
     * 加载布局
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function index(Request $request): Response
    {
        $groups = ArticleGroup::orderBy('id', 'desc')->orderBy('sort', 'desc')->get()->toArray();
        $form = [
            [
                'label' => '分类',
                'labelWidth' => '100px',
                'key' => 'group',
                'type' => 'treeselect',
                'span' => 24,
                'hidden' => false,
                'search' => true,
                'showlist' => false,
                'props' => [
                    'placeholder' => '请选择分类',
                    'clearable' => false,
                    'tooltip' => true,
                    'data' => $groups,
                    'checkStrictly' => true,
                    'props' => [
                        'label' => 'label',
                        'value' => 'id',
                    ]
                ],
                'rules' => [
                    [
                        'required' => true,
                        'message' => '请输入公告标题',
                        'trigger' => 'blur',
                    ]
                ],
            ],
            [
                'label' => '标题',
                'labelWidth' => '100px',
                'key' => 'title',
                'type' => 'input',
                'span' => 24,
                'hidden' => false,
                'search' => true,
                'showlist' => true,
                'props' => [
                    'placeholder' => '请输入标题',
                    'clearable' => true,
                    'tooltip' => true,
                    'options' => [],
                    'dict' => '',
                ],
                'rules' => [
                    [
                        'required' => true,
                        'message' => '请输入公告标题',
                        'trigger' => 'blur',
                    ]
                ],
            ],
            [
                'label' => '来源',
                'labelWidth' => '100px',
                'key' => 'source',
                'type' => 'input',
                'span' => 24,
                'hidden' => false,
                'search' => true,
                'showlist' => false,
                'props' => [
                    'placeholder' => '请输入来源',
                    'clearable' => true,
                    'tooltip' => true
                ],
            ],
            [
                'label' => '作者',
                'labelWidth' => '100px',
                'key' => 'author',
                'type' => 'input',
                'span' => 24,
                'hidden' => false,
                'showlist' => true,
                'props' => [
                    'placeholder' => '请输入作者',
                    'clearable' => true,
                    'tooltip' => true,
                    'defaultValue' => 'admin',
                ],
            ],
            [
                'label' => '排序',
                'labelWidth' => '100px',
                'key' => 'sort',
                'type' => 'number',
                'span' => 24,
                'hidden' => false,
                'showlist' => false,
                'props' => [
                    'placeholder' => '请输入排序',
                    'clearable' => true,
                    'tooltip' => true,
                    "min" => 0,
                    'defaultValue' => 0,
                ],
            ],
            [
                'label' => '状态',
                'labelWidth' => '100px',
                'key' => 'status',
                'type' => 'switch',
                'span' => 24,
                'hidden' => false,
                'props' => [
                    'placeholder' => '请输入状态',
                    'clearable' => true,
                    'tooltip' => true,
                    'defaultValue' => 1,
                    'activeValue' => 1,
                    'inactiveValue' => 0,
                ],
            ],
            [
                'label' => '时间',
                'labelWidth' => '100px',
                'key' => 'updated_at',
                'type' => 'input',
                'span' => 24,
                'hidden' => true,
                'search' => false,
                'showlist' => true,
                'props' => [
                    'placeholder' => '请输入时间',
                    'clearable' => true,
                    'tooltip' => true
                ],
            ],
            [
                'label' => '内容',
                'labelWidth' => '100px',
                'key' => 'content',
                'type' => 'wangeditor',
                'span' => 24,
                'hidden' => false,
                'props' => [
                    'placeholder' => '请输入内容',
                    'clearable' => true,
                    'tooltip' => false,
                    'height' => '310px',
                    'toolbarKeys' => [
                        'code',
                        'headerSelect',
                        'bold',
                        'italic',
                        'underline',
                        '|',
                        'bulletedList',
                        'numberedList',
                        '|',
                        'insertLink',
                        'insertImage',
                        '|',
                        'undo',
                        'redo'
                    ]
                ],
                'rules' => [
                    [
                        'required' => true,
                        'message' => '请输入内容',
                        'trigger' => 'blur',
                    ]
                ]
            ]
        ];
        return $this->json(200, '加载成功', [
            'form' => $form,
            'grouplist' => $groups,
            'config' => [
                'group' => true,
                'title' => '公告',
                'groupname' => '分类',
                'layout' => 'refresh,size,fullscreen,columns,settings',
            ],
        ]);
    }

    /**
     * 查询
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function select(Request $request): Response
    {
        [$where, $format, $limit, $field, $order] = $this->selectInput($request);
        if (!empty($where['title']) && is_string($where['title'])) {
            $where['title'] = ['like', "%{$where['title']}%"];
        }
        $query = $this->doSelect($where, $field, $order);
        return $this->doFormat($query, $format, $limit);
    }

    /**
     * 插入
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function insert(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::insert($request);
        }
        return $this->json(400, '请求方法错误');
    }

    /**
     * 更新
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function update(Request $request): Response
    {
        if ($request->method() === 'POST') {
            return parent::update($request);
        }
        return $this->json(400, '请求方法错误');
    }

    /**
     * 添加分组
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function groupinsert(Request $request): Response
    {
        $this->model = new ArticleGroup;
        if ($request->method() === 'POST') {
            $data = $this->insertInput($request);
            $id = $this->doInsert($data);
            return $this->json(200, '添加成功', ['id' => $id]);
        }
        return $this->json(400, '请求方法错误');
    }

    /**
     * 更新分组
     * @param Request $request
     * @return Response
     * @throws BusinessException|Throwable
     */
    public function groupupdate(Request $request): Response
    {
        $this->model = new ArticleGroup;
        if ($request->method() === 'POST') {
            [$id, $data] = $this->updateInput($request);
            $this->doUpdate($id, $data);
            return $this->json(200, '更新成功');
        }
        return $this->json(400, '请求方法错误');
    }

    /**
     * 删除分组
     * @param Request $request
     * @return Response
     * @throws BusinessException
     */
    public function groupdelete(Request $request): Response
    {
        $this->model = new ArticleGroup;
        $ids = $this->deleteInput($request);
        $this->doDelete($ids);
        return $this->json(200, '删除成功');
    }
}
