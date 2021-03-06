<?php
class Database {
    
    // Empty constructor
    public function __construct() {
        
    }

    // Only this class should need to open the actual connection
    private function openDB() {
        try {
            $con = new PDO(DB_PATH);
            $con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Error " . $e->getMessage());
        }
        return $con;
    }

    public function query($sql) {
        $con = $this->openDB();
        $result = $con->query($sql);
        $result->setFetchMode(PDO::FETCH_ASSOC);
        return $result;
    }

    private function createSchemaTables($con) {
        $con->exec("CREATE TABLE IF NOT EXISTS temps (id INTEGER PRIMARY KEY, timestamp TEXT, sensor TEXT, value REAL)");
    }

    public function logValueToDB($sensorName, $value) {
        if (!is_null($value)) {
            try {
                $con = $this->openDB();
                $stmt = $con->prepare("INSERT INTO temps (timestamp, sensor, value) VALUES (datetime(), :sensorName, :value);");
                $stmt->bindParam(':sensorName', $sensorName);
                $stmt->bindParam(':value', $value);
                $stmt->execute();
            } catch(PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    }
}
global $Database;
$Database = new Database();