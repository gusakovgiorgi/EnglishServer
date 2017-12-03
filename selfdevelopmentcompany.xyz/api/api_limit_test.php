<?php
require_once '../RestAdapter.php';
$restadapter=new RestrAdapter();
for ($i=0;$i<11000;$i++){
    $restadapter->getTranslation();
}