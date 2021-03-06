--TEST--
swoole_server/task: task enable coroutine with return value to finish
--SKIPIF--
<?php
require __DIR__ . '/../../include/skipif.inc';
skip_if_pdo_not_support_mysql8();
?>
--FILE--
<?php
require __DIR__ . '/../../include/bootstrap.php';
Swoole\Runtime::enableCoroutine();
$pm = new SwooleTest\ProcessManager;
$pm->parentFunc = function (int $pid) use ($pm) {
    for ($i = MAX_CONCURRENCY_LOW; $i--;) {
        go(function () use ($pm) {
            $ret = httpGetBody("http://127.0.0.1:{$pm->getFreePort()}");
            Assert::assert($ret === 'Hello Swoole!');
        });
    }
    Swoole\Event::wait();
    echo "DONE\n";
    $pm->kill();
};
$pm->childFunc = function () use ($pm) {
    $http = new Swoole\Http\Server('127.0.0.1', $pm->getFreePort(), SERVER_MODE_RANDOM);
    $http->set([
        'log_file' => '/dev/null',
        'task_worker_num' => 4,
        'task_enable_coroutine' => true
    ]);
    $http->on('request', function (Swoole\Http\Request $request, Swoole\Http\Response $response) use ($http) {
        Assert::assert($response->detach());
        $http->task($response->fd, -1, function ($server, $taskId, $data) {
            list($fd, $data) = $data;
            $response = Swoole\Http\Response::create($fd);
            $response->end($data);
        });
    });
    $http->on('task', function (Swoole\Http\Server $server, Swoole\Server\Task $task) {
        defer(function ($data) {
            if (!empty($data)) {
                list($task, $result) = $data;
                $task->finish($result);
            }
        });
        $fd = $task->data;
        $pdo = new PDO(
            "mysql:host=" . MYSQL_SERVER_HOST . ";port=" . MYSQL_SERVER_PORT . ";dbname=" . MYSQL_SERVER_DB . ";charset=utf8",
            MYSQL_SERVER_USER, MYSQL_SERVER_PWD
        );
        $stmt = $pdo->query('SELECT "Hello Swoole!"');
        Assert::assert($stmt->execute());
        $ret = $stmt->fetchAll(PDO::FETCH_COLUMN)[0];
        return [$task, [$fd, $ret]];
    });
    $http->on('finish', function ($server, $taskId, $data) {
        list($fd, $ret) = $data;
        $response = Swoole\Http\Response::create($fd);
        $response->end($ret);
    });
    $http->start();
};
$pm->childFirst();
$pm->run();
?>
--EXPECT--
DONE
