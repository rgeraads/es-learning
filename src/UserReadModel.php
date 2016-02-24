<?php

final class UserReadModel
{
    private $id;
    private $firstName;
    private $lastName;

    private function __construct(int $id, string $firstName, string $lastName)
    {
        $this->guardPublicMethodsHaveReturnTypes();

        $this->id        = $id;
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
    }

    public static function fromState(array $state): UserReadModel
    {
        return new UserReadModel($state['id'], $state['firstname'], $state['lastname']);
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

    private function guardPublicMethodsHaveReturnTypes()
    {
        $reflectionClass = new ReflectionClass($this);
        $methods         = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        /** @var ReflectionMethod[] $methods */
        foreach ($methods as $method) {
            if ($method->getName() == '__construct') {
                continue;
            }

            $reflectionMethod = new ReflectionMethod(__CLASS__, $method->getName());
            if ($reflectionMethod->getReturnType() === null) {
                trigger_error(sprintf('All public methods in the %s require a return type. None found for %s', get_class($this), $method->getName()));
            }
        }
    }
}
