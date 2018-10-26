<?php

namespace Qqes\Parallel;

use swoole_process;

/**
 * 利用多进程并行处理逻辑抽象
 *
 * @author wang
 */
class Parallel {

    /**
     * 自带cache目录名称
     */
    const SELF_CACHE_DIR = 'cache';

    
 

    //put your code here


    public function __construct() {
    }

    /**
     * 多线程并行运行
     * @param array $actors 任务
     * @param \callable $callback function($key, $actor) 分布处理函数
     * @param boolean $return_result 是否返回处理结果
     */
    public function run(array $actors, callable $callback, $return_result = false) {
        $mark = $this->genMark();
        foreach ($actors as $key => $actor) {
            $process = new swoole_process(function(swoole_process $worker) use($key, $actor, $callback,  $mark, $return_result) {
                $result = call_user_func($callback, $key, $actor);
                $return_result && Parallel::saveEachResult($mark, $key, $result);
            });
            $process->start();
        }
        swoole_process::wait();
        if ($return_result) {
            return $this->getResult($mark, array_keys($actors));
        }
    }

    /**
     * 获取记录
     * @param type $mark
     * @param array $keys
     * @return type
     */
    public function getResult($mark, array $keys) {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = self::getAndDelEachResult($mark, $key);
        }
        return $result;
    }

    /**
     * 储存每个独立处理逻辑的结果
     * @param type $mark
     * @param type $key
     * @param type $result
     */
    public static function saveEachResult($mark, $key, $result) {
        file_put_contents(Parallel::getFile($mark, $key), serialize($result));
    }

    /**
     * 获取并删除处理的结果
     * @param type $mark
     * @param type $key
     * @return type
     */
    public static function getAndDelEachResult($mark, $key) {
        $file = Parallel::getFile($mark, $key);
        $result = file_get_contents($file);
        unlink($file);
        return unserialize($result);
    }

    /**
     * 得到此次运行的标记 用pid 和当前时间标记
     * @return strinig
     */
    public function genMark() {
        return md5('p' . posix_getpid() . 'm' . microtime(true));
    }

    /**
     * 生成没个并行结果放入的文件路径
     * @param type $mark
     * @param type $key
     * @return type
     */
    public static function getFile($mark, $key) {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . self::SELF_CACHE_DIR . DIRECTORY_SEPARATOR . md5($mark . 'key' . $key);
    }

}
