<?php
// Database connection constants
define("DB_SERVER", "YOUR_DB_SERVER");
define("DB_USERNAME", "YOUR_DB_USERNAME");
define("DB_PASSWORD", "YOUR_DB_PASSWORD");
define("DB_NAME", "payment");

// Click payment integration constants
define("MERCHANT_ID", "YOUR_MERCHANT_ID");
define("SERVICE_ID", "YOUR_SERVICE_ID");
define("MERCHANT_USER_ID", "YOUR_MERCHANT_USER_ID");
define("SECRET_KEY", "YOUR_SECRET_KEY");

class Database
{
    private $conn;

    public function __construct()
    {
        $this->conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

        if ($this->conn->connect_error) {
            die("Database connection error: " . $this->conn->connect_error);
        }
    }

    public function __destruct()
    {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    public function executeQuery($sql, $params = [], $types = "")
    {
        $result = $this->conn->prepare($sql);

        if (!$result) {
            return "SQL error: " . $this->conn->error;
        }

        if ($params) {
            $result->bind_param($types, ...$params);
        }

        if (!$result->execute()) {
            return "Execution error: " . $result->error;
        }

        return $result;
    }

    public function validate($value)
    {
        return htmlspecialchars(trim(stripslashes($value)), ENT_QUOTES, 'UTF-8');
    }

    public function select($table, $columns = "*", $condition = "", $params = [], $types = "")
    {
        $sql = "SELECT $columns FROM $table" . ($condition ? " WHERE $condition" : "");
        $result = $this->executeQuery($sql, $params, $types);

        if (is_string($result)) {
            return $result;
        }

        return $result->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function insert($table, $data)
    {
        $keys = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO $table ($keys) VALUES ($placeholders)";
        $types = str_repeat('s', count($data));

        $result = $this->executeQuery($sql, array_values($data), $types);
        if (is_string($result)) {
            return $result;
        }

        return $this->conn->insert_id;
    }

    public function update($table, $data, $condition = "", $params = [], $types = "")
    {
        $set = implode(", ", array_map(function ($k) {
            return "$k = ?";
        }, array_keys($data)));
        $sql = "UPDATE $table SET $set" . ($condition ? " WHERE $condition" : "");
        $types = str_repeat('s', count($data)) . $types;

        $result = $this->executeQuery($sql, array_merge(array_values($data), $params), $types);
        if (is_string($result)) {
            return $result;
        }

        return $this->conn->affected_rows;
    }

    public function delete($table, $condition = "", $params = [], $types = "")
    {
        $sql = "DELETE FROM $table" . ($condition ? " WHERE $condition" : "");

        $result = $this->executeQuery($sql, $params, $types);
        if (is_string($result)) {
            return $result;
        }

        return $this->conn->affected_rows;
    }
}