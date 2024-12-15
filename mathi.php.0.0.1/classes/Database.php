<?php

class Database
{
    private $host, $user, $password, $database;
    protected $connection;

    // Constructor
    public function __construct()
    {
        global $config;

        $this->host     = $config['host'] ?? null;
        $this->user     = $config['user'] ?? null;
        $this->password = $config['password'] ?? null;
        $this->database = $config['database'] ?? null;

        $this->connectDb();
    }

    // Connect to the database
    private function connectDb()
    {
        $this->connection = new mysqli($this->host, $this->user, $this->password, $this->database);

        if ($this->connection->connect_error) 
        {
            die("Connection failed:: " . $this->connection->connect_error);
        }
    }

    // Kill connection to the database
    public function killConnection()
    {
        if ($this->connection) 
        {
            $this->connection->close();
        }
    }
}
