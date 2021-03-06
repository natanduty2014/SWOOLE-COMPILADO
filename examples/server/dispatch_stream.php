<?php
$serv = new Swoole\Server("127.0.0.1", 9501);

$serv->set(array(
    'dispatch_mode' => 7,
    'worker_num' => 2,
));

$serv->on('receive', function (Swoole\Server $serv, $fd, $threadId, $data)
{
    var_dump($data);
    echo "#{$serv->worker_id}>> received length=" . strlen($data) . "\n";
    $serv->send($fd, "Swoole $data\n");
});

$serv->start();
