<?php


namespace App\Tests\Controller;


use App\Tests\BaseTests;

class BDDTest extends BaseTests
{
  function testConnexion():void
  {
    $client = $this->create_client();
    $crawler = $client->request('GET', '/');
    $count = $crawler->filter('.navbar .nav-right .nav-item:contains("Mon profil")')->count();

    if ($count > 0) {
      $crawler = $client->request('GET', '/login');
      $this->assertResponseIsSuccessful();
      $form = $crawler->filter('#login-form form')->form([
          'registration_form[email]'    => "admin@admin.admin",
          '_password' => "admin",
      ]);
      $client->submit($form);

      $this->assertResponseRedirects('/home');
      $client->followRedirect();

      $this->assertResponseIsSuccessful();
    } else {
      $this->assertTrue(true, "On est déjà connecté");
    }
  }






}