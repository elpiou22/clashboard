<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Post;
use App\Entity\Clan;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class PostFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $p2 = new Post();
        $p2->setText('Je ne suis pas d\'accord. Le village d\'en face était rempli de trous !!! Même un débutant aurait pu le détruire à 100%');
        $p2->setAuthor(2);
        $p2->setDate(new \DateTime('2025-08-18 22:55:43'));
        $p2->setUpvote(1);
        $p2->setDownvote(0);
        $p2->setVoteWeight(1);
        $p2->setReplyOf(0);
        $p2->setNbReplies(1);
        $p2->setCwlId('2501');
        $p2->setPlayerMapPosition(1);
        $p2->setDay(1);
        $p2->setClan($this->getReference(ClanFixtures::REF_PREFIX.'P990YPPV', \App\Entity\Clan::class));
        $manager->persist($p2);

        $p3 = new Post();
        $p3->setText('Réel, même moi je le détruit alors que tout le monde sait que je suis nul');
        $p3->setAuthor(3);
        $p3->setDate(new \DateTime('2025-08-18 22:58:43'));
        $p3->setUpvote(0);
        $p3->setDownvote(0);
        $p3->setVoteWeight(0);
        $p3->setReplyOf(1);
        $p3->setNbReplies(1);
        $p3->setCwlId('2501');
        $p3->setPlayerMapPosition(1);
        $p3->setDay(1);
        $p3->setClan($this->getReference(ClanFixtures::REF_PREFIX.'P990YPPV', \App\Entity\Clan::class));
        $manager->persist($p3);

        $p4 = new Post();
        $p4->setText('Le resultat n\'est pas bon');
        $p4->setAuthor(3);
        $p4->setDate(new \DateTime('2025-08-18 23:01:00'));
        $p4->setUpvote(0);
        $p4->setDownvote(0);
        $p4->setVoteWeight(0);
        $p4->setReplyOf(0);
        $p4->setNbReplies(0);
        $p4->setCwlId('2501');
        $p4->setPlayerMapPosition(1);
        $p4->setDay(1);
        $p4->setClan($this->getReference(ClanFixtures::REF_PREFIX.'P990YPPV', \App\Entity\Clan::class));
        $manager->persist($p4);

        $p5 = new Post();
        $p5->setText('mdr');
        $p5->setAuthor(2);
        $p5->setDate(new \DateTime('2025-08-18 23:01:41'));
        $p5->setUpvote(0);
        $p5->setDownvote(0);
        $p5->setVoteWeight(0);
        $p5->setReplyOf(2);
        $p5->setNbReplies(0);
        $p5->setCwlId('2501');
        $p5->setPlayerMapPosition(1);
        $p5->setDay(1);
        $p5->setClan($this->getReference(ClanFixtures::REF_PREFIX.'P990YPPV', \App\Entity\Clan::class));
        $manager->persist($p5);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [ClanFixtures::class];
    }
}
