<?php
namespace Drupal\Tests\authorized_digital_sellers\Kernel\Controller;

use Drupal\authorized_digital_sellers\AuthorizedDigitalSellersProviderTrait;
use Drupal\authorized_digital_sellers\Controller\AuthorizedDigitalSellersController;
use Drupal\KernelTests\KernelTestBase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class AuthorizedDigitalSellersControllerKernelTest extends KernelTestBase {

  use AuthorizedDigitalSellersProviderTrait;

  public static $modules = array('authorized_digital_sellers');

  private $controller;

  public function setup() {
    parent::setup();

    $this->installConfig(['authorized_digital_sellers']);
    $this->controller = new AuthorizedDigitalSellersController();

    //\Drupal::cache()->invalidateAll();
  }

  /**
   * @dataProvider adstxtProvider
   */
  public function testadstxt($variables) {
    //Mock responses from guzzle
    $handler = HandlerStack::create(
      new MockHandler(
        [
          new Response(
            $variables["response"]["status"],
            $variables["response"]["headers"],
            $variables["response"]["body"]
          )
        ]
      )
    );
    $this->controller->setHttpClient(new Client(["handler" => $handler]));

    //Set the configurations
    $this->setConfigurations($variables["config"]);

    /*DEBUG*/ //echo "<pre>";print_r($this->controller->adstxt());echo "</pre>";//exit;
    $response = $this->controller->adstxt();//echo "<pre>";print_r($response->headers);echo "</pre>";

    //Assertions
    $this->assertInstanceOf("Symfony\Component\HttpFoundation\Response", $response);
    $this->assertEquals($variables["assert"]["adstxt"], $response->getContent());
    $this->assertEquals(200, $response->getStatusCode());
    if (isset($variables["assert"]["max-age"])) {
      $this->assertTrue($response->headers->hasCacheControlDirective('max-age'));
      $this->assertEquals($variables["assert"]["max-age"], $response->getMaxAge());
    } else {
      $this->assertFalse($response->headers->hasCacheControlDirective('max-age'));
    }
    if (isset($variables["assert"]["must-revalidate"])) {
      $this->assertTrue($response->headers->hasCacheControlDirective('must-revalidate'));
      $this->assertTrue($response->headers->getCacheControlDirective('must-revalidate'));
    } else {
      $this->assertFalse($response->headers->hasCacheControlDirective('must-revalidate'));
    }
    if (isset($variables["assert"]["no-store"])) {
      $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));
      $this->assertTrue($response->headers->getCacheControlDirective('no-store'));
    } else {
      $this->assertFalse($response->headers->hasCacheControlDirective('no-store'));
    }
  }

  /**
   * @dataProvider adstxtExceptionProvider
   */
  public function testadstxtException($variables) {
    //Expecting an exception
    $this->expectException("Exception");

    //Mock responses from guzzle
    $handler = HandlerStack::create(
      new MockHandler(
        [
          new Response(
            $variables["response"]["status"],
            $variables["response"]["headers"],
            $variables["response"]["body"]
          )
        ]
      )
    );
    $this->controller->setHttpClient(new Client(["handler" => $handler]));

    //Set the configurations
    $this->setConfigurations($variables["config"]);

    /*DEBUG*/ //echo "<pre>";print_r($this->controller->adstxt());echo "</pre>";//exit;
    $response = $this->controller->adstxt();
  }

}
