<?php
/**
 * Created by PhpStorm.
 * User: shiyang
 * Date: 16/5/19
 * Time: 16:49
 */

namespace My;


class StringUtils
{
    public static $target_img_size = 1048575;
    static public $pic_types = array("image/gif", "image/jpeg", "image/pjpeg", "image/jpg", "image/png", "image/bmp");

    public static $imageAllowFiles = [".png", ".jpg", ".jpeg", ".gif", ".bmp"]; /* 上传图片格式显示 */
    public static $videoAllowFiles = [
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid"]; /* 上传视频格式显示 */
    public static $fileAllowFiles = [
        ".png", ".jpg", ".jpeg", ".gif", ".bmp",
        ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
        ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
        ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
        ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
    ]; /* 上传文件格式显示 */

    /**
     * 获取UUID
     * @return string
     */
    public static function uuid()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
            return $uuid;
        }
    }

    /**
     * 字符串str中是否包含子字符串needle
     *
     * @param $str
     * @param $needle
     * @param bool $isCaseSensitive 是否区分大小写
     * @return bool
     */
    public static function contain($str, $needle, $isCaseSensitive = false)
    {
        //检查$str是否包含$needle字符串
        if($isCaseSensitive) {
            return !(strpos($str, $needle) === FALSE);//注意这里的"==="
        } else {
            return !(stripos($str, $needle) === FALSE);//注意这里的"==="
        }

    }

    /**
     * 字符串str中是否包含单词needle(或者单词数组),
     * 和contain方法有单词非单词的区别
     * 如果匹配到了第一个,就停止继续匹配,如需要继续匹配则使用:preg_match_all
     *
     * @param $str
     * @param $needle string or array
     * @param bool $isCaseSensitive 是否区分大小写
     * @return mixed
     */
    public static function matchWord($str, $needle, $isCaseSensitive = false)
    {
        if(! is_array($needle)) {
            $needle = array($needle);
        }
        $words = $needle;
        //拼装正则表达式
        $words = array_map(function($v){
            $v = '\\b' . $v . '\\b';
            return $v;
        }, $words);
        $wordsStr = join('|', $words);
        $iStr = $isCaseSensitive ? '' : 'i';
        //$pattern = "/(\\bcancel\\b|\\bupdate address\\b|\\burgent\\b|\\bemergency\\b|\\bupset\\b|\\bangry\\b)/i";
        $pattern = "/$wordsStr/$iStr";
        preg_match($pattern, $str, $matches);
        return $matches;
    }

    public static function startWith($str, $needle)
    {
        return strpos($str, $needle) === 0;
    }

    public static function endWith($str, $needle)
    {
        return substr($str, -strlen($needle)) === $needle;
    }

    /**
     * 字符串截取
     *
     * @param $str
     * @param $length
     * @return string
     */
    public static function subStr($str, $length)
    {
        if ($length > 0 && mb_strlen($str) > $length) {
            $str = mb_substr($str, 0, $length) . '...';
        }
        return $str;
    }

    /**
     * 获取交易单号
     *
     * @return string
     */
    public static function getTradeNo()
    {
        return date('YmdHis') . self::generateRandomString(6);
    }

    /**
     * 验证是否是国内手机号码
     * @param $mobile
     * @return bool
     */
    public static function isMobile($mobile)
    {
        return !preg_match("/1[3456789]{1}\d{9}$/", $mobile) ? false : true;
    }

    /**
     * 验证邮箱格式
     *
     * @param $mail
     * @return mixed
     */
    public static function isMail($mail)
    {
        return filter_var($mail, FILTER_VALIDATE_EMAIL);
    }

    /**
     * 生成N位纯数字验证码
     *
     * @param int $length
     * @param string $chars
     * @return string
     */
    public static function rand( $length= 4, $chars = '0123456789')
    {
        $hash = '';
        $max = strlen($chars) - 1;
        for ($i = 0; $i < $length; $i++) {
            $hash .= $chars[mt_rand(0, $max)];
        }
        return $hash;
    }

    /**
     * 从数字、大小写字母生成随机字符串,不包含数字1、0和字母：i,l,o,I,O，以免造成误会
     * @param int $length 指定长度
     * @return string
     */
    public static function generateRandomString($length)
    {
        $pattern = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $strLen = strlen($pattern);
        $return_str = '';
        for ($i = 0; $i < $length; $i++) {
            $return_str .= $pattern{mt_rand(0, ($strLen - 1))}; //生成php随机数
        }
        return $return_str;
    }

    /**
     * 文件类型检测
     *
     * @param $fileName
     * @param array $allowFiles
     * @return bool
     */
    public static function checkType($fileName,Array $allowFiles)
    {
        return in_array(self::getFileExt($fileName), $allowFiles);
    }

    /**
     * 获取文件扩展名
     *
     * @param $fileName
     * @return string
     */
    public static function getFileExt($fileName)
    {
        return strtolower(strrchr($fileName, '.'));
    }

    public static function parseQuotationMark($keyword)
    {
        if (empty($keyword)) return '';
        $keyword = trim($keyword);
        $keyword = str_replace("'", "\\'", $keyword);
        $keyword = str_replace('"', '\\"', $keyword);
        $keyword = str_replace("\n", "", $keyword);//去除回车、换行
        $keyword = str_replace("\r", "", $keyword);//去除回车、换行
        return $keyword;
    }

    public static function parseParameter($keyword)
    {

        $parsed_keyword = preg_replace('[:cntrl:]', '', $keyword);

        $parsed_keyword = str_replace("\"", " ", $parsed_keyword);
        $parsed_keyword = str_replace("/", " ", $parsed_keyword);
        $parsed_keyword = str_replace("\\", " ", $parsed_keyword);
        $parsed_keyword = str_replace(":", " ", $parsed_keyword);
        $parsed_keyword = str_replace("?", " ", $parsed_keyword);
        $parsed_keyword = str_replace("'", " ", $parsed_keyword);
        $parsed_keyword = str_replace("[", " ", $parsed_keyword);
        $parsed_keyword = str_replace("]", " ", $parsed_keyword);
        $parsed_keyword = str_replace("{", " ", $parsed_keyword);
        $parsed_keyword = str_replace("}", " ", $parsed_keyword);
        $parsed_keyword = str_replace(")", " ", $parsed_keyword);
        $parsed_keyword = str_replace("(", " ", $parsed_keyword);
        $parsed_keyword = str_replace("~", " ", $parsed_keyword);
        $parsed_keyword = str_replace("\n", "", $parsed_keyword);//去除回车、换行
        $parsed_keyword = str_replace("\r", "", $parsed_keyword);//去除回车、换行
        $parsed_keyword = trim($parsed_keyword);
        return $parsed_keyword;
    }

    public static function cleanHtml($str)
    {
        $str = preg_replace('/<\\s*\/?br.*?>|<\\s*\/?BR.*?>/', '', $str);
        $str = preg_replace('/<.*?>/', '', $str);
        $str = preg_replace('/\s+/', ' ', $str);
        $str = preg_replace('/&nbsp;/', '', $str);
        $str = preg_replace('[:cntrl:]', '', $str);
        return $str;
    }

    /**
     * 将长日期转化为短日期时间格式
     * @param $longTime
     * @return bool|null
     */
    public static function getShortTimeFromLongTime($longTime)
    {
        if (!isset($longTime)) {
            return null;
        }
        return self::getShortTime(strtotime($longTime));
    }

    /**
     * 将时间戳转化为短日期时间格式
     * @param $unixtimeStamp
     * @return bool|null
     */
    public static function getShortTime($unixtimeStamp)
    {
        if (empty($unixtimeStamp)) {
            return null;
        }
        return date('Y-m-d', $unixtimeStamp);
    }

    /**
     * 将时间戳转化为标准时间格式
     * @param $unixtimeStamp
     * @return bool|null
     */
    public static function getLongTime($unixtimeStamp)
    {
        if (empty($unixtimeStamp)) {
            return null;
        }
        return date('Y-m-d H:i:s', $unixtimeStamp);
    }

    /**
     * 将时间戳转化为标准时间格式
     * @param $unixtimeStamp
     * @return bool|null
     */
    public static function convertUnixTimeStamp($unixtimeStamp)
    {
        if (empty($unixtimeStamp)) {
            return null;
        }
        return date('Y-m-d H:i:s', $unixtimeStamp);
    }

    /**
     * 将时间格式字符串转化为时间戳
     * @param $time
     * @return int|null
     */
    public static function timeToUnixTimeStamp($time)
    {
        if (empty($time)) {
            return null;
        }
        return strtotime($time);
    }

    /**
     * 验证两个字符串是否相同
     * @param string $str1
     * @param string $str2
     * @return bool
     */
    public static function validateAll($str1, $str2)
    {
        if ($str1 != $str2) return false;
        return true;
    }

    /**
     * 格式化时间
     *
     * @param string $time
     * @return bool|string
     */
    public static function formatDate($time = 'default')
    {
        $date = $time == 'default' ? date('Y-m-d H:i:s', time()) : date('Y-m-d H:i:s', $time);
        return $date;
    }

    /**
     * 获取当前时间
     * @return bool|string
     */
    public static function getCurrentDateTime()
    {
        return date('Y-m-d H:i:s', time());
    }

    /**
     * 检查短日期格式是否正确
     * @param $date string 短日期字符串
     * @return bool|string
     */
    public static function checkShortDate($date)
    {
        //$date 这里可以任意格式
        $is_date = strtotime($date) ? strtotime($date) : false;
        //只要提交的是合法的日期，这里都统一成2014-11-11格式
        return date('Y-m-d', $is_date);
    }

    /**
     * 检查长日期格式是否正确
     * @param $date string 长日期字符串
     * @return bool|string
     */
    public static function checkLongDate($date)
    {
        //$date 这里可以任意格式
        $is_date = strtotime($date) ? strtotime($date) : false;
        //只要提交的是合法的日期，这里都统一成2014-11-11 12:12:12格式
        return date('Y-m-d H:i:s', $is_date);
    }

    /**
     * 验证图片类型
     * @param string $type 图片类型
     * @return bool
     */
    public static function checkImgType($type)
    {
        if (in_array($type, self::$pic_types)) {
            return true;
        }
        return false;
    }// 1048575=1*1024*1024 上传img大小上限1MB 单位：MB

    /**
     * 验证图片大小
     *
     * @param int $size 待验证文件的大小
     * @param int $max_size 指定文件最大限制,单位：k
     * @return bool
     */
    public static function checkImgSize($size,$max_size=0)
    {
        if(empty($max_size)) {
            $max_size=self::$target_img_size;
        } else {
            $max_size=$max_size*1024;
        }
        return self::checkFileSize($size, $max_size);
    }

    /**
     * 验证文件大小
     * @param  int $size 待验证文件的大小
     * @param  int $target 指定文件大小
     * @return bool
     */
    public static function checkFileSize($size, $target = 1048575)
    {
        if ($size <= $target) {
            return true;
        }
        return false;
    }

    public static function checkUrl($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * 添加url参数到网址后面
     * @param $url
     * @param $string
     * @return string
     */
    public static function addParameterToUrl($url, $string)
    {
        //如果$url中已经包含了$string,则直接返回
        if (self::contain($url, $string)) {
            return $url;
        }
        if (self::contain($url, "?")) {
            $url = $url . '&' . $string;
        } else {
            $url = $url . '?' . $string;
        }
        return $url;
    }

    /**
     * 获取客户端IP
     * @return string
     */
    public static function getClientIP()
    {
        $ip = '';
        if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]) && strcasecmp($_SERVER["HTTP_X_FORWARDED_FOR"], "unknown")) {
            $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $pos = array_search('unknown', $arr);
            if (false !== $pos) unset($arr[$pos]);
            $ip = trim($arr[0]);
        }
        if (isset($_SERVER["HTTP_CLIENT_IP"]) && strcasecmp($_SERVER["HTTP_CLIENT_IP"], "unknown")) {
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (isset($_SERVER["REMOTE_ADDR"])) {
            $ip = $_SERVER["REMOTE_ADDR"];
        }
        return $ip;
    }

    /**
     * 给url中添加参数，如有重发的key，会覆盖value
     *
     * @param $url
     * @param $key
     * @param $val
     * @return string
     */
    public static function addUrlParam($url, $key, $val)
    {
        $tmpArr  = explode("?",$url);
        $hostStr  = $tmpArr[0];
        $queryParams = [];
        if(isset($tmpArr[1])) {
            parse_str($tmpArr[1], $queryParams);
        }
        $queryParams[$key] = $val;
        return $hostStr . '?' . http_build_query($queryParams);
    }

    /**
     * 获取GMT标准时间 如:2015-03-14T14:38:08.531Z 在kibana上用得着
     * @return string
     */
    public static function getGmdate()
    {
        $time = explode(" ", microtime());
        $time2 = explode(".", $time [0] * 1000);
        $ms = $time2 [0];//得到毫秒3位数
        return gmdate("Y-m-d\TH:i:s.$ms\Z", strtotime("+ 8 hours"));
    }

    /**
     * 获取带毫秒的时间 如:2015-03-14 14:38:08.531
     * @return string
     */
    public static function getMillisecond()
    {
        $time = explode(" ", microtime());
        $time2 = explode(".", $time [0] * 1000);
        $ms = $time2 [0];//得到毫秒3位数
        $time_str = self::getLongTime($time[1]) . "." . $ms;
        return $time_str;
    }

    /**
     * 获取前几个月的日期
     * @param int $month
     * @return bool|string
     */
    public static function getLastMonthDateTime($month = 3)
    {
        $month = intval($month);
        return date('Y-m-d H:i:s',strtotime('-'.$month.' month'));
    }

    /**
     * 获取时间差值
     *
     * @param int $begin_time 开始时间戳
     * @param int $end_time 结束时间戳
     * @return array
     */
    public static function timeDiff($begin_time,$end_time)
    {
        if($begin_time < $end_time){
            $starttime = $begin_time;
            $endtime = $end_time;
        }
        else{
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        $timediff = $endtime-$starttime;
        $days = intval($timediff/86400);
        $remain = $timediff%86400;
        $hours = intval($remain/3600);
        $remain = $remain%3600;
        $mins = intval($remain/60);
        $secs = $remain%60;
        $res = array("day" => $days,"hour" => $hours,"min" => $mins,"sec" => $secs);
        return $res;
    }

    /**
     * 个性化显示时间
     * @param $date
     * @param string $format
     * @return false|string
     */
    public static function humanTime($date, $format = 'Y-m-d H:i:s')
    {
        if ($date) {
            if (is_numeric($date)) {
                if (mb_strlen(strval($date)) > 10) {
                    $date = $date / 1000;//date去除毫秒精度
                }
                $timestamp = $date;//时间戳
            } else {
                $timestamp = strtotime($date);
            }

            $diff = time() - $timestamp;
            if ($diff < 600)
                return '刚刚';
            elseif ($diff < 3600)
                return floor($diff / 60) . '分钟前';
            elseif ($diff < 86400)
                return floor($diff / 3600) . '小时前';
            elseif ($diff < 864000)
                return floor($diff / 86400) . '天前';
            else
                return date($format, $timestamp);
        } else {
            return '';
        }
    }

    public static function arrayToObject($e)
    {
        if (gettype($e) != 'array') return null;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'array' || getType($v) == 'object')
                $e[$k] = (object)self::arrayToObject($v);
        }
        return (object)$e;
    }

    public static function objectToArray($e)
    {
        $e = (array)$e;
        foreach ($e as $k => $v) {
            if (gettype($v) == 'resource') return null;
            if (gettype($v) == 'object' || gettype($v) == 'array')
                $e[$k] = (array)self::objectToArray($v);
        }
        return $e;
    }

    /**
     * 获取纯文本内容,只适用于英文等字母为基础的语种,不适合中文等
     * 去掉所有不是字母或者数字的其他字符，用空格替代
     *
     * @param $str
     * @return mixed
     */
    public static function getPlainText($str)
    {
        //去掉逗号，双引号和换行
//        $content = str_replace('"', '', $content);
//        $content = str_replace("'", '', $content);
//        $content = str_replace(',', ' ', $content);
//        $content = str_replace(PHP_EOL, '', $content);

        if(empty($str)) {
            return $str;
        }
        $str = self::replace4byte($str);
        //去掉所有不是字母或者数字的其他字符，用空格替代
        $pattern = '/\W+/';
        $str = preg_replace($pattern,' ', $str);
        return $str;
    }

    //去掉表情符号
    public static function replace4byte($str)
    {
        if(empty($str)) return $str;
        return preg_replace('%(?:
          \xF0[\x90-\xBF][\x80-\xBF]{2}      # planes 1-3
        | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
        | \xF4[\x80-\x8F][\x80-\xBF]{2}      # plane 16
    )%xs', '', $str);
    }

    /**
     * 对金额数值保留两位小数(包含：四舍五入)
     *
     * @param $money
     * @return string
     */
    public static function formatMoney($money)
    {
        return sprintf("%.2f", $money);
    }

    /**
     * 身份证验证
     *
     * @param $id
     * @return bool
     */
    public static function isIdcard( $id )
    {
        $id = strtoupper($id);
        $regx = "/(^\d{15}$)|(^\d{17}([0-9]|X)$)/";
        $arr_split = array();
        if(!preg_match($regx, $id))
        {
            return FALSE;
        }
        if(15==strlen($id)) //检查15位
        {
            $regx = "/^(\d{6})+(\d{2})+(\d{2})+(\d{2})+(\d{3})$/";

            @preg_match($regx, $id, $arr_split);
            //检查生日日期是否正确
            $dtm_birth = "19".$arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
            if(!strtotime($dtm_birth))
            {
                return FALSE;
            } else {
                return TRUE;
            }
        }
        else           //检查18位
        {
            $regx = "/^(\d{6})+(\d{4})+(\d{2})+(\d{2})+(\d{3})([0-9]|X)$/";
            @preg_match($regx, $id, $arr_split);
            $dtm_birth = $arr_split[2] . '/' . $arr_split[3]. '/' .$arr_split[4];
            if(!strtotime($dtm_birth))  //检查生日日期是否正确
            {
                return FALSE;
            }
            else
            {
                //检验18位身份证的校验码是否正确。
                //校验位按照ISO 7064:1983.MOD 11-2的规定生成，X可以认为是数字10。
                $arr_int = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
                $arr_ch = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
                $sign = 0;
                for ( $i = 0; $i < 17; $i++ )
                {
                    $b = (int) $id{$i};
                    $w = $arr_int[$i];
                    $sign += $b * $w;
                }
                $n  = $sign % 11;
                $val_num = $arr_ch[$n];
                if ($val_num != substr($id,17, 1))
                {
                    return FALSE;
                }
                else
                {
                    return TRUE;
                }
            }
        }
    }
    
    /**
     * 匹配是否包含中文
     *
     * @param $content
     * @return bool
     */
    public static function matchChText($content)
    {
        $reg='/[\x{4e00}-\x{9fa5}]/u';//匹配是否包含中文的正则表达式
        if (preg_match($reg,$content)) {
            return true;
        }
        return false;
    }

    //
    /**
     * 深度合并多维数组 array_merge增强
     *
     * @param $arr1
     * @param $arr2
     * @return array
     */
    public static function array_merge_deep($arr1, $arr2){
        $merged	= $arr1;

        foreach($arr2 as $key => &$value){
            if(is_array($value) && isset($merged[$key]) && is_array($merged[$key])){
                $merged[$key]	= self::array_merge_deep($merged[$key], $value);
            }elseif(is_numeric($key)){
                if(!in_array($value, $merged)) {
                    $merged[]	= $value;
                }
            }else{
                $merged[$key]	= $value;
            }
        }

        return $merged;
    }

    /**
     * 校验是否为合法的http url
     *
     * @param $str
     * @return bool
     */
    public static function isUrl($str)
    {
        $reg='/^(http(s)?:\/\/)(([0-9a-z_!~*\'().&=+$%-]+: )?[0-9a-z_!~*\'().&=+$%-]+@)?(([0-9]{1,3}.){3}[0-9]{1,3}|([0-9a-z_!~*\'()-]+.)*([0-9a-z][0-9a-z-]{0,61})?[0-9a-z].[a-z]{2,6})(:[0-9]{1,4})?((\/?)|(\/[0-9a-z_!~*\'().;?:@&=+$,%#-]+)+\/?)$/i';
        if (preg_match($reg,$str)) {
            return true;
        }
        return false;
    }

}
