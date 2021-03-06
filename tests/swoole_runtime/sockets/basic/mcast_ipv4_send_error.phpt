--TEST--
swoole_runtime/sockets/basic: IPv4 send options with unusual values
--SKIPIF--
<?php require __DIR__ . '/../../../include/skipif.inc'; ?>
<?php
if (!extension_loaded('sockets')) {
    die('skip sockets extension not available.');
}
$domain = AF_INET;
$level = IPPROTO_IP;
$s = socket_create($domain, SOCK_DGRAM, SOL_UDP);
if ($s === false) {
    die("skip unable to create socket");
}
if (socket_set_option($s, $level, IP_MULTICAST_IF, 1) === false) {
    die("skip interface 1 either doesn't exist or has no ipv4 address");
}?>
--FILE--
<?php
require __DIR__ . '/../../../include/bootstrap.php';
use Swoole\Runtime;
use function Swoole\Coroutine\run;

Runtime::setHookFlags(SWOOLE_HOOK_SOCKETS);

run(function () {
    $domain = AF_INET;
    $level = IPPROTO_IP;
    $s = socket_create($domain, SOCK_DGRAM, SOL_UDP) or die("err");
    Assert::assert(is_object($s));
    echo "Setting IP_MULTICAST_LOOP with 256\n";
//if we had a simple cast to unsigned char, this would be the same as 0
    $r = @socket_set_option($s, $level, IP_MULTICAST_LOOP, 256);
    var_dump($r);
    $r = @socket_get_option($s, $level, IP_MULTICAST_LOOP);
    var_dump($r);

    echo "Setting IP_MULTICAST_LOOP with false\n";
//should convert to (unsigned char)0
    $r = @socket_set_option($s, $level, IP_MULTICAST_LOOP, false);
    var_dump($r);
    $r = @socket_get_option($s, $level, IP_MULTICAST_LOOP);
    var_dump($r);

    echo "Setting IP_MULTICAST_TTL with 256\n";
//if we had a simple cast to unsigned char, this would be the same as 0
    try {
        $r = @socket_set_option($s, $level, IP_MULTICAST_TTL, 256);
        var_dump($r);
    } catch (\ValueError $e) {
        echo $e->getMessage() . \PHP_EOL;
    }
    $r = @socket_get_option($s, $level, IP_MULTICAST_TTL);
    var_dump($r);

    echo "Setting IP_MULTICAST_TTL with \"254\"\n";
    $r = @socket_set_option($s, $level, IP_MULTICAST_TTL, "254");
    var_dump($r);
    $r = @socket_get_option($s, $level, IP_MULTICAST_TTL);
    var_dump($r);

    echo "Setting IP_MULTICAST_TTL with -1\n";
//should give error, not be the same as 255
    try {
        $r = @socket_set_option($s, $level, IP_MULTICAST_TTL, -1);
        var_dump($r);
    } catch (\ValueError $e) {
        echo $e->getMessage() . \PHP_EOL;
    }
    $r = @socket_get_option($s, $level, IP_MULTICAST_TTL);
    var_dump($r);
});
?>
--EXPECT--
Setting IP_MULTICAST_LOOP with 256
bool(true)
int(1)
Setting IP_MULTICAST_LOOP with false
bool(true)
int(0)
Setting IP_MULTICAST_TTL with 256
bool(false)
int(1)
Setting IP_MULTICAST_TTL with "254"
bool(true)
int(254)
Setting IP_MULTICAST_TTL with -1
bool(false)
int(254)
