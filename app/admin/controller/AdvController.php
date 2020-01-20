<?php


namespace app\admin\controller;


use app\admin\model\AdvModel;
use app\admin\model\AdvPositionModel;
use cmf\controller\AdminBaseController;

class AdvController extends AdminBaseController
{
    public function adv_list()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $page = isset($data['page']) && $data['page'] ? $data['page'] : 1;
            $num = 10;
            $list = AdvModel::with('position')->paginate($num, false, ['page' => $page]);
            $adv_address = AdvPositionModel::all();
            $this->success('', null, ['list' => $list->items(), 'count' => $list->total(), 'adv_address' => $adv_address]);
        }
        return $this->fetch();
    }

    public function position()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            $page = isset($data['page']) && $data['page'] ? $data['page'] : 1;
            $num = 10;
            $list = AdvPositionModel::withCount('adv_list')->paginate($num, false, ['page' => $page]);
            $this->success('', null, ['list' => $list->items(), 'count' => $list->total()]);
        }
        return $this->fetch();
    }

    public function category()
    {
        return $this->fetch();
    }

    public function set_adv()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            if ($data['id'] && isset($data['id'])) {
                $position = AdvPositionModel::get($data['id']);
            } else {
                $position = new  AdvPositionModel();
            }
            $position->position_name = $data['title'];
            $position->num = $data['num'];
            $position->status = isset($data['status']) && $data['status'] ? $data['status'] : 0;
            $res = $position->save();
            if ($res) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }

        }
    }

    public function set_advlist()
    {
        if ($this->request->isAjax()) {
            $data = $this->request->param();
            if (isset($data['id']) && $data['id']) {
                $advlist = Advlist::get($data['id']);
            } else {
                $advlist = new Advlist();
                $advlist->add_time = time();
            }
            $advlist->sort = $data['sort'];
            $advlist->adv_id = $data['adv_id'];
            $advlist->title = $data['title'];
            $advlist->img = $data['img'];
            $advlist->status = $data['status'];
            $advlist->type = $data['type'];
            $advlist->organ_id = $data['organ_id'];
            $advlist->curriculum_id = $data['curriculum_id'];
            $advlist->adv_url = $data['adv_url'];
            $res = $advlist->save();
            if ($res) {
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }

        }

    }

    public function delete_advlist()
    {
        $data = $this->request->param();
        $res = Advlist::destroy($data['id']);
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}
