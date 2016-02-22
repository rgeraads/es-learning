<?php

final class UserRepository
{
    const MYSQL   = 'mysql';
    const MONGODB = 'mongodb';

    private $table;

    /**
     * @var Mysql
     */
    private $connection;

    private function __construct(string $dbal, string $host, string $user, string $password, string $database)
    {
        $this->table = $this->getTableName();

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
        $columns = $this->getColumnNames($user);

        $users = $this->connection->select($this->getTableName($user));

        $reflectedUser = new ReflectionClass($user);

        $id = $reflectedUser->getProperty('id');
        $firstName = $reflectedUser->getProperty('firstName');
        $lastName = $reflectedUser->getProperty('lastName');

        $id->setAccessible(true);
        $firstName->setAccessible(true);
        $lastName->setAccessible(true);

        if (count($users) === 0) {
            $this->connection->insert($this->table, $columns, [$id->getValue($user), $firstName->getValue($user), $lastName->getValue($user)]);
        } else {
            $this->connection->update($this->table, $columns, [$id->getValue($user), $firstName->getValue($user), $lastName->getValue($user)]);
        }
    }

    public function load(int $id): array
    {
        $users = $this->connection->select($this->table, ['id'], [$id]);

        if (count($users) > 1) {
            throw new \Exception(sprintf('Too many users found for id %d. Expected 1, got %d', $id, count($users)));
        }

        if (count($users) !== 1) {
            throw new \Exception(sprintf('User not found for id %d', $id));
        }

        return current($users);
    }

    private function getTableName(): string
    {
        $classPieces = preg_split('/(?=[A-Z])/', get_class($this), -1, PREG_SPLIT_NO_EMPTY);
        $allButLast  = array_slice($classPieces, 0, -1);

        return strtolower(implode('', $allButLast));
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
