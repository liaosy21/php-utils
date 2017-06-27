<?php
/**
 * Created by PhpStorm.
 * User: shiyang
 * Date: 17/6/19
 * Time: 22:21
 */

namespace Test;

use My\StringUtils;

class Test
{

    function timeTran($timeInt, $format = 'Y-m-d H:i:s')
    {
        return StringUtils::timeTran($timeInt, $format);
    }
}


spl_autoload_register(function ($class) {
    if ($class) {
        $file = str_replace('\\', '/', $class);
        $file .= '.php';
        if (file_exists($file)) {
            include $file;
        }
    }
});

$obj = new Test();
$timeStr = "2017-05-05T10:48:49.178269219Z";
$timeFormate = str_replace('T', ' ', explode('.', $timeStr)[0]);
$timeInt = strtotime($timeFormate);
$result = $obj->timeTran($timeInt);
echo 'result:', $result, PHP_EOL;

$created = "2017-06-21T03:07:07Z";
$created1 = "2017-06-21 03:07:07";
echo strtotime($created),PHP_EOL;
echo strtotime($created1),PHP_EOL;

$date = new \DateTime($created);
$dateStr = $date->format('Y-m-d H:i:s');
echo $dateStr,PHP_EOL;
