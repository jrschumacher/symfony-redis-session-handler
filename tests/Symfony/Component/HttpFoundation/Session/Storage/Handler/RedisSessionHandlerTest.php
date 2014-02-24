<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\HttpFoundation\Tests\Session\Storage\Handler;

use Symfony\Component\HttpFoundation\Session\Storage\Handler\RedisSessionHandler;

/**
 * @author Ryan Schumacher <ryan@38pages.com>
 */
class RedisSessionHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $redis;
    private $storage;
    public $options;

    protected function setUp()
    {
        if (!extension_loaded('redis')) {
            $this->markTestSkipped('RedisSessionHandler requires the PHP "redis" extension.');
        }

        $this->redis = $this->getMockBuilder('redis')
            ->disableOriginalConstructor()
            ->getMock();

        $this->lifetime = 1;

        $this->options = array(
            'key_prefix' => 'foo:'
        );

        $this->storage = new RedisSessionHandler($this->redis, $this->lifetime, $this->options);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testConstructorShouldThrowExceptionForInvalidRedis()
    {
        new RedisSessionHandler(new \stdClass(), $this->lifetime);
    }

    public function testOpenMethodAlwaysReturnTrue()
    {
        $this->assertTrue($this->storage->open('test', 'test'), 'The "open" method should always return true');
    }

    public function testCloseMethodAlwaysReturnTrue()
    {
        $this->assertTrue($this->storage->close(), 'The "close" method should always return true');
    }

    public function testGcMethodAlwaysReturnTrue()
    {
        $this->assertTrue($this->storage->gc(1), 'The "gc" method should always return true');
    }

    public function testReadWithKeyPrefix()
    {
        $that = $this;

        $this->redis->expects($this->once())
            ->method('get')
            ->will($this->returnCallback(function ($key) use ($that) {
                $that->assertEquals('foo:bar', $key);

                return 'foo-bar';
            }));

        $this->assertEquals('foo-bar', $this->storage->read('bar'));
    }

    public function testWriteWithKeyPrefix()
    {
        $that = $this;

        $this->redis->expects($this->once())
            ->method('setex')
            ->will($this->returnCallback(function ($key, $data) use ($that) {
                $that->assertEquals('foo:bar', $key);

                return true;
            }));

        $this->assertTrue($this->storage->write('bar', 1));
    }

    public function testDestroyWithKeyPrefix()
    {
        $that = $this;

        $this->redis->expects($this->once())
            ->method('delete')
            ->will($this->returnCallback(function ($key) use ($that) {
                $that->assertEquals('foo:bar', $key);

                return 1;
            }));

        $this->assertTrue($this->storage->destroy('bar'));
    }

    public function testGetKey()
    {
        $method = new \ReflectionMethod($this->storage, 'getKey');
        $method->setAccessible(true);

        $this->assertEquals($this->options['key_prefix'] . 'bar', $method->invoke($this->storage, 'bar'));
    }
}