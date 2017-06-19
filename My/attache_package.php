<?php
/**
 *
 * @authors Shiyang
 * @date    2016-07-01 16:15:26
 */
//require_once __DIR__ .'/_init.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';
use My\StringUtils;

class AttachePackage
{
    private $count = 0;
    private $isRealDelete = false;
    public $dateLimit = '';
    function __construct()
    {
        //记录总数
        $this->count = 0;
        //默认移除过去一年的附件
        $this->dateLimit = date('Ym',strtotime('-12 month'));
    }

    function main()
    {
        //记录任务执行开始时间
        $startTimeUnix = time();
        $start_time = date('Y-m-d H:i:s',$startTimeUnix);
        echo 'start_time:'.$start_time . "\n";
//        $this->dateLimit = '201606';


        //原始文件路径
        $path = '/data/osticket_attachments/osticket';
//        $path = '/Users/shiyang/tmp/php_test';

        //需要将原始文件移动到该路径,方便后续zip打包
        $moveToBaseDir = '/data/osticket_attachments/osticket_bak';
        //开始移动文件到打包区
        self::moveFiles($path, $moveToBaseDir, strlen("$path/"));
        //按年月打包输出的根路径
//        $outZipPath = '/tmp/Downloadsbak/';
//        $sourcePath = $moveToBaseDir;
//        self::zipByMouth($sourcePath, $outZipPath);

        //记录任务执行结束时间
        $endTimeUnix = time();
        $end_time = date('Y-m-d H:i:s', $endTimeUnix);
        $diff_time = StringUtils::timeDiff($startTimeUnix, $endTimeUnix);
        echo 'count:'.$this->count . "\n";
        echo 'end time:'.$end_time . "\n";
        echo 'diff time:'.json_encode($diff_time) . "\n";
    }

    /**
     * 移动原始目录下的所有文件到指定目录,
     * 并以文件的写入时间作为基准判断,按年月自动建立子目录分开存储
     *
     * @param string $sourcePath 原始目录
     * @param string $moveTo 移动文件到该目录
     * @param int $exclusiveLength 排除指定位数路径截取,方便子目录操作
     */
    function moveFiles($sourcePath, $moveTo, $exclusiveLength)
    {
        //如果目录不存在,则创建该目录
        self::mkDir($moveTo);
        $files = new FilesystemIterator($sourcePath,FilesystemIterator::SKIP_DOTS);
        foreach($files as $file) {
            $fileName = $file->getFilename();
            $filePath = $file->getPathname();
            $localPath = substr($filePath, $exclusiveLength);
            $localPath = str_replace($fileName, '', $localPath);
            if($file->isDir()) {
                $subPath = rtrim($sourcePath, '/') . '/' . $fileName;
                $this->moveFiles($subPath, $moveTo, $exclusiveLength);
            } else {
                //获取文件 Modified time:在写入文件时随文件内容的更改而更改，是指文件内容最后一次被修改的时间
                $cTime = $file->getMTime();
                $newFolder = date('Ym',$cTime);
//                echo 'modify date:'.$newFolder.', file name:'. $fileName . "\n";
                if($newFolder < $this->dateLimit) {
                    $moveToTargetDir = rtrim($moveTo, '/') . '/' .$newFolder . '/' . $localPath;
                    //如果目录不存在,则创建该目录
                    self::mkDir($moveToTargetDir);
                    $moveToDirTargetFile = rtrim($moveToTargetDir, '/') . '/' . $fileName;
                    //移动文件到指定目录
                    rename($filePath, $moveToDirTargetFile);
//                copy($filePath, $moveToDirTargetFile);
                    $this->count++;//统计file总数
                }
            }

        }
    }

    function zipByMouth($sourcePath, $outZipBasePath)
    {
        //如果目录不存在,则创建该目录
        self::mkDir($outZipBasePath);
        $files = new FilesystemIterator($sourcePath,FilesystemIterator::SKIP_DOTS);
        foreach($files as $file) {
            //前提是目录才进行打zip包,目录命名格式:yyyymm(如:201607)
            if($file->isDir()) {
                $fileName = $file->getFilename();
                $filePath = $file->getPathname();
                //拼接输出zip包名
                $outZipPath = rtrim($outZipBasePath, '/') . '/' . $fileName  . '.zip';
                HZip::zipDir($filePath, $outZipPath);
                //压缩完成后删除原文件,腾出磁盘空间.
                $success = self::delDir($filePath, $this->isRealDelete);
                if($success) {
                    echo 'delete files success! path:' . $filePath . ', time:' . StringUtils::getCurrentDateTime() . "\n";
                } else {
                    echo 'delete files failure! path:' . $filePath . ', time:' . StringUtils::getCurrentDateTime() . "\n";
                }
            }
        }
    }

    function checkFileMTime($sourcePath)
    {
        $files = new FilesystemIterator($sourcePath,FilesystemIterator::SKIP_DOTS);
        foreach($files as $file) {
            //前提是目录才进行打zip包,目录命名格式:yyyymm(如:201607)
            if($file->isDir()) {
                self::checkFileMTime($file->getPathname());
            } else {
                //获取文件 Modified time:在写入文件时随文件内容的更改而更改，是指文件内容最后一次被修改的时间
                $aTime = $file->getATime();
                $mTime = $file->getMTime();
                $cTime = $file->getCTime();
                echo date('Y-m-d H:i:s', $aTime). ' , '. $file->getFilename() . "\n";
                echo date('Y-m-d H:i:s', $mTime). ' , '. $file->getFilename() . "\n";
                echo date('Y-m-d H:i:s', $cTime). ' , '. $file->getFilename() . "\n";
            }
        }
    }

    /**
     * 如果目录不存在,则创建该目录
     *
     * @param $path
     */
    function mkDir($path)
    {
        //如果目录不存在,则创建该目录
        if(! file_exists($path)) {
            mkdir($path, 0777, true);
        }
    }

    /**
     * 遍历删除文件夹以及文件夹下的所有文件
     *
     * @param $path
     * @param bool $isRealDelete
     * @return bool
     */
    function delDir($path, $isRealDelete = true)
    {
        if(! $isRealDelete) {
            return false;
        }
        //遍历删除子目录
        $files = new FilesystemIterator($path,FilesystemIterator::SKIP_DOTS);
        foreach($files as $file) {
            $filePath = $file->getPathname();
            if($file->isDir()) {
                self::delDir($filePath, $isRealDelete);
            } else {
                unlink($filePath);
            }
        }
        //删除当前文件夹：
        if(rmdir($path)) {
            return true;
        } else {
            return false;
        }
    }




    /**
     * 删除文件
     *
     * @param $filePath
     */
    function delFile($filePath)
    {
        $isDel = unlink($filePath);
        if($isDel) {
            echo 'isDel:'.$isDel . ',file path:'. $filePath . "\n";
        }
    }

    /**
     * 删除文件夹
     *
     * @param $dir
     */
    function delFileDir($dir)
    {
        $isRmDir = rmdir($dir);
        if($isRmDir) {
            echo 'isRmdir:'.$isRmDir . ',dir path:'. $dir . "\n";
        }
    }
}

$obj = new AttachePackage();
$obj->main();

class HZip
{

    /**
     * Add files and sub-directories in a folder to zip file.
     * @param string $folder
     * @param ZipArchive $zipFile
     * @param int $exclusiveLength Number of text to be exclusived from the file path.
     */
    private static function folderToZip($folder, ZipArchive &$zipFIle, $exclusiveLength)
    {
        //遍历当前目录所有文件和子目录
        $files = new FilesystemIterator($folder,FilesystemIterator::SKIP_DOTS);
        foreach($files as $file) {
            $filePath = $file->getPathname();//当前文件路径
            $localPath = substr($filePath, $exclusiveLength);
            if($file->isDir()) {
                //is dir
                $zipFIle->addEmptyDir($localPath);//新建子目录
                self::folderToZip($filePath, $zipFIle, $exclusiveLength);
            } else {
                // is file
                $zipFIle->addFile($filePath, $localPath);
            }

        }

    }

    /**
     * Zip a folder (include itself).
     * Usage:
     *   HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip');
     *
     * @param string $sourcePath Path of directory to be zip.
     * @param string $outZipPath Path of output zip file.
     */
    public static function zipDir($sourcePath, $outZipPath)
    {
        $pathInfo = pathInfo($sourcePath);
        $parentName = $pathInfo['dirname'];//父级目录名称
        $dirName = $pathInfo['basename'];//当前目录的名称

        $z = new ZipArchive();
        $z->open($outZipPath, ZipArchive::CREATE);
        $z->addEmptyDir($dirName);
        self::folderToZip($sourcePath, $z, strlen("$parentName/"));
        $z->close();
    }

}
