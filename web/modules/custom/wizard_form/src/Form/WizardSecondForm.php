<?php

namespace Drupal\wizard_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wizard_form\Form\WizardBase\WizardFormBase;

/**
 * Class WizardSecondForm.
 */
class WizardSecondForm extends WizardFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'secondstep_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#maxlength' => 64,
      '#size' => 64,
      '#required' => TRUE,
      '#default_value' => $this->store->get('city'),
    ];

    $form['phone_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone number'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $this->store->get('phone_number'),
    ];

    $form['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $this->store->get('address'),
    ];

    $form['actions']['submit']['#value'] = $this->t('Next');
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      // @TODO: Validate fields.
    }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
      \Drupal::messenger()->addMessage($key . ': ' . ($key === 'text_format'?$value['value']:$value));
    }
  }

}
