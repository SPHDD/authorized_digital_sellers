<?php
namespace Drupal\authorized_digital_sellers;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;

trait AuthorizedDigitalSellersProviderTrait {

  private $sourceUrl = "https://httpbin.org/get";

  public function getExternalADSFileProvider() {
    return [
      [
        "variables" => [
          "sourceUrl" => $this->sourceUrl,
          "response" => [
            "status" => 200,
            "headers" => [],
            "body" => "adstxt",
          ],
        ],
      ],
    ];
  }

  public function getExternalADSFileExceptionProvider() {
    return [
      [
        "variables" => [
          "sourceUrl" => $this->sourceUrl,
          "response" => [
            "status" => 404,
            "headers" => [],
            "body" => "adstxt",
          ],
        ],
      ],
      [
        "variables" => [
          "sourceUrl" => $this->sourceUrl,
          "response" => [
            "status" => 500,
            "headers" => [],
            "body" => "adstxt",
          ],
        ],
      ],
      [
        "variables" => [
          "sourceUrl" => "not a url at all",
          "response" => [
            "status" => 404,
            "headers" => [],
            "body" => "adstxt",
          ],
        ],
      ],
    ];
  }

  public function adstxtProvider() {
    return [
      //Normal Expected Success from external source
      [
        "variables" => [
          "config" => [
            "radio_external_self" => "external",
            "external_ads_file" => $this->sourceUrl,
            "external_ads_file_refresh_rate" => "+5 minutes",
            "external_ads_file_fallback_to_self" => true,
            "self_managed_text" => "adstxt Fallback",
            "http_cache_control" => " +5 minutes",
          ],
          "response" => [
            "status" => 200,
            "headers" => [],
            "body" => "adstxt",
          ],
          "assert" => [
            "adstxt" => "adstxt",
            "max-age" => 300,
          ],
        ],
      ],
      //Fallback to Self Managed
      [
        "variables" => [
          "config" => [
            "radio_external_self" => "external",
            "external_ads_file" => $this->sourceUrl,
            "external_ads_file_refresh_rate" => "+5 minutes",
            "external_ads_file_fallback_to_self" => true,
            "self_managed_text" => "adstxt Fallback",
            "http_cache_control" => " +5 minutes",
          ],
          "response" => [
            "status" => 404,
            "headers" => [],
            "body" => "adstxt",
          ],
          "assert" => [
            "adstxt" => "adstxt Fallback",
            "max-age" => 300,
          ],
        ],
      ],
      //Self Managed
      [
        "variables" => [
          "config" => [
            "radio_external_self" => "self",
            "external_ads_file" => $this->sourceUrl,
            "external_ads_file_refresh_rate" => "+5 minutes",
            "external_ads_file_fallback_to_self" => true,
            "self_managed_text" => "adstxt Fallback",
            "http_cache_control" => " +5 minutes",
          ],
          "response" => [
            "status" => 200,
            "headers" => [],
            "body" => "adstxt",
          ],
          "assert" => [
            "adstxt" => "adstxt Fallback",
            "max-age" => 300,
          ],
        ],
      ],
      //Normal Expected Success from external source with no cache-control
      [
        "variables" => [
          "config" => [
            "radio_external_self" => "external",
            "external_ads_file" => $this->sourceUrl,
            "external_ads_file_refresh_rate" => "+5 minutes",
            "external_ads_file_fallback_to_self" => true,
            "self_managed_text" => "adstxt Fallback",
            "http_cache_control" => "",
          ],
          "response" => [
            "status" => 200,
            "headers" => [],
            "body" => "adstxt",
          ],
          "assert" => [
            "adstxt" => "adstxt",
            "must-revalidate" => true,
            "no-store" => true,
          ],
        ],
      ],
    ];
  }

  public function adstxtExceptionProvider() {
    return [
      //No source and fallback
      [
        "variables" => [
          "config" => [
            "radio_external_self" => "external",
            "external_ads_file" => "not a url",
            "external_ads_file_refresh_rate" => "+5 minutes",
            "external_ads_file_fallback_to_self" => false,
            "self_managed_text" => "adstxt Fallback",
            "http_cache_control" => " +5 minutes",
          ],
          "response" => [
            "status" => 404,
            "headers" => [],
            "body" => "",
          ],
        ],
      ],
    ];
  }

  /**
   * Set Drupal Configurations
   */
  protected function setConfigurations($configurations) {
    //Set the configurations
    $config = \Drupal::service('config.factory')->getEditable("authorized_digital_sellers.settings");

    foreach ($configurations as $name => $value) {
      $config->set($name, $value);
    }

    $config->save();
  }

}
