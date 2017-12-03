<?php
require_once '../RestAdapter.php';
require_once '../Database.php';
require_once '../LogUtil.php';
try {
    $restAdapter = new RestrAdapter();
    $restAdapter->getTranslation();
}catch (Exception $e){
    LogUtil::sendErrorToServerLog($e->getMessage());
    http_response_code(406);
    echo "error";
}finally{
    Database::closeExistingConnection();
}