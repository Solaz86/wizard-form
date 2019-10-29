<?php

namespace Drupal\wizard_form\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wizard_form\Form\WizardBase\WizardFormBase;

/**
 * Class WizardCeroForm.
 */
class WizardCeroForm extends WizardFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'cerostep_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $step = ($form_state->get('step') ?: 1);

    $form['#prefix'] = '<div id="wizard-user-form-wrapper">';
    $form['#suffix'] = '</div>';
    $form['message'] = [
      '#markup' => '<div id="messages-wrapper"></div>',
    ];

    $submit_prev = [
      '#type' => 'submit',
      '#value' => $this->t('Previous'),
      '#submit' => ['::prevStep'],
      '#ajax' => [
        'callback' => [$this, 'stepCallback'],
        'wrapper' => 'wizard-user-form-wrapper',
      ],
    ];

    $submit_next = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#submit' => ['::nextStep'],
      '#ajax' => [
        'callback' => [$this, 'stepCallback'],
        'wrapper' => 'wizard-user-form-wrapper',
      ],
    ];

    switch ($step) {
      // Step 1
      case 1 :
        $form += $this->buildStepOneForm($form, $form_state);
        $form['actions']['step_next'] = $submit_next;
        break;

      // Step 2
      case 2 :
        $form += $this->buildStepTwoForm($form, $form_state);
        $form['actions']['step_prev'] = $submit_prev;
        $form['actions']['step_next'] = $submit_next;
        break;

      // Step 3
      case 3 :
        $form += $this->buildStepThreeForm($form, $form_state);
        $form['actions']['step_prev'] = $submit_prev;
        $form['actions']['submit'] = [
          '#type' => 'submit',
          '#value' => $this->t('Finish'),
          '#button_type' => 'primary',
        ];
        break;
    }

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
