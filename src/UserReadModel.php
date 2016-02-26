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
        $this->guardPublicMethodsHaveReturnTypes();

        $this->id        = $id;
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
    }

    public function foo()
    {
        return 'bla';
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

    private function guardPublicMethodsHaveReturnTypes()
    {
        $reflectionClass = new ReflectionClass($this);
        $methods         = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

        /** @var ReflectionMethod[] $methods */
        foreach ($methods as $method) {
            if ($method->getName() == '__construct') {
                continue;
            }

            $returnValue = $this->getReturnValue($this->parseMethod($method));

            $reflectionMethod = new ReflectionMethod(__CLASS__, $method->getName());

            if ($returnValue !== self::NO_RETURN_VALUE && $reflectionMethod->getReturnType() === null) {
                trigger_error(sprintf('All public methods in the %s require a return type. Method "%s" returns %s', get_class($this), $method->getName(), $returnValue));
            }
        }
    }

    private function parseMethod(ReflectionMethod $method): array
    {
        $file = explode(PHP_EOL, file_get_contents(__FILE__));

        $reflectionMethod = new ReflectionMethod(__CLASS__, $method->getName());

        foreach ($file as $key => $row) {
            if ($key < $reflectionMethod->getStartLine() - 1) {
                unset($file[$key]);
                continue;
            }

            if ($key > $reflectionMethod->getEndLine() - 1) {
                unset($file[$key]);
                continue;
            }

            if (strpos($row, '    ') === 0) {
                $file[$key] = substr_replace($row, '', 0, 4);
            }
        }

        return $file;
    }

    private function getReturnValue(array $parsedMethod): string
    {
        foreach ($parsedMethod as $key => $row) {
            if ($pos = strpos(strtolower(ltrim($row)), 'return') !== false) {
                $returnValue = substr(ltrim($row), $pos + 6);

                return ltrim($this->stripSemicolon($returnValue));
            }
        }

        if (!isset($returnValue)) {
            return self::NO_RETURN_VALUE;
        }
    }

    private function stripSemicolon($returnValue): string
    {
        if (strpos($returnValue, ';', strlen($returnValue) - 1) !== false) {
            $returnValue = substr($returnValue, 0, strlen($returnValue) - 1);
        }

        return $returnValue;
    }
}
