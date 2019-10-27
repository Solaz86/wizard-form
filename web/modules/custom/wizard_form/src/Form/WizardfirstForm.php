<?php

namespace Drupal\wizard_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wizard_form\Form\WizardBase\WizardFormBase;

/**
 * Class WizardfirstForm.
 */
class WizardfirstForm extends WizardFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'firststep_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {


    $form['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First name'),
      '#maxlength' => 64,
      '#size' => 64,
      '#required' => TRUE,
      '#default_value' => $this->store->get('first_name'),
    ];

    $form['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last name'),
      '#maxlength' => 64,
      '#size' => 64,
      '#required' => TRUE,
      '#default_value' => $this->store->get('last_name'),
    ];

    $form['gender'] = [
      '#type' => 'select',
      '#title' => $this->t('Gender'),
      '#options' => [
        'M' => 'Male',
        'F' => 'Female',
        'N' => 'Neutral',
      ],
      '#required' => TRUE,
      '#default_value' => $this->store->get('gender'),
    ];

    $form['birthdate'] = [
      '#type' => 'date',
      '#title' => $this->t('Date of birth'),
      '#format' => 'm/d/Y',
      '#description' => $this->t('i.e. 09/06/2016'),
      '#required' => TRUE,
      '#default_value' => $this->store->get('birthdate'),
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
