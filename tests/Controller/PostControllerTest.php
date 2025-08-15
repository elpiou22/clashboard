<?php

/*
namespace App\Tests\Controller;

use App\DataFixtures\AttackFixtures;
use App\DataFixtures\PostFixtures;
use App\Tests\BaseTests;

use Liip\TestFixturesBundle\Test\FixturesTrait;

class PostControllerTest extends BaseTests
{
  use FixturesTrait;

  public function testCreatePost(): void
  {
    // Charge les fixtures nécessaires (Attack + un post initial)
    $this->loadFixtures([AttackFixtures::class, PostFixtures::class]);

    $client = static::createClient();

    // Simule un referer avec clanId, cwlId, mapPosition, day
    $headers = [
        'HTTP_REFERER' => '/contest/12345/250101' // clanId=12345, cwlId=2501, mapPos=01, day=1
    ];

    $crawler = $client->request('POST', '/post_create', [
        'content' => 'Ceci est un post de test',
        'id' => 0 // pas une réponse
    ], [], $headers);

    $this->assertResponseRedirects('/contest/12345/250101');
  }

  public function testVoteUp(): void
  {
    $this->loadFixtures([PostFixtures::class]);

    $client = static::createClient();
    $postId = 1; // ID du post créé dans PostFixtures

    $client->request(
        'POST',
        '/vote',
        [],
        [],
        ['CONTENT_TYPE' => 'application/json'],
        json_encode(['post_id' => $postId, 'vote_type' => 'up'])
    );

    $this->assertResponseIsSuccessful();
    $data = json_decode($client->getResponse()->getContent(), true);

    $this->assertEquals($postId, $data['postId']);
    $this->assertArrayHasKey('vote_weight', $data);
  }

  public function testShowPost(): void
  {
    $this->loadFixtures([PostFixtures::class]);

    $client = static::createClient();
    $postId = 1; // ID du post créé dans PostFixtures

    $crawler = $client->request('GET', "/post/{$postId}");

    $this->assertResponseIsSuccessful();
    $this->assertSelectorExists('body'); // on peut affiner si on sait ce qu'il y a dans le template
  }
}

*/