<?php
$serv = new Swoole\Server("::1", 9502, SWOOLE_PROCESS, SWOOLE_SOCK_UDP6);
$serv->set(array(
    'worker_num' => 1,
));
$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {
    echo "[#".posix_getpid()."]\tClient[$fd]: $data\n";
    var_dump($serv->connection_info($fd, $reactor_id));
	$serv->send($fd, json_encode(array("hello" => '1213', "bat" => "ab")));
    //$serv->close($fd);
});
$serv->start();
