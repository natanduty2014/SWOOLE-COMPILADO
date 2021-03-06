--TEST--
swoole_runtime/sockets/basic: mixed socket_getopt( resource $socket , int $level , int $optname ) ;
--CREDITS--
marcosptf -
<marcosptf@yahoo.com.br> - #phparty7 - @phpsp - novatec/2015 - sao paulo - br
--SKIPIF--
<?php require __DIR__ . '/../../../include/skipif.inc'; ?>
<?php
if (!extension_loaded('sockets')) {
    die('skip sockets extension not available.');
}
?>
--FILE--
<?php

use Swoole\Runtime;

use function Swoole\Coroutine\run;

Runtime::setHookFlags(SWOOLE_HOOK_SOCKETS);

run(function () {
    $domain = AF_INET;
    $level = IPPROTO_IP;
    $s = socket_create($domain, SOCK_DGRAM, SOL_UDP) or die("err");

    echo "Setting IP_MULTICAST_TTL\n";
    $r = socket_set_option($s, $level, IP_MULTICAST_TTL, 9);
    var_dump($r);
    $r = socket_getopt($s, $level, IP_MULTICAST_TTL);
    var_dump($r);
    echo "\n";

    echo "Setting IP_MULTICAST_LOOP\n";
    $r = socket_set_option($s, $level, IP_MULTICAST_LOOP, 0);
    var_dump($r);
    $r = socket_getopt($s, $level, IP_MULTICAST_LOOP);
    var_dump($r);
    $r = socket_set_option($s, $level, IP_MULTICAST_LOOP, 1);
    var_dump($r);
    $r = socket_getopt($s, $level, IP_MULTICAST_LOOP);
    var_dump($r);
    echo "\n";

    echo "Setting IP_MULTICAST_IF\n";
    echo "interface 0:\n";
    $r = socket_set_option($s, $level, IP_MULTICAST_IF, 0);
    var_dump($r);
    $r = socket_getopt($s, $level, IP_MULTICAST_IF);
    var_dump($r);
    echo "interface 1:\n";
    $r = socket_set_option($s, $level, IP_MULTICAST_IF, 1);
    var_dump($r);
    $r = socket_getopt($s, $level, IP_MULTICAST_IF);
    var_dump($r);
    echo "\n";

    unset($domain);
    unset($level);
    socket_close($s);
    unset($s);
    unset($r);
});
?>
--EXPECT--
Setting IP_MULTICAST_TTL
bool(true)
int(9)

Setting IP_MULTICAST_LOOP
bool(true)
int(0)
bool(true)
int(1)

Setting IP_MULTICAST_IF
interface 0:
bool(true)
int(0)
interface 1:
bool(true)
int(1)
