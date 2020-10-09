<?php

namespace App\DataFixtures;

use App\Entity\Communes;
use App\Entity\Media;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create("Fr-fr");
        for ($i = 0; $i < 20; $i++) {
            $commune = new Communes();

            $faker->numberBetween(1000, 80000);
            $commune
                ->setNom($faker->state)
                ->setCode($faker->postcode)
                ->setCodeDepartement($faker->numberBetween(1000, 80000))
                ->setCodePostal($faker->numberBetween(24000, 880000))
                ->setCodeRegion($faker->numberBetween(25000, 500000));
            if (mt_rand(0, 1) === 1) {
                $media = new Media();
                $media->setIdCommune($commune)->setImage($faker->imageUrl(640, 488, 'city'));
                $manager->persist($media);

            }
            $manager->persist($commune);
        }

        $manager->flush();
    }
}
