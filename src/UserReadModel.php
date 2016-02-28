<?php

declare(strict_types = 1);

final class UserReadModel
{
    private $id;
    private $firstName;
    private $lastName;

    const NO_RETURN_VALUE = 'void';

    public function __construct(int $id, string $firstName, string $lastName)
    {
        $this->guardPublicMethodsHaveReturTypes();

        $this->id        = $id;
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
    }

    public function foo(): string
    {
        return;
    }

    public static function fromState(array $state): UserReadModel
    {
        return new UserReadModel((int) $state['id'], $state['firstname'], $state['lastname']);
    }

    public function getId(): integer
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    private function guardPublicMethodsHaveReturTypes()
    {
        $checker = new ReturValuesChecker();
        $checker->scanDir(dirname(getcwd()));
    }
}
