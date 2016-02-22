<?php

final class User
{
    private $id;
    private $firstName;
    private $lastName;

    public function __construct(int $id, string $firstName, string $lastName)
    {
        $this->id        = $id;
        $this->firstName = $firstName;
        $this->lastName  = $lastName;
    }

    /**
     * @return void
     */
    public function setFirstName(string $firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return void
     */
    public function setLastName(string $lastName)
    {
        $this->lastName = $lastName;
    }
}
