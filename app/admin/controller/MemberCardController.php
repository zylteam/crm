<?php


namespace app\admin\controller;


use cmf\controller\AdminBaseController;

class MemberCardController extends AdminBaseController
{
    public function index()
    {
        return $this->fetch();
    }
}
