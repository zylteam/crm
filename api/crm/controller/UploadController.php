<?php


namespace api\crm\controller;
header('Access-Control-Allow-Origin:*');

use cmf\controller\RestBaseController;
use cmf\lib\Upload;

class UploadController extends RestBaseController
{
    public function upload()
    {
        if ($this->request->isPost()) {
            $uploader = new Upload();
            $res = $uploader->upload();
            if ($res === false) {
                $result = [
                    'code' => 0,
                    'message' => $uploader->getError()
                ];
            } else {
                $result = [
                    'code' => 1,
                    'url' => str_replace('\\', '/', '/upload/' . $res["filepath"])
                ];
            }
            return json($result);
        }
    }
}
