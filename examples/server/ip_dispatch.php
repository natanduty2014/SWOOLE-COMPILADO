<?php
$serv = new Swoole\Server("0.0.0.0", 9501);
$serv->fdlist = [];
$serv->workerid = 0;
$serv->set(array(
		//'tcp_defer_accept' => 5,
		//'ipc_mode' => 2,
		'worker_num' => 4,
		//'task_worker_num' => 2,
		'dispatch_mode' => 4,   //ip dispatch
		//'max_request' => 1000,
		//'daemonize' => true,
		//'log_file' => '/tmp/swoole.log'
));

$serv->on('workerStart', function($serv, $worker_id) {
	echo "{$worker_id} start".PHP_EOL;
	$serv->workerid = $worker_id;
});

$serv->on('connect', function ($serv, $fd, $reactor_id){
	//echo "[#".posix_getpid()."]\tClient@[$fd:$reactor_id]: Connect.\n";
	echo "{$fd} connect, worker:".$serv->workerid.PHP_EOL;
	$conn = print_r($serv->connection_info($fd));
	$serv->fdlist[$fd] = 1;
	print_r($serv->fdlist);

});

$serv->on('task', function ($serv, $task_id, $reactor_id, $data){
	//var_dump($task_id, $reactor_id, $data);
	$fd = $data;
	$serv->send($fd, str_repeat('B', 1024*rand(40, 60)).rand(10000, 99999)."\n");
});

$serv->on('finish', function ($serv, $fd, $reactor_id){

});

$serv->on('receive', function (Swoole\Server $serv, $fd, $reactor_id, $data) {

    foreach($serv->fdlist as $_fd=>$val) {
        $serv->send($_fd, "{$fd} say:".$data.PHP_EOL);
    }
});

$serv->on('close', function ($serv, $fd, $reactor_id) {
	//echo "[#".posix_getpid()."]\tClient@[$fd:$reactor_id]: Close.\n";
	unset($serv->fdlist[$fd]);
});

$serv->start();
