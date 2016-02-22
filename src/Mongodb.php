<?php

final class Mongodb
{
    /**
     * @var \mysqli
     */
    private $connection;

    private function __construct(string $host, string $user, string $password, string $database)
    {
        $this->connection = mysqli_connect($host, $user, $password, $database) or exit('Cannot connect');
        $this->error();
    }

    /**
     * @param string $host
     * @param string $user
     * @param string $password
     * @param string $database
     *
     * @return bool
     */
    public static function connect(string $host, string $user, string $password, string $database)
    {
        return new Mongodb($host, $user, $password, $database) or exit('Cannot connect');
    }

    protected function error()
    {
        if (mysqli_errno($this->connection) > 0) {
            exit(mysqli_error($this->connection));
        }
    }

    /**
     * @param string $queryString
     *
     * @return bool|\mysqli_result
     */
    protected function query(string $queryString)
    {
        $result = mysqli_query($this->connection, $queryString);
        $this->error();

        return $result;
    }

    /**
     * @param string $queryString
     *
     * @return array
     */
    public function select(string $queryString)
    {
        $result = $this->query($queryString);
        $this->error();

        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @param string $queryString
     *
     * @return int|string
     */
    public function insert(string $queryString)
    {
        $this->query($queryString);
        $this->error();

        return mysqli_insert_id($this->connection);
    }

    /**
     * @param string $queryString
     *
     * @return int
     */
    public function update(string $queryString)
    {
        $this->query($queryString);
        $this->error();

        return mysqli_affected_rows($this->connection);
    }

    /**
     * @param string $queryString
     *
     * @return int
     */
    public function delete(string $queryString)
    {
        $this->query($queryString);
        $this->error();

        return mysqli_affected_rows($this->connection);
    }
}
