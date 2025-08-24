<?php

namespace App\Tests\Controller;

use App\Tests\BaseTests;

class HomeControllerTest extends BaseTests
{
  public function testHomePageIsSuccessful(): void
  {

    $this->create_client()->request('GET', '/home');
    $this->testWebsiteAvailable();
    $this->testElementContainsText('.title_container span', 'Simplifiez votre gestion de bonus de ligues !');
    $this->testElementExists('div.content_container div.img_container img[src$="cwl_example.png"]');
    }

  public function testLegalNoticePageIsSuccessful(): void
  {
    $this->create_client()->request('GET', '/legal-notice');
    $this->testWebsiteAvailable();
    $this->testElementContainsText('h1', 'Mentions légales');
  }
}
