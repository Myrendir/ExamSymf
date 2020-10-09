<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Faker;

class UserFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Faker\Factory::create('FR-fr');
        $user = new User();
        $user2 = new User();
        $user->setEmail('admin@admin.fr')
            ->setRoles(['ROLE_USER']);
        $user2->setEmail('test@test.fr')
            ->setRoles(['ROLE_SUPER_ADMIN']);
        $user2->setPassword($this->passwordEncoder->encodePassword(
            $user2,
            'myPassword2'
        ));

        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            'myPassword'
        ));
        $manager->persist($user2);
        $manager->persist($user);
        $manager->flush();
    }

}
