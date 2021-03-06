--TEST--
swoole_runtime/sockets/basic: socket_recvfrom may return an invalid 'from' address on MacOS
--SKIPIF--
<?php require __DIR__ . '/../../../include/skipif.inc'; ?>
<?php
if (strtolower(substr(PHP_OS, 0, 3)) === 'win') {
    die('skip not valid for Windows.');
}
if (!extension_loaded('sockets')) {
    die('skip sockets extension not available.');
}?>
--FILE--
<?php
use Swoole\Runtime;
use function Swoole\Coroutine\run;

Runtime::setHookFlags(SWOOLE_HOOK_SOCKETS);

run(function () {


// This bug only occurs when a specific portion of memory is unclean.
// Unfortunately, looping around 10 times and using random paths is the
// best way I could manage to reproduce this problem without modifying php itself :-/

for ($i = 0; $i < 10; $i++) {
    $senderSocketPath = '/tmp/' . substr(md5(rand()), 0, rand(8, 16)) . '.sock';
    $senderSocket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
    socket_bind($senderSocket, $senderSocketPath);

    $receiverSocketPath = '/tmp/' . substr(md5(rand()), 0, rand(8, 16)) . '.sock';
    $receiverSocket = socket_create(AF_UNIX, SOCK_DGRAM, 0);
    socket_bind($receiverSocket, $receiverSocketPath);

    // Send message from sender socket to receiver socket
    socket_sendto($senderSocket, 'Ping!', 5, 0, $receiverSocketPath);

    // Receive message on receiver socket
    $from = '';
    $message = '';
    socket_recvfrom($receiverSocket, $message, 65535, 0, $from);
    echo "Received '$message'\n";

    // Respond to the sender using the 'from' address from socket_recvfrom
    socket_sendto($receiverSocket, 'Pong!', 5, 0, $from);
    echo "Responded to sender with 'Pong!'\n";

    socket_close($receiverSocket);
    unlink($receiverSocketPath);
    socket_close($senderSocket);
}
});
?>
--EXPECT--
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
Received 'Ping!'
Responded to sender with 'Pong!'
