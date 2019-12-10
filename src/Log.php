<?php
/**
 * @author: justwkj
 * @date: 2019/12/10 10:17
 * @email: justwkj@gmail.com
 * @desc:
 */

namespace Justwkj\Log;


class Log {

    private $fileHandle;

    /** 初始化日志类
     * Log constructor.
     * @param string $path 日志路径
     * @param string $fileName 文件名
     * @throws \Exception
     */
    public function __construct($path = './', $fileName = 'log.log') {
        if (!is_dir($path)) {
            throw new \Exception(sprintf("path:%s not exists", $path));
        }

        $path = rtrim($path, '/');
        $fileName = ltrim($fileName, '/');
        if (strstr($fileName, '/')) {
            throw new \Exception(sprintf("fileName:%s is invalid", $fileName));
        }
        $filePath = $path . '/' . $fileName;

        $this->fileHandle = fopen($filePath, "a") or function ($filePath) {
            throw new \Exception("unable to open file: " . $filePath);
        };
    }

    /**
     *  日志前缀
     * @param $level
     * @return string
     * @author: justwkj
     * @date: 2019/12/10 11:14
     */
    private function logPrefix($level) {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $traceInfo = array_filter($trace, function ($item) use ($level) {
            if ($item['function'] == $level && $item['class'] == __CLASS__) {
                return $item;
            }
        });
        $traceInfo = array_values($traceInfo);
        $traceInfo = $traceInfo[0];

        return vsprintf("[%s][%s][%s:%d]", [
            date('Y-m-d H:i:s'),
            $level,
            basename($traceInfo['file']),
            $traceInfo['line'],
        ]);
    }

    /**
     *  统一记录
     * @param $data
     * @param $levelName
     * @author: justwkj
     * @date: 2019/12/10 11:14
     */
    private function logData($data, $levelName){
        if (is_array($data)) {
            $data = json_encode($data);
        } else if (is_bool($data)) {
            $data = "bool:" . ($data ? 'true' : 'false');
        } else if(is_object($data)){
            $data = "obj:".get_class($data);

        } else if(is_resource($data)){
            $data = "resource type";
        } else {
            $data = strval($data);
        }
        $data = $this->logPrefix($levelName) . $data;
        $data = strval($data);
        fwrite($this->fileHandle, $data . PHP_EOL);
    }

    /**
     *  debug log
     * @param $data
     * @author: justwkj
     * @date: 2019/12/10 11:14
     */
    public function debug($data) {
        $this->logData($data,__FUNCTION__);
    }

    /**
     * info log
     * @param $data
     * @author: justwkj
     * @date: 2019/12/10 11:14
     */
    public function info($data) {
        $this->logData($data,__FUNCTION__);
    }

    /**
     *  error log
     * @param $data
     * @author: justwkj
     * @date: 2019/12/10 11:13
     */
    public function error($data) {
        $this->logData($data,__FUNCTION__);
    }

    public function __destruct() {
        fclose($this->fileHandle);
    }
}