<?php
require_once dirname(__FILE__).'/AuthCode.class.php';
$authCode = new AuthCode();
if($authCode->check()){
    echo "ok";
}else{
    echo "error";
}

?>
