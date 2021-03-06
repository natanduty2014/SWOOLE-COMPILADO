--TEST--
swoole_runtime/sockets/basic: Test parameter handling in socket_select().
--SKIPIF--
<?php require __DIR__ . '/../../../include/skipif.inc'; ?>
<?php
if (!extension_loaded('sockets')) {
    die('SKIP The sockets extension is not loaded.');
}
die('skip unsupport');
?>
--FILE--
<?php
use Swoole\Runtime;
use function Swoole\Coroutine\run;

Runtime::setHookFlags(SWOOLE_HOOK_SOCKETS);

run(function () {

$sockets = array();
if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
    $domain = AF_INET;
} else {
    $domain = AF_UNIX;
}
socket_create_pair($domain, SOCK_STREAM, 0, $sockets);

$write  = null;
$except = null;
$ref =& $sockets[0]; // bug #78038
var_dump(socket_select($sockets, $write, $except, 0));
});
?>
--EXPECT--
int(0)
--CREDITS--
Till Klampaeckel, till@php.net
Berlin TestFest 2009
