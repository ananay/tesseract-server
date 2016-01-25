<?php namespace LiveScores;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Scores implements MessageComponentInterface {
    
	private $clients;
    private $games;

	public function __construct() {
        
        // Create a collection of clients
        $this->clients = new \SplObjectStorage;

    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        // New connection, send it the current set of matches
        $conn->send(json_encode(array('type' => 'init', 'games' => $this->games)));

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        
        foreach ($this->clients as $client) {        
            $client->send($msg);            
        }

        $message = json_decode($msg);

        switch ($message->type) {
            case 'goal':
                echo "Goal!\n";
                $this->games[$message->game][$message->team]['score']++;
                break;
        }

    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }


}