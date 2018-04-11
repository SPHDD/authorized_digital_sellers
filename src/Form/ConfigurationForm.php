<?php
namespace Drupal\authorized_digital_sellers\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class ConfigurationForm extends FormBase {
  public function getFormId() {
    return "authorized_digital_sellers_configuration_form";
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $form["description"] = [
      "#type" => "item",
      "#markup" => $this->t("Authorized Digital Seller configuration form"),
    ];

    $form["external_self_radio"] = [
      "#type" => "radios",
      "#title" => $this->t("External File Management or Self-Managed"),
      "#options" => [
        "external" => $this->t("External File Management"),
        "self" => $this->t("Self-Managed"),
      ],
    ];

    $form["external_ads_file"] = [
      "#type" => "textfield",
      "#title" => $this->t("External ADS file"),
      "#description" => $this->t("Enter the http/https location of the ads.txt file to retrieve, if you selected External File Management."),
      "#required" => false,
      "#placeholder" => "https://example/ads.txt",
    ];

    $form["self_managed"] = [
      "#type" => "textarea",
      "#title" => $this->t("Self Managed ADS text file"),
      "#description" => $this->t("Enter the ads.txt information, if you selected Self-Managed."),
      "#required" => false,
    ];

    $form["actions"] = [
      "#type" => "actions",
      "submit" => [
        "#type" => "submit",
        "#value" => $this->t("Push this Button"),
      ],
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
  }
}