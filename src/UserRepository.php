<?php

final class UserRepository
{
    const MYSQL   = 'mysql';
    const MONGODB = 'mongodb';

    /**
     * @var Mysql
     */
    private $connection;

    private function __construct(string $dbal, string $host, string $user, string $password, string $database)
    {
        switch ($dbal) {
            case self::MYSQL:
                $this->connection = Mysql::connect($host, $user, $password, $database);
                break;
            case self::MONGODB:
                $this->connection = Mongodb::connect($host, $user, $password, $database);
        }
    }

    public static function mysql(string $host, string $user, string $password, string $database)
    {
        return new UserRepository(self::MYSQL, $host, $user, $password, $database);
    }

    public static function mongodb(string $host, string $user, string $password, string $database)
    {
        return new UserRepository(self::MONGODB, $host, $user, $password, $database);
    }

    public function save(User $user)
    {
        $table   = $this->getTableName($user);
        $columns = $this->getColumnNames($user);

        $users = $this->connection->select($this->getTableName($user), [], []);
        if (count($users) === 0) {
            $this->connection->insert($table, $columns, [$user->getId(), $user->getFirstName(), $user->getLastName()]);
        } else {
            $this->connection->update($table, $columns, [$user->getId(), $user->getFirstName(), $user->getLastName()]);
        }

    }

    private function getTableName(User $user): string
    {
        $table = strtolower(get_class($user));

        return $table;
    }

    private function getColumnNames(User $user): array
    {
        $columns = array_map(function ($property) {
            /** @var \ReflectionProperty $property */
            return strtolower($property->getName());
        }, (new \ReflectionClass($user))->getProperties());

        return $columns;
    }
}
