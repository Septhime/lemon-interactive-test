<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $userList = [];
        $faker = Factory::create();
        for ($i = 0; $i < 3; ++$i) {
            $user = new User();
            $user->setFirstName($faker->firstName());
            $user->setLastName($faker->lastName());
            $user->setEmail($faker->email());
            $user->setPassword(password_hash($faker->password(8), PASSWORD_BCRYPT));
            $manager->persist($user);
            $userList[] = $user;
        }
        for ($i = 0; $i < 10; ++$i) {
            $event = new Event();
            $event->setTitle($faker->text(20));
            $event->setDescription($faker->paragraph());
            if ($i >= 5) {
                $event->setBegin($faker->dateTimeBetween('now', '+1 year'));
                $event->setEndDate($faker->dateTimeBetween($event->getBegin(), '+1 year'));
            } else {
                $event->setBegin($faker->dateTimeBetween('-1 year'));
                $event->setEndDate($faker->dateTimeBetween($event->getBegin()));
            }
            $event->setLocation($faker->address());
            $event->setOwner($userList[array_rand($userList)]);
            $manager->persist($event);
        }

        $manager->flush();
    }
}
