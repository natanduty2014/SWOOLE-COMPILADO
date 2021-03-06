<?php
class ProxyServer
{
    protected $clients;
    protected $backends;
    protected $serv;

    function run()
    {
        $serv = new Swoole\Server("127.0.0.1", 9509);
        $serv->set(array(
            'worker_num' => 32, //reactor thread num
            'backlog' => 128, //listen backlog
            'max_conn' => 10000,
            'dispatch_mode' => 2,
            //'open_tcp_keepalive' => 1,
            //'log_file' => '/tmp/swoole.log', //swoole error log
        ));
        $serv->on('WorkerStart', array($this, 'onStart'));
        $serv->on('Connect', array($this, 'onConnect'));
        $serv->on('Receive', array($this, 'onReceive'));
        $serv->on('Close', array($this, 'onClose'));
        $serv->on('WorkerStop', array($this, 'onShutdown'));
        $serv->start();
    }

    function onStart($serv)
    {
        $this->serv = $serv;
        echo "Server: start.Swoole version is [" . SWOOLE_VERSION . "]\n";
    }

    function onShutdown($serv)
    {
        echo "Server: onShutdown\n";
    }

    function onClose($serv, $fd, $reactor_id)
    {

    }

    function onConnect($serv, $fd, $reactor_id)
    {

    }

    function onReceive($serv, $fd, $reactor_id, $data)
    {
		$socket = new Swoole\Client(SWOOLE_SOCK_TCP);
        if($socket->connect('127.0.0.1', 80, 0.5))
        {
			$socket->send($data);
			$serv->send($fd, $socket->recv(8192, 0));
		}
        unset($socket);
        $serv->close($fd);
    }
}

$serv = new ProxyServer();
$serv->run();
