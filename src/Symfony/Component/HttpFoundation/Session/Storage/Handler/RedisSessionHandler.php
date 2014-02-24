<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Session\Storage\Handler;

/**
 * RedisSessionHandler
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 * @author Ryan Schumacher <ryan@38pages.com>
 */
class RedisSessionHandler implements \SessionHandlerInterface
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var integer
     */
    private $lifetime;

    /**
     * @var array
     */
    private $options;

    /**
     * Constructor
     * 
     * List of available options:
     *  * key_prefix: The key prefix [default: '']
     *
     * @param \Redis $redis The redis instance
     * @param integer $lifetime Max lifetime in seconds to keep sessions stored.
     * @param array $options Options for the session handler
     * 
     * @throws \InvalidArgumentException When Redis instance not provided
     */
    public function __construct($redis, $lifetime, array $options = array())
    {
        if (!$redis instanceof \Redis) {
            throw new \InvalidArgumentException('Redis instance required');
        }

        $this->redis = $redis;
        $this->lifetime = $lifetime;

        if(!is_array($options)) $options = array();
        $this->options = array_merge(array(
            'key_prefix' => ''
        ), $options);
    }

    /**
     * {@inheritDoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function read($sessionId)
    {   
        $key = $this->getKey($sessionId);
        return (string) $this->redis->get($key);
    }

    /**
     * {@inheritDoc}
     */
    public function write($sessionId, $data)
    {
        $key = $this->getKey($sessionId);
        return $this->redis->setex($key, $this->lifetime, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function destroy($sessionId)
    {
        $key = $this->getKey($sessionId);
        return 1 === $this->redis->delete($key);
    }

    /**
     * {@inheritDoc}
     */
    public function gc($lifetime)
    {
        /* Note: Redis will handle the expiration of keys with SETEX command
         * See: http://redis.io/commands/setex
         */
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * Get the redis key
     * 
     * @param string $sessionId session id
     */
    protected function getKey($sessionId)
    {
        if(is_string($this->options['key_prefix'])) {
            return $this->options['key_prefix'].$sessionId;
        }
        return $sessionId;
    }
}