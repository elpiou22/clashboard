<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BaseTests extends WebTestCase
{
  /**
   * Vérifie que la page web est accessible et renvoie un code HTTP 200.
   *
   * @return void
   */
  protected function testWebsiteAvailable(): void
  {
    $this->assertResponseIsSuccessful();
  }

  /**
   * Vérifie qu'un élément correspondant au sélecteur CSS existe dans la page.
   *
   * @param string $selector Sélecteur CSS de l'élément à vérifier
   * @return void
   */
  protected function testElementExists(string $selector): void
  {
    $this->assertSelectorExists($selector);
  }

  /**
   * Vérifie qu'un élément donné contient le texte attendu.
   *
   * @param string $selector Sélecteur CSS de l'élément à vérifier
   * @param string $expectedText Texte attendu à l'intérieur de l'élément
   * @return void
   */
  protected function testElementContainsText(string $selector, string $expectedText): void
  {
    $this->assertSelectorTextContains($selector, $expectedText);
  }

  protected function create_client(): \Symfony\Bundle\FrameworkBundle\KernelBrowser
  {
    return static::createClient(server: [
        'HTTP_HOST' => '192.168.1.17:8000',
    ]);
  }

}