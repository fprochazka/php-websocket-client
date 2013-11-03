<?php

namespace WebSocketClient\TestsHelpers;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\Http\Router;
use Ratchet\Wamp\WampServer;
use Ratchet\Wamp\WampServerInterface;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Wamp\Topic;
use React\EventLoop\StreamSelectLoop;
use React\Socket\Server as Reactor;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Closure;

class Server implements WampServerInterface
{
    /** @var RouteCollection $routes */
    private $routes;

    /** @var int $port */
    private $port;

    /** @var string $path */
    private $path;

    /** @var Reactor $socket */
    private $socket;

    /** @var IoServer $server */
    private $server;

    /** @var callable $onSubscribeCallback */
    private $onSubscribeCallback;

    /** @var callable $onUnSubscribeCallback */
    private $onUnSubscribeCallback;

    /**
     * Init websocket server
     *
     * @param StreamSelectLoop $loop
     * @param int $port
     * @param string $path
     */
    function __construct(StreamSelectLoop $loop, $port, $path)
    {
        $httpHost = 'localhost';

        $this->setPort($port)
            ->setPath($path)
            ->setSocket(new Reactor($loop))
            ->setRoutes(new RouteCollection);

        $this->getSocket()->listen($this->getPort());

        $this->getRoutes()
            ->add(
                'rr-1',
                new Route(
                    $this->getPath(),
                    array('_controller' => new WsServer(new WampServer($this))),
                    array('Origin' => $httpHost),
                    array(),
                    $httpHost
                )
            );

        $this->setServer(
            new IoServer(
                new HttpServer(
                    new Router(
                        new UrlMatcher($this->getRoutes(), new RequestContext)
                    )
                ),
                $this->getSocket(),
                $loop
            )
        );
    }

    /**
     * Release websocket server
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Release websocket server
     */
    public function close()
    {
        $socket = $this->getSocket();
        if (null !== $socket) {
            $this->getSocket()->shutdown();
            $this->setSocket(null);
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @param Topic|string $topic
     */
    public function onSubscribe(ConnectionInterface $conn, $topic)
    {
        $callback = $this->getOnSubscribeCallback();
        if (null !== $callback) {
            $callback($conn, $topic);
        }
    }

    /**
     * @param ConnectionInterface $conn
     * @param Topic|string $topic
     */
    public function onUnSubscribe(ConnectionInterface $conn, $topic)
    {
        $callback = $this->getOnUnSubscribeCallback();
        if (null !== $callback) {
            $callback($conn, $topic);
        }
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onOpen(ConnectionInterface $conn)
    {
        echo '';
    }

    /**
     * @param ConnectionInterface $conn
     */
    public function onClose(ConnectionInterface $conn)
    {
        echo '';
    }

    /**
     * @param ConnectionInterface $conn
     * @param string $id
     * @param Topic|string $topic
     * @param array $params
     */
    public function onCall(ConnectionInterface $conn, $id, $topic, array $params)
    {
        echo '';
    }

    /**
     * @param ConnectionInterface $conn
     * @param Topic|string $topic
     * @param string $event
     * @param array $exclude
     * @param array $eligible
     */
    public function onPublish(ConnectionInterface $conn, $topic, $event, array $exclude, array $eligible)
    {
        echo '';
    }

    /**
     * @param ConnectionInterface $conn
     * @param Exception $e
     */
    public function onError(ConnectionInterface $conn, Exception $e)
    {
        echo '';
    }

    /**
     * @param callable $onSubscribeCallback
     * @return self
     */
    public function setOnSubscribeCallback(Closure $onSubscribeCallback)
    {
        $this->onSubscribeCallback = $onSubscribeCallback;
        return $this;
    }

    /**
     * @return callable
     */
    public function getOnSubscribeCallback()
    {
        return $this->onSubscribeCallback;
    }

    /**
     * @param callable $onUnSubscribeCallback
     * @return self
     */
    public function setOnUnSubscribeCallback(Closure $onUnSubscribeCallback)
    {
        $this->onUnSubscribeCallback = $onUnSubscribeCallback;
        return $this;
    }

    /**
     * @return callable
     */
    public function getOnUnSubscribeCallback()
    {
        return $this->onUnSubscribeCallback;
    }

    /**
     * @param int $port
     * @return self
     */
    public function setPort($port)
    {
        $this->port = (int)$port;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param Reactor|null $socket
     * @return self
     */
    public function setSocket(Reactor $socket = null)
    {
        $this->socket = $socket;
        return $this;
    }

    /**
     * @return Reactor
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @param RouteCollection $routes
     * @return self
     */
    public function setRoutes(RouteCollection $routes)
    {
        $this->routes = $routes;
        return $this;
    }

    /**
     * @return RouteCollection
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * @param IoServer $server
     * @return self
     */
    public function setServer(IoServer $server)
    {
        $this->server = $server;
        return $this;
    }

    /**
     * @return IoServer
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param string $path
     * @return self
     */
    public function setPath($path)
    {
        $this->path = (string)$path;
        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
}