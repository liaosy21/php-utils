<?php
/**
 * Created by PhpStorm.
 * User: shiyang
 * Date: 17/6/19
 * Time: 22:21
 */

namespace Test;

require_once "My/StringUtils.php";

use My\StringUtils;

class Test
{
    function timeTran($timeInt, $format = 'Y-m-d H:i:s')
    {
        return StringUtils::timeTran($timeInt, $format);
    }
}

$obj = new Test();
$timeStr = "2017-05-05T10:48:49.178269219Z";
$timeFormate = str_replace('T', ' ', explode('.', $timeStr)[0]);
$timeInt = strtotime($timeFormate);
$result = $obj->timeTran($timeInt);
echo 'result:', $result, PHP_EOL;