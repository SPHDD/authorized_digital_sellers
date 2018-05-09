<?php
namespace Drupal\authorized_digital_sellers\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthorizedDigitalSellersController extends ControllerBase {

  private $client;

  /**
   * Initialising constructor
   */
  public function __construct(ClientInterface $client = null) {
    if (empty($client)) {
      $this->setHttpClient(new Client());
    } else {
      $this->setHttpClient($client);
    }
  }

  /**
   * Renders the ads.txt content
   */
  public function adstxt() {
    $config = $this->config("authorized_digital_sellers.settings");
    /*DEBUG*/ //echo "<pre>";print_r($config->get("radio_external_self"));echo "</pre>";//exit;

    switch ($config->get("radio_external_self")) {
      case "self":
      default:
        //Return the ADS text
        $adstxt = $config->get("self_managed_text");
        break;
      case "external":
        /*DEBUG*/ //\Drupal::cache()->invalidate("AuthorizedDigitalSellers.text");
        //Get from cache the ADS text
        $adstxtCache = \Drupal::cache()->get("AuthorizedDigitalSellers.text");
        /*DEBUG*/ //echo "<pre>";print_r($adstxtCache);echo "</pre>";//exit;

        if ($adstxtCache == false) {
          //No cache hit. Get the ADS text
          try {
            $adstxt = $this->getExternalADSFile($config->get("external_ads_file"));
            /*DEBUG*/ //echo "<pre>";print_r($adstxt);echo "</pre>";//exit;
          } catch (\Exception $e) {
            /*DEBUG*/ //echo "<pre>";print_r($e->getMessage());echo "</pre>";//exit;
            if ($config->get("external_ads_file_fallback_to_self")) {
              $adstxt = $config->get("self_managed_text");
            } else {
              //Throw the exception to the viewer
              throw $e;
            }
          }

          //Set the cache
          \Drupal::cache()->set("AuthorizedDigitalSellers.text", $adstxt);
        } else {
          //Cache Hit!
          //Check for expiry
          $now = (new \DateTime)->getTimestamp();
          /*DEBUG*/ //echo "<pre>";print_r($now);echo "</pre>";exit;
          if (empty($config->get("external_ads_file_refresh_rate"))) {
            $cacheMaxAge = 0;
          } else {
            $cacheMaxAge = strtotime($config->get("external_ads_file_refresh_rate")) - $now;
            if ($cacheMaxAge < 0) {
              $cacheMaxAge = 0;
            }
          }
          /*DEBUG*/ //echo "<pre>";print_r($cacheMaxAge);echo "</pre>";exit;
          if ($now - $adstxtCache->created > $cacheMaxAge) {
            try {
              //Refresh cache
              $adstxt = $this->getExternalADSFile($config->get("external_ads_file"));

              //Set the cache
              \Drupal::cache()->set("AuthorizedDigitalSellers.text", $adstxt);
            } catch (\Exception $e) {
              //There was an error with the external ads.txt source.
              //Use back the cache instead
              $adstxt = $adstxtCache->data;
            }
          } else {
            //Do not refresh cache
            $adstxt = $adstxtCache->data;
          }
        }
        break;
    }

    $response = new Response($adstxt);
    $response->headers->set("Content-Type", "text/plain");

    //If http cache is set, set the cache
    //else no-store
    if (!empty($config->get("http_cache_control"))) {
      $httpCache = strtotime($config->get("http_cache_control")) - (new \DateTime)->getTimestamp();
      if ($httpCache < 1) {
        $httpCache = "no-store";
      } else {
        $httpCache = "max-age=" . $httpCache;
      }
      $response->headers->set("Cache-Control", $httpCache);
    } else {
      $response->headers->set("Cache-Control", "must-revalidate,no-store");
    }
    return $response;
  }

  /**
   * Get the external ads.txt text
   */
  public function getExternalADSFile($sourceUrl = null) {
    if ($sourceUrl == null) {
      throw new \Exception("External ads.txt url not provided");
    }

    //Async Init: external.ads
    $externaladsPromise = $this->client->requestAsync("GET", $sourceUrl);
    /*DEBUG*/ //echo "<pre>";print_r($externaladsPromise->wait()->getBody()->getContents());echo "</pre>";//exit;

    //Async Fulfiled: external.ads
    return $externaladsPromise->wait()->getBody()->getContents();
  }

  /**
   * Get the HTTP Client
   */
  public function getHttpClient() {
    return $this->client;
  }

  /**
   * Set the HTTP Client
   */
  public function setHttpClient(ClientInterface $client = null) {
    if (empty($client)) {
      throw new \Exception("HTTP Client must be provided");
    }

    $this->client = $client;
  }
}
