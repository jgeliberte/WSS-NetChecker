<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;


 

class WSSNetChecker implements MessageComponentInterface {
    protected $clients;
    protected $dbconn;
    protected $qiInit;

    public function __construct() {
        $this->clients = new \SplObjectStorage;
    }

    //TODO: Resilience against Net Connection Loss
    //Create a protocol for checking whether the message was sent to GSM.
    //There should be a function that will attempt to send "PENDING" data
    //  to GSM everytime there is a new connection.

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        $this->time_start = microtime(true); 
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;

        $decodedText = json_decode($msg);

        if ($decodedText == NULL) {
            echo "Message is not in JSON format ($msg).\n";
            return;
        } else {
            echo "Pinging.. \n";
            $from->send(json_encode('connected')); 
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is losed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
        $this->time_end = microtime(true);
        $execution_time = ($this->time_end - $this->time_start)/60;
        echo 'Total Execution Time: '.$execution_time.' Mins'."\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

}