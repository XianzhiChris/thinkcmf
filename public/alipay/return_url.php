<?php

require_once("config.php");
require_once 'pagepay/service/AlipayTradeService.php';

$arr=$_GET;
$alipaySevice = new AlipayTradeService($config); 
$result = $alipaySevice->check($arr);

if($result) {//验证成功

    echo '<script>window.close();</script>';

}
else {

    echo '<script>window.close();</script>';
}
?>