<?php
namespace Drupal\Tests\authorized_digital_sellers\Functional\Controller;

use Drupal\Tests\BrowserTestBase;

class ConfigurationFormFunctionalTest extends BrowserTestBase {

  public static $modules = [
    'authorized_digital_sellers',
    "node"
  ];

  public function testconfigurationForm() {
    //First attempt should result in permissions failure
    $content = $this->drupalGet('/admin/config/services/authorized_digital_sellers');
    $this->assertSession()->statusCodeEquals(403);

    //Login
    $this->drupalLogin(
      $this->drupalCreateUser([
        "administer authorized_digital_sellers"
      ])
    );

    //Access Success
    $content = $this->drupalGet('/admin/config/services/authorized_digital_sellers');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->buttonExists('Save');//You better have the ability to save

    //Attempt to input correct values
    /*DEBUG*/ //print_r(get_class($this->getSession()));
    $this->getSession()->getPage()->fillField("radio_external_self", "self");
    $this->getSession()->getPage()->fillField("external_ads_file", "http://example.com/ads.txt");
    $this->getSession()->getPage()->fillField("external_ads_file_refresh_rate", "+5 minutes");
    $this->getSession()->getPage()->fillField("external_ads_file_fallback_to_self", true);
    $this->getSession()->getPage()->fillField("self_managed_text", "adstxt");
    $this->getSession()->getPage()->fillField("http_cache_control", "+5 minutes");
    $this->getSession()->getPage()->pressButton("Save");
    /*DEBUG*/ //print_r($this->getSession()->getPage()->getContent());
    $this->assertSession()->pageTextContains("The configuration options have been saved.");

    //Attempt to input wrong values
    $this->getSession()->getPage()->fillField("radio_external_self", "self");
    $this->getSession()->getPage()->fillField("external_ads_file", "http://localhost/ads.txt");
    $this->getSession()->getPage()->fillField("external_ads_file_refresh_rate", "+5 minutes");
    $this->getSession()->getPage()->fillField("external_ads_file_fallback_to_self", true);
    $this->getSession()->getPage()->fillField("self_managed_text", "adstxt");
    $this->getSession()->getPage()->fillField("http_cache_control", "+5 minutes");
    $this->getSession()->getPage()->pressButton("Save");
    /*DEBUG*/ //print_r($this->getSession()->getPage()->getContent());
    $this->assertSession()->pageTextContains(" External File URL must be valid");
  }

}
