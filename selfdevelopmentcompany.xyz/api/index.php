<?php
require_once '../RestAdapter.php';
require_once '../Database.php';
require_once '../LogUtil.php';
try {
    //initialize rest adapter
    $restAdapter = new RestrAdapter();
    //translate given text with given lang direction
    $restAdapter->getTranslation();
}catch (Exception $e){
    //if there is some exceptions log it and return error code
    LogUtil::sendErrorToServerLog($e->getMessage());
    http_response_code(406);
    echo "error";
}finally{
    Database::closeExistingConnection();
}