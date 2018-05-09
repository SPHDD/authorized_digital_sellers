<?php
namespace Drupal\Tests\authorized_digital_sellers\Unit\Controller;

use Drupal\authorized_digital_sellers\AuthorizedDigitalSellersProviderTrait;
use Drupal\authorized_digital_sellers\Controller\AuthorizedDigitalSellersController;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

class AuthorizedDigitalSellersControllerUnitTest extends UnitTestCase {

  use AuthorizedDigitalSellersProviderTrait;

  private $controller;

  public function setup() {
    parent::setup();
    $this->controller = new AuthorizedDigitalSellersController();
  }

  /**
   * @dataProvider getExternalADSFileProvider
   */
  public function testgetExternalADSFile($variables) {
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
    /*DEBUG*/ //echo "<pre>";print_r($this->controller->getExternalADSFile($sourceUrl));echo "</pre>";exit;

    $this->assertEquals($variables["response"]["body"], $this->controller->getExternalADSFile($variables["sourceUrl"]));
  }

  /**
   * @dataProvider getExternalADSFileExceptionProvider
   */
  public function testgetExternalADSFileException($variables) {
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
    /*DEBUG*/ //echo "<pre>";print_r($this->controller->getExternalADSFile($sourceUrl));echo "</pre>";exit;

    $this->controller->getExternalADSFile($variables["sourceUrl"]);
  }

}
