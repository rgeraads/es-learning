<?php

require_once __DIR__ . '/../bootstrap.php';

$user = new User(1, 'randy', 'Geraads');
$userRepository = UserRepository::mysql('127.0.0.1', 'vagrant', 'vagrant', 'users');
$userRepository->save($user);

echo $user->getFullName();

$user->setFirstName('Randy');

echo $user->getFullName();
