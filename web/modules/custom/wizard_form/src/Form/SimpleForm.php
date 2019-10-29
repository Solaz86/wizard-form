<?php

namespace Drupal\wizard_form\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wizard_form\Form\Multistep\WizardFormBase;

/**
 * Class SimpleForm.
 */
class SimpleForm extends WizardFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['#prefix'] = '<div id="new-user-form-wrapper">';
    $form['#suffix'] = '</div>';
    $form['message'] = [
      '#markup' => '<div id="messages-wrapper"></div>',
    ];

    $step = ($form_state->get('step') ?: 1);

    $previous = [
      '#type' => 'submit',
      '#value' => $this->t('Previous'),
      '#submit' => ['::prevStep'],
      '#ajax' => [
        'callback' => [$this, 'stepCallback'],
        'wrapper' => 'new-user-form-wrapper',
      ],
    ];

    $next = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#submit' => ['::nextStep'],
      '#ajax' => [
        'callback' => [$this, 'stepCallback'],
        'wrapper' => 'new-user-form-wrapper',
      ],
    ];


    if ($step === 1) {
      $form['first_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('First name'),
        '#maxlength' => 64,
        '#size' => 64,
        '#required' => TRUE,
        '#default_value' => $this->tempStore->get('first_name'),
      ];

      $form['last_name'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Last name'),
        '#maxlength' => 64,
        '#size' => 64,
        '#required' => TRUE,
        '#default_value' => $this->tempStore->get('last_name'),
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
        '#default_value' => $this->tempStore->get('gender'),
      ];

      $form['birthdate'] = [
        '#type' => 'date',
        '#title' => $this->t('Date of birth'),
        '#format' => 'm/d/Y',
        '#description' => $this->t('i.e. 09/06/2016'),
        '#required' => TRUE,
        '#default_value' => $this->tempStore->get('birthdate'),
      ];

      $form['actions']['step_next'] = $next;

    } elseif ($step === 2) {
      $form['city'] = [
        '#type' => 'textfield',
        '#title' => $this->t('City'),
        '#maxlength' => 64,
        '#size' => 64,
        '#required' => TRUE,
        '#default_value' => $this->tempStore->get('city'),
      ];

      $form['phone_number'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Phone number'),
        '#maxlength' => 64,
        '#size' => 64,
        '#default_value' => $this->tempStore->get('phone_number'),
      ];

      $form['address'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Address'),
        '#maxlength' => 64,
        '#size' => 64,
        '#default_value' => $this->tempStore->get('address'),
      ];


      $form['actions']['step_next'] = $previous;

      $form['actions']['step_next'] = $next;
    } elseif ($step === 3) {

      $form['#attributes']['class'][] = 'webform-client-form';

      $result = $this->getDataTheme();

      \Drupal::service('renderer')->render($result);

//      $form['actions']['submit']['#attributes']['data-twig-suggestion'] = 'contact_submit';

    }

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    $this->saveData();
    $this->deleteTempStore($this->fields);
  }


  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function nextStep(array &$form, FormStateInterface $form_state) {
    $step = ($form_state->get('step') ?: 1);
    $step === 1 ? $this->betwinstep($form, $form_state, 2) : $this->betwinstep($form, $form_state, 3);
  }

  public function prevStep(array &$form, FormStateInterface $form_state) {
    $step = ($form_state->get('step') ?: 1);
    $step === 2 ? $this->betwinstep($form, $form_state, 1) : $this->betwinstep($form, $form_state, 2);
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param int $step
   */
  public function betwinstep(array &$form, FormStateInterface $form_state, $step) {
    foreach ($form_state->getValues() as $key => $value) {
      $this->saveTempStore($key, $form_state->getValue($key));
    }
    $form_state->set('step', $step);
    $form_state->setRebuild();
  }


  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function stepCallback(array &$form, FormStateInterface $form_state) {
    $ajax_response = new AjaxResponse();
    $messages = $this->messenger->deleteAll();

    $ajax_response->addCommand(new ReplaceCommand('#new-user-form-wrapper', $form));

    if (!empty($messages)) {
      $messages = [
        '#theme' => 'status_messages',
        '#message_list' => $messages,
      ];
      $ajax_response->addCommand(new HtmlCommand('#messages-wrapper', $messages));
    }
    else {
      // Remove messages.
      $ajax_response->addCommand(new HtmlCommand('#messages-wrapper', ''));
    }

    return $ajax_response;
  }



}
