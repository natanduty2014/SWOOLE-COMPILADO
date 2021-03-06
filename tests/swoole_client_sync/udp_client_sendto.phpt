--TEST--
swoole_client_sync: udp sync client sendto
--SKIPIF--
<?php require __DIR__ . '/../include/skipif.inc'; ?>
--FILE--
<?php
require __DIR__ . '/../include/bootstrap.php';

$simple_tcp_server = __DIR__ . "/../include/api/swoole_server/simple_udp_server.php";
start_server($simple_tcp_server, UDP_SERVER_HOST, UDP_SERVER_PORT);

$client = new Swoole\Client(SWOOLE_SOCK_UDP);
$client->connect(UDP_SERVER_HOST, UDP_SERVER_PORT);

$ret = $client->sendto(UDP_SERVER_HOST, UDP_SERVER_PORT, "TestUdpClientSendto");
$message = $client->recv();
echo "FromServer:{$message}\n";
echo "SUCCESS";

?>
--EXPECT--
FromServer:TestUdpClientSendto
SUCCESS
