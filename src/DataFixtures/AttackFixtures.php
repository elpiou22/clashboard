<?php

namespace App\DataFixtures;

use App\Entity\Attack;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AttackFixtures extends Fixture
{

  public function load(ObjectManager $manager): void
  {
    /*
    $attack = new Attack();
    $attack->setPseudo('TestPlayer');
    $attack->setDate('2501');
    $attack->setDay(1);
    $attack->setResult(3);
    $attack->setClanID('12345');
    $attack->setMapPosition(1);
    $attack->setTag('#TEST');
    $attack->setAttackerTH(13);
    $attack->setDefenderTH(12);
    $attack->setPercentage(100);
    $attack->setAttackStars(3);

    $manager->persist($attack);
    $manager->flush();
    */
  }

}
