<?php
namespace Drupal\authorized_digital_sellers\Form;

use Cake\I18n\Time;
use Cake\Validation\Validator;
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

    $form["external_ads_file_refresh_rate"] = [
      "#type" => "textfield",
      "#title" => $this->t("External ADS file refresh rate"),
      "#description" => $this->t("How long should the ads.txt renew from the external source?"),
      "#required" => false,
      "#placeholder" => "eg. +5 minutes or +1 day",
      "#default_value" => $config->get("external_ads_file_refresh_rate"),
    ];

    $form["external_ads_file_fallback_to_self"] = [
      "#type" => "checkbox",
      "#title" => $this->t("Fallback to Self-Managed"),
      "#description" => $this->t("If the external ads.txt file cannot be retrieved and there is no cached version, should the module fallback to Self-Managed text version?"),
      "#required" => false,
      "#placeholder" => "eg. +5 minutes or +1 day",
      "#default_value" => $config->get("external_ads_file_fallback_to_self"),
    ];

    $form["self_managed_text"] = [
      "#type" => "textarea",
      "#title" => $this->t("Self Managed ADS text file"),
      "#description" => $this->t("Enter the ads.txt information, if you selected Self-Managed."),
      "#required" => false,
      "#default_value" => $config->get("self_managed_text"),
    ];

    $form["http_cache_control"] = [
      "#type" => "textfield",
      "#title" => $this->t("HTTP Cache Control"),
      "#description" => $this->t("Enter the duration of cache-control for http header. Leave blank for no-store."),
      "#required" => false,
      "#placeholder" => "eg. +5 minutes or +1 day",
      "#default_value" => $config->get("http_cache_control"),
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
    $validator = new Validator();
    $validator
      ->requirePresence("radio_external_self", "External or Self-Managed value must be present")
      ->notEmpty("radio_external_self", "External or Self-Managed value must be present")
      ->add("radio_external_self", [
        "inList" => [
          "rule" => ["inList", ["self", "external"]],
          "message" => "External File or Self-Managed value must be valid",
        ],
      ])

      ->add("external_ads_file", [
        "url" => [
          "rule" => "url",
          "message" => "External File URL must be valid",
        ],
      ])

      ->add("external_ads_file_refresh_rate", [
        "strtotime" => [
          "rule" => [$this, "validationStrtotime"],
          "message" => "External File URL refresh rate value must be valid",
        ],
        "isFuture" => [
          "rule" => [$this, "validationIsFuture"],
          "message" => "External File URL refresh rate value must be set in the future",
        ],
      ])

      ->add("external_ads_file_fallback_to_self", [
        "boolean" => [
          "rule" => "boolean",
          "message" => "External File fallback to self value must be valid",
        ],
      ])

      ->add("http_cache_control", [
        "strtotime" => [
          "rule" => [$this, "validationStrtotime"],
          "message" => "HTTP Cache Control value must be valid",
        ],
        "isFuture" => [
          "rule" => [$this, "validationIsFuture"],
          "message" => "External File URL refresh rate value must be set in the future",
        ],
      ])
    ;
    $errors = $validator->errors($_POST);
    foreach ($errors as $field => $messages) {
      $errorMessages = "";
      foreach ($messages as $message) {
        $errorMessages .= $message . " | ";
      }
      $errorMessages = substr($errorMessages, 0, strlen($errorMessages) - 3);
      $form_state->setErrorByName($field, $this->t($errorMessages));
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $config = \Drupal::service('config.factory')->getEditable("authorized_digital_sellers.settings");

    $config->set("radio_external_self", $form_state->getValue("radio_external_self"));
    $config->set("external_ads_file", $form_state->getValue("external_ads_file"));
    $config->set("external_ads_file_refresh_rate", $form_state->getValue("external_ads_file_refresh_rate"));
    $config->set("external_ads_file_fallback_to_self", $form_state->getValue("external_ads_file_fallback_to_self"));
    $config->set("self_managed_text", $form_state->getValue("self_managed_text"));
    $config->set("http_cache_control", $form_state->getValue("http_cache_control"));

    //Save Configuration
    $config->save();
  }

  protected function getEditableConfigNames() {
    return [
      "authorized_digital_sellers.settings",
    ];
  }

  /**
   * Validate if the value is valid English Textual Date
   * Using strtotime()
   */
  public function validationStrtotime($check, array $context) {
    /*DEBUG*/ //echo "<pre>";print_r($context);echo "</pre>";exit;

    //If there is an exception thrown, this is not a valid date textual value
    try {
      $time = strtotime($check);
    } catch (\Exception $e) {
      return false;
    }

    //If the value returns is false, there is an error
    if ($time === false) {
      return false;
    }
    return true;
  }

  /**
   * Validate if the time is set in the future
   */
  public function validationIsFuture($check, array $context) {
    return (new Time($check))->isFuture();
  }
}
