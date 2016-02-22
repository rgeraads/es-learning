<?php

final class Mysql
{
    /**
     * @var \PDO
     */
    private $connection;

    private function __construct(string $host, string $user, string $password, string $database)
    {
        $dsn = "mysql:dbname=$database;host=$host";

        $this->connection = new \PDO($dsn, $user, $password);
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
        return new Mysql($host, $user, $password, $database);
    }

    /**
     * @param string $table
     * @param array  $columns
     * @param array  $values
     *
     * @return array
     */
    public function select(string $table, array $columns = [], array $values = [])
    {
        $queryString = "SELECT * FROM `$table`";

        return $this->execute($queryString)->fetchAll();
    }

    /**
     * @param string $table
     * @param array  $columns
     * @param array  $values
     *
     * @return \PDOStatement
     */
    public function insert(string $table, array $columns, array $values)
    {
        $this->guardEqualAmountOfColumnsAndValues($columns, $values);

        $valueString  = '\'' . implode('\', \'', $values) . '\'';
        $columnString = implode(', ', $columns);

        $queryString = "INSERT INTO `$table` ($columnString) VALUES ($valueString)";

        return $this->execute($queryString);
    }

    /**
     * @param string $table
     * @param array  $columns
     * @param array  $values
     *
     * @return \PDOStatement
     */
    public function update(string $table, array $columns, array $values)
    {
        $this->guardEqualAmountOfColumnsAndValues($columns, $values);

        $columnsAndValues = array_map(function($column, $value) {
            return "$column='$value'";
        }, $columns, $values);

        $columnAndValueString  = implode(', ', $columnsAndValues);
        $queryString = "UPDATE `$table` SET $columnAndValueString";

        return $this->execute($queryString);
    }

    /**
     * @param string $table
     * @param string $column
     * @param string $value
     *
     * @return \PDOStatement
     */
    public function delete(string $table, string $column, string $value)
    {
        return $this->execute("DELETE FROM `$table` WHERE $column = $value");
    }

    /**
     * @param $queryString
     *
     * @return \PDOStatement|array
     */
    protected function execute($queryString)
    {
        return $this->connection->query($queryString) ?: $this->connection->errorInfo();
    }

    /**
     * @param array $columns
     * @param array $values
     */
    protected function guardEqualAmountOfColumnsAndValues(array $columns, array $values)
    {
        $columnCount = count($columns);
        $valueCount  = count($values);

        if ($columnCount !== $valueCount) {
            throw new \InvalidArgumentException(
                sprintf('Number of columns (%d) and values (%d) does not match', $columnCount, $valueCount)
            );
        }
    }
}
