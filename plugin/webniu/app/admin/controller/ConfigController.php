<?php

namespace plugin\webniu\app\admin\controller;

use plugin\webniu\app\common\Util;
use plugin\webniu\app\model\Option;
use plugin\webniu\app\model\Confset;
use support\exception\BusinessException;
use support\Request;
use support\Response;
use Throwable;

/**
 * 系统设置
 */
class ConfigController extends Base
{

    /**
     * 无需登录及鉴权的方法
     * @var string[]
     */
    protected $noNeedLogin = ['index'];

    /**
     * @var Model
     */
    protected $model = null;

    /**
     * 获取配置
     * @return Response
     */
    public function index(): Response
    {
        try {
            $data = options(['systemInfo', 'systemSetting', 'systemTheme']);
            if (isEmpty2DArray($data)) {
                $data = $this->getByDefault();
            }
            $data['systemPath'] = [
                'menuList' => '/admin/rule/get',
            ];
            $data['systemSetting']['isInstall'] = is_install();
        } catch (Throwable $e) {
            $data = $this->getByDefault();
        }
        return $this->json(200, '读取成功', $data);
    }

    /**
     * 获取配置
     * @return Response
     */
    public function getconfig(Request $request): Response
    {
        $form = [];
        $model = $request->input('model', 'system');
        $name = $request->input('name', '');
        $this->model = new Confset;
        $confset_list = $this->model->where([
            ['model', '=', $model],
            ['name', '=', $name],
            ['status', '=', 1],
        ])->orderBy('sort', 'asc')->get()->toArray();
        if ($confset_list) {
            foreach ($confset_list as $item) {
                $db = [
                    'label' => $item['label'],
                    'labelWidth' => $item['label_width'] != null ? $item['label_width'] . 'px' : '110px',
                    'key' => $item['key'],
                    'type' => $item['type'],
                    'span' => $item['span'] != null ? $item['span'] : 24,
                    'hidden' => $item['hidden'] == 1 ? true : false,
                    'props' => json_decode($item['props'], true)
                ];
                if ($item['type'] == 'treeselect') {
                    $db['props']['data'] = json_decode($item['options'], true);
                }else{
                    $db['props']['options'] = json_decode($item['options'], true);
                }
                $db['props']['dict'] = $item['dict'];
                $form[] = $db;
            }
        }
        $config = Option::where([
            ['name', '=', $name],
            ['model', '=', $model],
        ])->first();
        if ($config) {
            $data = json_decode($config->value, true);
            $username = $config->username;
            $updated_at = Util::humanDate($config->updated_at);
        }
        return $this->json(200, '读取成功', [
            'form' => $form,
            'data' => $data ?? [],
            'username' => $username ?? '-',
            'updated_at' => $updated_at ?? '-',
        ]);
    }

    /**
     * 修改配置
     * @return Response
     */
    public function setconfig(Request $request): Response
    {
        $admin = admin();
        $model = $request->input('model', 'system');
        $name = $request->input('label', false);
        $value = $request->input('value', false);
        if (empty($name) || empty($value)) {
            throw new BusinessException('参数错误');
        }
        $option = Option::where([
            ['name', '=', $name],
            ['model', '=', $model],
        ])->first();
        if ($option) {
            $option->value = json_encode($value);
            $option->updated_at = date('Y-m-d H:i:s');
            $option->save();
        } else {
            $option = new Option();
            $option->name = $name;
            $option->model = $model;
            $option->value = json_encode($value);
            $option->username = $admin['username'];
            $option->save();
        }
        return $this->json(200, '保存成功');
    }
    /**
     * 基于配置文件获取默认权限
     * @return mixed
     */
    protected function getByDefault()
    {
        $result = [];
        $config = file_get_contents(base_path('plugin/webniu/public/config/pear.config.json'));
        if ($config) {
            $result = json_decode($config, true);
            try {
                $this->model = new Option;
                foreach ($result as $key => $item) {
                    $this->model->updateOrInsert([
                        'model' => 'system',
                        'name' => $key,
                    ], [
                        'value' => json_encode($item),
                        'username' => 'system',
                        'updated_at' => date('Y-m-d H:i:s'),
                        'created_at' => date('Y-m-d H:i:s'),
                    ]);
                }
            } catch (Throwable $e) {
                return $result;
            }
        }
        return $result;
    }
    /**
     * 颜色检查
     * @param string $color
     * @return string
     * @throws BusinessException
     */
    protected function filterColor(string $color): string
    {
        if (!preg_match('/\#[a-zA-Z]6/', $color)) {
            throw new BusinessException('参数错误');
        }
        return $color;
    }
}
