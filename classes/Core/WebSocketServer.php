<?php

namespace Core;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoConnection;

class WebSocketServer implements MessageComponentInterface
{
    protected $clients;
    protected $rooms = [];
    protected $database;
    protected $heartbeatInterval = 30; // seconds
    protected $maxIdleTime = 300; // 5 minutes

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->database = new Database();
        
        // Start heartbeat timer
        $this->startHeartbeat();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);
        
        // Set connection metadata
        $conn->connectedAt = time();
        $conn->lastActivity = time();
        $conn->heartbeatCount = 0;
        
        // Parse query parameters safely (PSR-7 UriInterface)
        $params = [];
        try {
            $uri = $conn->httpRequest->getUri();
            $query = method_exists($uri, 'getQuery') ? $uri->getQuery() : parse_url((string)$uri, PHP_URL_QUERY);
            if (is_string($query)) {
                parse_str($query, $params);
            }
        } catch (\Throwable $t) {
            error_log('WebSocket onOpen: failed to parse URI query: ' . $t->getMessage());
        }
        
        $roomId = $params['room'] ?? '';
        $userName = $params['user'] ?? '';
        
        if ($roomId && $userName) {
            $conn->roomId = $roomId;
            $conn->userName = $userName;
            
            if (!isset($this->rooms[$roomId])) {
                $this->rooms[$roomId] = new \SplObjectStorage;
            }
            $this->rooms[$roomId]->attach($conn);
            
            echo "New connection! ({$conn->resourceId}) - Room: {$roomId}, User: {$userName}\n";
            
            // Send welcome message with connection info
            $conn->send(json_encode([
                'type' => 'connection_established',
                'payload' => [
                    'message' => 'Connected successfully',
                    'timestamp' => time(),
                    'heartbeat_interval' => $this->heartbeatInterval
                ]
            ]));
            
            // Broadcast user joined to all other users in the room
            $this->broadcastToRoom($roomId, [
                'type' => 'user_joined',
                'payload' => [
                    'userName' => $userName,
                    'users' => $this->getRoomVotes($roomId)
                ]
            ], $conn);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // Update last activity
        $from->lastActivity = time();
        
        $data = json_decode($msg, true);
        $roomId = $from->roomId ?? '';
        
        // Handle heartbeat/ping messages
        if (isset($data['type']) && $data['type'] === 'ping') {
            $from->send(json_encode([
                'type' => 'pong',
                'payload' => [
                    'timestamp' => time(),
                    'heartbeat_count' => ++$from->heartbeatCount
                ]
            ]));
            return;
        }
        
        if (!$roomId || !isset($this->rooms[$roomId])) {
            return;
        }
        
        switch ($data['type'] ?? '') {
            case 'vote':
                $this->handleVote($from, $data, $roomId);
                break;
            case 'flip':
                $this->handleFlip($from, $data, $roomId);
                break;
            case 'reset':
                $this->handleReset($from, $data, $roomId);
                break;
            case 'remove_user':
                $this->handleRemoveUser($from, $data, $roomId);
                break;
            case 'user_joined':
                $this->broadcastToRoom($roomId, [
                    'type' => 'user_joined',
                    'payload' => $data['payload'] ?? []
                ], $from);
                break;
            case 'user_left':
                $this->broadcastToRoom($roomId, [
                    'type' => 'user_left',
                    'payload' => $data['payload'] ?? []
                ], $from);
                break;
            case 'keepalive':
                // Simple keepalive response
                $from->send(json_encode([
                    'type' => 'keepalive_ack',
                    'payload' => ['timestamp' => time()]
                ]));
                break;
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);
        
        $roomId = $conn->roomId ?? '';
        if ($roomId && isset($this->rooms[$roomId])) {
            $this->rooms[$roomId]->detach($conn);
            
            // Broadcast user left
            $this->broadcastToRoom($roomId, [
                'type' => 'user_left',
                'payload' => ['userName' => $conn->userName ?? '']
            ], $conn);
        }
        
        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";
        
        // Log error details
        error_log("WebSocket error for connection {$conn->resourceId}: " . $e->getMessage());
        
        // Try to send error message to client before closing
        try {
            $conn->send(json_encode([
                'type' => 'error',
                'payload' => [
                    'message' => 'Connection error occurred',
                    'code' => $e->getCode(),
                    'timestamp' => time()
                ]
            ]));
        } catch (\Exception $sendError) {
            echo "Could not send error message to client: " . $sendError->getMessage() . "\n";
        }
        
        $conn->close();
    }

    protected function startHeartbeat()
    {
        // Only start timers if we have an event loop running
        // For now, we'll handle heartbeat differently since we can't easily access the loop here
        // The timers will be handled by the main server loop
    }

    protected function sendHeartbeat()
    {
        $heartbeatMessage = json_encode([
            'type' => 'heartbeat',
            'payload' => [
                'timestamp' => time(),
                'server_time' => date('Y-m-d H:i:s')
            ]
        ]);
        
        foreach ($this->clients as $client) {
            try {
                if ($client->isConnected()) {
                    $client->send($heartbeatMessage);
                }
            } catch (\Exception $e) {
                echo "Error sending heartbeat to client {$client->resourceId}: " . $e->getMessage() . "\n";
                // Mark for cleanup
                $client->lastActivity = 0;
            }
        }
    }

    protected function cleanupIdleConnections()
    {
        $currentTime = time();
        $connectionsToClose = [];
        
        foreach ($this->clients as $client) {
            if (isset($client->lastActivity) && 
                ($currentTime - $client->lastActivity) > $this->maxIdleTime) {
                $connectionsToClose[] = $client;
            }
        }
        
        foreach ($connectionsToClose as $client) {
            echo "Closing idle connection {$client->resourceId}\n";
            try {
                $client->send(json_encode([
                    'type' => 'connection_timeout',
                    'payload' => [
                        'message' => 'Connection closed due to inactivity',
                        'timestamp' => time()
                    ]
                ]));
                $client->close();
            } catch (\Exception $e) {
                echo "Error closing idle connection: " . $e->getMessage() . "\n";
            }
        }
    }

    protected function handleVote($from, $data, $roomId)
    {
        // Update vote in the room
        $this->updateRoomVote($roomId, $data['userId'] ?? '', $data['vote'] ?? null);
        
        // Broadcast updated votes to all users in the room
        $votes = $this->getRoomVotes($roomId);
        $this->broadcastToRoom($roomId, [
            'type' => 'votes_update',
            'payload' => [
                'votes' => $votes,
                'revealed' => $this->isRoomRevealed($roomId)
            ]
        ]);
    }

    protected function handleFlip($from, $data, $roomId)
    {
        // Check if user is admin
        if ($this->isUserAdmin($roomId, $from->userName ?? '')) {
            $this->revealRoom($roomId);
            
            $this->broadcastToRoom($roomId, [
                'type' => 'vote_revealed',
                'payload' => []
            ]);
        }
    }

    protected function handleReset($from, $data, $roomId)
    {
        // Check if user is admin
        if ($this->isUserAdmin($roomId, $from->userName ?? '')) {
            $this->resetRoom($roomId);
            
            $this->broadcastToRoom($roomId, [
                'type' => 'vote_reset',
                'payload' => []
            ]);
        }
    }

    protected function handleRemoveUser($from, $data, $roomId)
    {
        if (!$this->isUserAdmin($roomId, $from->userName ?? '')) {
            return;
        }
        $userName = $data['payload']['userName'] ?? '';
        $userId = $data['payload']['userId'] ?? '';
        if (!$userName && !$userId) return;
        
        // Notify clients that a user was removed so they can refresh state
        $this->broadcastToRoom($roomId, [
            'type' => 'user_removed',
            'payload' => [ 'userName' => $userName, 'userId' => $userId ]
        ]);

        // Proactively close the removed user's WS connection in this room (match by userName)
        if (isset($this->rooms[$roomId])) {
            $clientsToClose = [];
            foreach ($this->rooms[$roomId] as $client) {
                if (!empty($userName) && ($client->userName ?? '') === $userName) {
                    $clientsToClose[] = $client;
                }
            }
            foreach ($clientsToClose as $client) {
                try {
                    $client->close();
                } catch (\Exception $e) {
                    // ignore
                }
                $this->rooms[$roomId]->detach($client);
                $this->clients->detach($client);
            }
        }
    }

    protected function broadcastToRoom($roomId, $data, $exclude = null)
    {
        if (!isset($this->rooms[$roomId])) {
            return;
        }
        
        $message = json_encode($data);
        
        foreach ($this->rooms[$roomId] as $client) {
            if ($client !== $exclude) {
                try {
                    $client->send($message);
                } catch (\Exception $e) {
                    echo "Error broadcasting to client {$client->resourceId}: " . $e->getMessage() . "\n";
                    // Mark for cleanup
                    $client->lastActivity = 0;
                }
            }
        }
    }

    protected function updateRoomVote($roomId, $userId, $vote)
    {
        // Load room data from JSON file using Database class
        $roomFile = $this->database->getUsersFile($roomId);
        if (file_exists($roomFile)) {
            $users = json_decode(file_get_contents($roomFile), true) ?: [];
            
            foreach ($users as &$user) {
                if ($user['id'] === $userId) {
                    $user['vote'] = $vote;
                    break;
                }
            }
            
            $this->database->writeJson($roomFile, $users);
        }
    }

    protected function getRoomVotes($roomId)
    {
        $roomFile = $this->database->getUsersFile($roomId);
        if (file_exists($roomFile)) {
            return json_decode(file_get_contents($roomFile), true) ?: [];
        }
        return [];
    }

    protected function isRoomRevealed($roomId)
    {
        $flipFile = $this->database->getFlipFile($roomId);
        if (file_exists($flipFile)) {
            $flip = json_decode(file_get_contents($flipFile), true) ?: [];
            return $flip['revealed'] ?? false;
        }
        return false;
    }

    protected function revealRoom($roomId)
    {
        $flipFile = $this->database->getFlipFile($roomId);
        $flip = ['revealed' => true];
        $this->database->writeJson($flipFile, $flip);
    }

    protected function resetRoom($roomId)
    {
        // Reset flip state
        $flipFile = $this->database->getFlipFile($roomId);
        $flip = ['revealed' => false];
        $this->database->writeJson($flipFile, $flip);
        
        // Reset all votes
        $roomFile = $this->database->getUsersFile($roomId);
        if (file_exists($roomFile)) {
            $users = json_decode(file_get_contents($roomFile), true) ?: [];
            
            foreach ($users as &$user) {
                $user['vote'] = null;
            }
            
            $this->database->writeJson($roomFile, $users);
        }
    }

    protected function isUserAdmin($roomId, $userName)
    {
        $roomFile = $this->database->getUsersFile($roomId);
        if (file_exists($roomFile)) {
            $users = json_decode(file_get_contents($roomFile), true) ?: [];
            
            foreach ($users as $user) {
                if ($user['name'] === $userName) {
                    return $user['isAdmin'] ?? false;
                }
            }
        }
        return false;
    }

    public static function run($port = 8080)
    {
        // Check if localhost SSL certificates exist
        $sslCert = '/etc/apache2/ssl/localhost.crt';
        $sslKey = '/etc/apache2/ssl/localhost.key';
        
        // For development, disable SSL as Ratchet 0.4.4 has SSL implementation issues
        $useSSL = false; // Disable SSL for development compatibility
        
        if ($useSSL && file_exists($sslCert) && file_exists($sslKey)) {
            // Create SSL context
            $context = stream_context_create([
                'ssl' => [
                    'local_cert' => $sslCert,
                    'local_pk' => $sslKey,
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                    'disable_compression' => true,
                ]
            ]);
            
            // Run with SSL support for localhost
            $server = IoServer::factory(
                new HttpServer(
                    new WsServer(
                        new self()
                    )
                ),
                $port,
                '0.0.0.0',  // Listen on all interfaces
                $context
            );
            
            echo "WebSocket SSL server started on 0.0.0.0:{$port} with localhost certificate\n";
        } else {
            // Run without SSL for development (better Safari compatibility)
            $server = IoServer::factory(
                new HttpServer(
                    new WsServer(
                        new self()
                    )
                ),
                $port,
                '0.0.0.0'  // Listen on all interfaces
            );
            
            echo "WebSocket server started on 0.0.0.0:{$port} (no SSL - development mode)\n";
        }
        
        $server->run();
    }
}

