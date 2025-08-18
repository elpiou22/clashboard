<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Clan;

class ClanFixtures extends Fixture
{
    public const REF_PREFIX = 'clan.';
    public function load(ObjectManager $manager): void
    {
        $c1 = new Clan();
        $c1->setClanId('P990YPPV');
        $c1->setName('Apologize');
        $manager->persist($c1);
        $this->addReference(self::REF_PREFIX.'P990YPPV', $c1);

        $manager->flush();
    }
}
