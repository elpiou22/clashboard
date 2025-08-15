<?php

namespace App\Tests\Controller;

use App\Tests\BaseTests;

class CwlControllerTest extends BaseTests
{

  public function testCwlHomePageIsSuccessful(): void
  {
    $client = static::createClient();
    $crawler = $client->request('GET', '/cwl');

    $this->testWebsiteAvailable();
    $this->testElementContainsText('span', 'CWL bonus tool');
  }

  /*
  public function testBonusDataPagePost(): void
  {
    $client = static::createClient();
    $crawler = $client->request('POST', '/bonusdata', [
        'clan_id' => 'P990YPPV',
        'stars' => ["", "0", "1", "2", "2", "3"],
        'logical_operator' => ["", "", "", "AND", "AND", ""],
        'comparison_operator' => ["", "", "", "=", "<", ""],
        'result' => ["-1", "-1", "-1", "0", "1", "1"]
    ]);


    $this->testWebsiteAvailable();
  }
  */
}
