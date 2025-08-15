<?php

namespace App\Tests\Controller;

use App\Tests\BaseTests;

class AccountControllerTest extends BaseTests
{

  public function testSigninPageIsSuccessful(): void
  {
    $client = static::createClient();
    $crawler = $client->request('GET', '/signin');

    $this->testWebsiteAvailable();
    $this->testElementExists('form');
    $this->testElementContainsText('.title_text', 'Identifiez-vous:');
  }

  /*
  public function testSendResetEmailPageIsSuccessful(): void
  {
    $client = static::createClient();
    $crawler = $client->request('GET', '/send_reset_email');

    $this->isWebsiteAvailable();
    $this->assertElementExists('form');
    $this->assertElementContainsText('h1, h2', 'Réinitialiser le mot de passe');
  }
  */


  public function testProfilePageRequiresAuthentication(): void
  {
    $client = static::createClient();
    $client->request('GET', '/profile');
    $this->assertResponseRedirects();
  }

}
