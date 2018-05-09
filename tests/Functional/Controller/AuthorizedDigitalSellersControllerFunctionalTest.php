<?php
namespace Drupal\Tests\authorized_digital_sellers\Functional\Controller;

use Drupal\authorized_digital_sellers\AuthorizedDigitalSellersProviderTrait;
use Drupal\Tests\BrowserTestBase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class AuthorizedDigitalSellersControllerFunctionalTest extends BrowserTestBase {

  use AuthorizedDigitalSellersProviderTrait;

  public static $modules = [
    'authorized_digital_sellers',
    "node"
  ];

  public function setup() {
    parent::setup();
  }

  /**
   * @dataProvider adstxtProvider
   */
  public function testadstxt($variables) {
    $this->setConfigurations($variables["config"]);

    $content = $this->drupalGet('/ads.txt');
    /*DEBUG*/ //print_r($content);

    $this->assertTrue(is_string($content));
    $this->assertSession()->statusCodeEquals(200);
    if (isset($variables["assert"]["max-age"])) {
      $this->assertSession()->responseHeaderContains("cache-control", "max-age=" . $variables["assert"]["max-age"]);
    }
    if (isset($variables["assert"]["must-revalidate"])) {
      $this->assertSession()->responseHeaderContains("cache-control", "must-revalidate");
    }
    if (isset($variables["assert"]["no-store"])) {
      $this->assertSession()->responseHeaderContains("cache-control", "no-store");
    }
  }

  /**
   * @dataProvider adstxtExceptionProvider
   */
  public function testadstxtError($variables) {
    $this->setConfigurations($variables["config"]);

    $content = $this->drupalGet('/ads.txt');
    /*DEBUG*/ //print_r($content);

    $this->assertSession()->statusCodeEquals(500);
  }
}
