--TEST--
swoole_process: exec
--SKIPIF--
<?php
require __DIR__ . '/../include/skipif.inc';
if (!@file_exists('/usr/bin/python')) {
    exit('skip if no python');
}
?>
--FILE--
<?php
require __DIR__ . '/../include/bootstrap.php';

$process = new Swoole\Process('python_process', true);
$pid = $process->start();

function python_process(Swoole\Process $worker)
{
    $worker->exec('/usr/bin/python', array(__DIR__ . "/echo.py"));
}

$process->write("Hello World\n");
echo $process->read();
?>
Done
--EXPECTREGEX--
Python: Hello World
Done.*
