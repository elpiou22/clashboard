<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;

class UserFixtures extends Fixture
{
    public const REF_PREFIX = 'user.';
    public function load(ObjectManager $manager): void
    {
        $u1 = new User();
        $u1->setPseudo('admin');
        $u1->setEmail('admin@admin.admin');
        $u1->setBirthdate(new \DateTime('2025-08-18 23:02:00'));
        $u1->setPassword('$2y$13$ebCa3EQjL4oxikCDaWSlK.WdOynZ.wg6b7njIOyWJW2UJuL0xZ4vi');
        $u1->setRoles(json_decode('["ROLE_USER"]', true) ?? []);
        $u1->setProfilePicture('d00428efa0bf27b9edd37eac32dfd2c1-68a3950abc7df490249986.jpg');
        $manager->persist($u1);
        $this->addReference(self::REF_PREFIX.'1', $u1);

        $u2 = new User();
        $u2->setPseudo('TheAxel78');
        $u2->setEmail('axel@gmail.com');
        $u2->setBirthdate(new \DateTime('2025-08-11 22:34:00'));
        $u2->setPassword('$2y$13$APpMG8T8QtVF2DUHQvKRluNxA3ubfHPS8yqSFWK18dDmbuUiSMb/m');
        $u2->setRoles(json_decode('["ROLE_USER"]', true) ?? []);
        $u2->setProfilePicture('dominic-toretto-68a38f6b26608084088939.webp');
        $manager->persist($u2);
        $this->addReference(self::REF_PREFIX.'2', $u2);

        $u3 = new User();
        $u3->setPseudo('Rokkass');
        $u3->setEmail('rokkass@gmail.com');
        $u3->setBirthdate(new \DateTime('2025-08-18 22:56:00'));
        $u3->setPassword('$2y$13$Sh2DIG2PJndZUDqgn.yB0OKHLMvraX0CiNEgwo/.PVBjhs.wIzto6');
        $u3->setRoles(json_decode('["ROLE_USER"]', true) ?? []);
        $u3->setProfilePicture('unknown-68a39396d7bd9001004961.png');
        $manager->persist($u3);
        $this->addReference(self::REF_PREFIX.'3', $u3);

        $manager->flush();
    }
}
