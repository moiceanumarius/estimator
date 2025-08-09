<?php

require_once __DIR__ . '/vendor/autoload.php';

use Core\WebSocketServer;

// Run the WebSocket server
WebSocketServer::run(8080);

