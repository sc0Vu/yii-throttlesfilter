<?php

/**
 * ThrottleFilter
 *
 * @author Peter Lai <alk03073135@gmail.com>
 * @since 20160825
 */
class ThrottlesFilter extends CFilter
{
    /**
     * requests throttle limit
     * 
     * @var integer
     */
    public $limit = 0;

    /**
     * limit seconds
     * 
     * @var integer
     */
    public $limitSeconds = 60;

    /**
     * lock seconds
     * @var integer
     */
    public $lockSeconds = 60;
    
    /**
     * Cache
     * 
     * @var CCache
     */
    protected $cache;

    /**
     * client ip
     * 
     * @var string
     */
    protected $ip;
    
    /**
     * init
     * 
     * @return void
     */
    public function init()
    {
        parent::init();
        $this->cache = Yii::app()->cache;
        $this->ip = Yii::app()->request->userHostAddress;
    }

    /**
     * preFilter
     * 
     * @param  CFilterChain $filterChain
     * @return boolean
     */
    protected function preFilter($filterChain)
    {
        if ($this->limit === 0) {
            return true;
        }
        if ($this->limitSeconds === 0) {
            return false;
        }

        $cache = $this->cache;
        $ip = $this->ip;
        $now = time();
        $diff = 0;

        if (($lockTime = $cache->get($ip.'|lock')) !== false) {
            if (($now - $lockTime) <= $this->lockSeconds) {
                return false;
            }
            $cache->delete($ip.'|lock');
        }
        if ($cache->get($ip.'|count') === false) {
            $cache->set($ip.'|count', 1);
        }
        if (($time = $cache->get($ip.'|time')) !== false) {
            $diff = $now - $time;
            if ($diff <= $this->limitSeconds) {
                if ($cache->get($ip.'|count') > $this->limit) {
                    $cache->set($ip.'|lock', $now);
                    $cache->delete($ip.'|count');
                    $cache->delete($ip.'|time');
                    return false;
                }
            } else {
                $cache->set($ip.'|count', 0);
                $cache->set($ip.'|time', $now);
            }
        } else {
            $this->cache->set($ip.'|time', $now);
        }
        return true;
    }

    /**
     * postFilter
     * 
     * @param  CFilterChain $filterChain
     * @return void
     */
    protected function postFilter($filterChain)
    {
        $cache = $this->cache;
        $ip = $this->ip;
        $count = $cache->get($ip.'|count');
        $cache->set($ip.'|count', $count += 1);
    }
}