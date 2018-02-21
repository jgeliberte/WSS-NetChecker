<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class WSSNetCheckerModel {
    protected $dbconn;

    public function __construct() {
        $this->checkDatabaseConnection();
    }

    public function checkDatabaseConnection() {
        //Create a DB Connection
        $host = "localhost";
        $usr = "root";
        $pwd = "senslope";
        $dbname = "senslopedb";

        $this->dbconn = new \mysqli($host, $usr, $pwd);

        if ($this->dbconn->connect_error) {
            die("Connection failed: " . $this->dbconn->connect_error);
        }
        $this->connectSenslopeDB();
        echo "Successfully connected to database!\n";
    }

    public function insertNewErrorLog($data) {
        $status = "";
        $log_description_query = "INSERT INTO error_log_description VALUES (0,(SELECT id FROM error_log_modules WHERE module_code = 'CTBX'),'".$data['timestamp']."','".$data['error_descriptio']."');";
        if ($this->dbconn->query($query) === TRUE) {
           $log_query = "INSERT INTO error_logs VALUES (0,(SELECT id FROM error_log_modules WHERE module_code = 'CTBX'),(SELECT id FROM error_log_description WHERE timestamp = '".$data['timestamp']."'))";
           if ($this->dbconn->query($log_query) === TRUE) {
                $status = "Error log entry successfully added..\n";
           } else {
                $status = "Failed to add Error log entry..\n";
           }
        } else {
            $status = "Failed to add new error log: " . $this->dbconn->error." \n";
        }
        return $status;
    }

    public function initializeErrorLogsDB() {
        echo "Initializing Error logs database..\n";
        $error_log_description_query = "CREATE TABLE error_log_description (id int(11) NOT NULL AUTO_INCREMENT,module_id_fk int(11) DEFAULT NULL,timestamp date DEFAULT NULL,description varchar(45) DEFAULT NULL,PRIMARY KEY (id),KEY module_id_fk_idx (module_id_fk),
          CONSTRAINT module_id_fk_1 FOREIGN KEY (module_id_fk) REFERENCES error_log_modules (id) ON DELETE NO ACTION ON UPDATE NO ACTION) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        $error_log_modulees_query = "CREATE TABLE error_log_modules (id int(11) NOT NULL AUTO_INCREMENT,module_code varchar(45) DEFAULT NULL,module_name varchar(45) DEFAULT NULL,PRIMARY KEY (id)) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;";

        $error_log_query = "CREATE TABLE error_logs (id int(11) NOT NULL AUTO_INCREMENT,module_id_fk int(11) DEFAULT NULL,
          error_id_fk int(11) DEFAULT NULL,PRIMARY KEY (id),KEY module_id_fk_idx (module_id_fk),KEY error_id_fk_idx (error_id_fk),CONSTRAINT error_id_fk FOREIGN KEY (error_id_fk) REFERENCES error_log_description (id) ON DELETE NO ACTION ON UPDATE NO ACTION,CONSTRAINT module_id_fk FOREIGN KEY (module_id_fk) REFERENCES error_log_modules (id) ON DELETE NO ACTION ON UPDATE NO ACTION) ENGINE=InnoDB DEFAULT CHARSET=latin1;";

        if ($this->dbconn->query($error_log_modulees_query) === TRUE) {
            echo "Table 'error_log_description' exists!\n";
            if ($this->dbconn->query($error_log_description_query) === TRUE) {
                echo "Table 'error_log_modules_query' exists!\n";
                if ($this->dbconn->query($error_log_query) === TRUE) {
                    echo "Table 'error_log' exists!\n";
                } else {
                    echo "Error creating table 'error_log'!\n";
                }
            } else {
                echo "Error creating table 'error_log_modules'!\n";
            }
        } else {
            echo "Error log tables already exists!\n";
        }

        echo "Initialization complete..\n";
    }

    public function connectSenslopeDB() {
        $success = mysqli_select_db($this->dbconn, "senslopedb");
        if ($success) {
            $this->initializeErrorLogsDB();
        } else {
            echo "Table for error logging exists!\n";
        }
        return $success;
    }
}