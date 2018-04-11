<?php
namespace Drupal\authorized_digital_sellers\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class ConfigurationForm extends ConfigFormBase {
  public function getFormId() {
    return "authorized_digital_sellers_configuration_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config("authorized_digital_sellers.settings");
    $form["description"] = [
      "#type" => "item",
      "#markup" => $this->t("Authorized Digital Seller configuration form"),
    ];

    $form["radio_external_self"] = [
      "#type" => "radios",
      "#title" => $this->t("External File Management or Self-Managed"),
      "#options" => [
        "external" => $this->t("External File Management"),
        "self" => $this->t("Self-Managed"),
      ],
      "#default_value" => $config->get("radio_external_self"),
    ];

    $form["external_ads_file"] = [
      "#type" => "textfield",
      "#title" => $this->t("External ADS file"),
      "#description" => $this->t("Enter the http/https location of the ads.txt file to retrieve, if you selected External File Management."),
      "#required" => false,
      "#placeholder" => "https://example/ads.txt",
      "#default_value" => $config->get("external_ads_file"),
    ];

    $form["self_managed_text"] = [
      "#type" => "textarea",
      "#title" => $this->t("Self Managed ADS text file"),
      "#description" => $this->t("Enter the ads.txt information, if you selected Self-Managed."),
      "#required" => false,
      "#default_value" => $config->get("self_managed_text"),
    ];

    $form["actions"] = [
      "#type" => "actions",
      "submit" => [
        "#type" => "submit",
        "#value" => $this->t("Save"),
      ],
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = \Drupal::service('config.factory')->getEditable("authorized_digital_sellers.settings");

    $config->set("radio_external_self", $form_state->getValue("radio_external_self"));
    $config->set("external_ads_file", $form_state->getValue("external_ads_file"));
    $config->set("self_managed_text", $form_state->getValue("self_managed_text"));

    //Save Configuration
    $config->save();
  }

  protected function getEditableConfigNames() {
    return [
      "authorized_digital_sellers.settings",
    ];
  }
}