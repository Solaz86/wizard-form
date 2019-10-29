<?php

namespace Drupal\wizard_form\Form;

use Drupal\Core\Form\FormBase;
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

    $step = ($this->tempStore->get('step') ?: 1);


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
      $form['actions']['submit']['#attributes']['data-twig-suggestion'] = 'contact_submit';


//      $message = $this->t('Do you want to create the user with the following data?. Click finish to confirm.');
//      $message .= "<br><b>First name: </b>{$this->store->get('first_name')}";
//      $message .= "<br><b>Last name: </b>{$this->store->get('last_name')}";
//      $message .= "<br><b>Gender: </b>{$this->store->get('gender')}";
//      $message .= "<br><b>Date of birth: </b>{$this->store->get('birthdate')}";
//      $message .= "<br><b>City: </b>{$this->store->get('city')}";
//      $message .= "<br><b>Phone number: </b>{$this->store->get('phone_number')}";
//      $message .= "<br><b>Address: </b>{$this->store->get('address')}<br>";
//
//      $form['message'] = [
//        '#markup' => $message,
//      ];
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
    $step === 1 ? $this->betwinstep($form, $form_state, $step) : $this->nextStepTwoToThree($form, $form_state, $step);
  }

  public function prevStep(array &$form, FormStateInterface $form_state) {
    $step = ($form_state->get('step') ?: 1);
    $step === 2 ? $this->betwinstep($form, $form_state, $step) : $this->nextStepTwoToThree($form, $form_state, $step);


    if ($step === 2 ) {
      $this->prevStepTwoToOne($form, $form_state);
    } elseif ($step === 3) {
        $this->prevStepThreeToTwo($form, $form_state);
    }
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param int $step
   */
  public function betwinstep(array &$form, FormStateInterface $form_state, $step) {
    $this->saveProcessForm($form, $form_state);
    $form_state->set('step', $step);
    $form_state->setRebuild();
  }


}
