<?php
namespace Drupal\authorized_digital_sellers\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\Response;

class AuthorizedDigitalSellersController extends ControllerBase {
  public function adstxt() {
    $config = $this->config("authorized_digital_sellers.settings");
    /*DEBUG*/ //echo "<pre>";print_r($config->get("radio_external_self"));echo "</pre>";exit;

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
        /*DEBUG*/ //echo "<pre>";print_r($adstxtCache);echo "</pre>";exit;

        if ($adstxtCache == false) {
          //No cache hit. Get the ADS text
          try {
            $adstxt = $this->getExternalADSFile($config->get("external_ads_file"));
            /*DEBUG*/ //echo "<pre>";print_r($adstxt);echo "</pre>";exit;
          } catch (\Exception $e) {
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
    return $response;
  }

  /**
   * Get the external ads.txt text
   */
  private function getExternalADSFile($sourceUrl = null) {
    if ($sourceUrl == null) {
      throw new \Exception("External ads.txt url not provided");
    }

    //Get the ADS text from external source
    $client = new Client();
    //Async Init: external.ads
    $externaladsPromise = $client->getAsync($sourceUrl);

    //Async Fulfiled: external.ads
    return $externaladsPromise->wait()->getBody()->getContents();
  }
}
