<?php

namespace Drupal\wizard_form\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\wizard_form\Form\Multistep\WizardFormBase;

/**
 * Class UserForm.
 */
class WizardUserForm extends WizardFormBase {

  /**
   * List of fields.
   *
   * @var array
   */
  protected $fields = [
    'first_name' => 'first_name',
    'last_name' => 'last_name',
    'gender' => 'gender',
    'birthdate' => 'birthdate',
    'city' => 'city',
    'phone_number' => 'phone_number',
    'address' => 'address',
  ];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wizard_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $step = ($form_state->get('step') ?: 1);

    $form['#prefix'] = '<div id="new-user-form-wrapper">';
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
        'wrapper' => 'new-user-form-wrapper',
      ],
    ];

    $submit_next = [
      '#type' => 'submit',
      '#value' => $this->t('Next'),
      '#submit' => ['::nextStep'],
      '#ajax' => [
        'callback' => [$this, 'stepCallback'],
        'wrapper' => 'new-user-form-wrapper',
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
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   * @throws \Drupal\Core\store\storeException
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->saveData();
    $this->deletestore($this->fields);
  }

  protected function saveData() {
    try {
      /** @var \Drupal\user\UserInterface $user */
      $user = $this->entityTypeManager->getStorage('user')->create();
      $username_fake = 'new-user_' . uniqid();
      $mail_fake = "{$username_fake}@new-user.fake";

      // Mandatory.
      $user->setPassword('password');
      $user->enforceIsNew();
      $user->setEmail($mail_fake);
      $user->setUsername($username_fake);

      foreach ($this->fields as $field) {
        if ($value = $this->store->get($field)) {
          $user->set("field_{$field}", $value);
        }
      }

      $user->save();

      $this->messenger->addMessage('The information has been saved successfully.');

    } catch (\Exception $e) {
      $this->messenger->addError('An error occurred while creating the user.');
    }
  }


  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function nextStep(array &$form, FormStateInterface $form_state) {
    $step = ($form_state->get('step') ?: 1);

    switch ($step) {
      // Step 1
      case 1 :
        $this->nextStepOneToTwo($form, $form_state);
        break;

      // Step 2
      case 2 :
        $this->nextStepTwoToThree($form, $form_state);
        break;
    }
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

  public function prevStep(array &$form, FormStateInterface $form_state) {
    $step = ($form_state->get('step') ?: 1);

    switch ($step) {
      // Step 2
      case 2 :
        $this->prevStepTwoToOne($form, $form_state);
        break;

      // Step 3
      case 3 :
        $this->prevStepThreeToTwo($form, $form_state);
        break;
    }
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  protected function saveProcessForm(array $form, FormStateInterface $form_state) {
    $elements = $form_state->getValues();

    foreach ($elements as $key => $element) {
      $this->saveTempStore($key, $form_state->getValue($key));
    }
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildStepOneForm(array $form, FormStateInterface $form_state) {
    $form['step'] = [
      '#type' => 'details',
      '#title' => $this->t('Step 1'),
      '#open' => TRUE,
    ];

    $form['step']['first_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('First name'),
      '#maxlength' => 64,
      '#size' => 64,
      '#required' => TRUE,
      '#default_value' => $this->tempStore->get('first_name'),
    ];

    $form['step']['last_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Last name'),
      '#maxlength' => 64,
      '#size' => 64,
      '#required' => TRUE,
      '#default_value' => $this->tempStore->get('last_name'),
    ];

    $form['step']['gender'] = [
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

    $form['step']['birthdate'] = [
      '#type' => 'date',
      '#title' => $this->t('Date of birth'),
      '#format' => 'm/d/Y',
      '#description' => $this->t('i.e. 09/06/2016'),
      '#required' => TRUE,
      '#default_value' => $this->tempStore->get('birthdate'),
    ];

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function nextStepOneToTwo(array &$form, FormStateInterface $form_state) {
    $this->saveProcessForm($form, $form_state);
    $form_state->set('step', 2);
    $form_state->setRebuild();
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function prevStepTwoToOne(array &$form, FormStateInterface $form_state) {
    $this->saveProcessForm($form, $form_state);
    $form_state->set('step', 1);
    $form_state->setRebuild();
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildStepTwoForm(array $form, FormStateInterface $form_state) {
    $form['step'] = [
      '#type' => 'details',
      '#title' => $this->t('Step 2'),
      '#open' => TRUE,
    ];

    $form['step']['city'] = [
      '#type' => 'textfield',
      '#title' => $this->t('City'),
      '#maxlength' => 64,
      '#size' => 64,
      '#required' => TRUE,
      '#default_value' => $this->tempStore->get('city'),
    ];

    $form['step']['phone_number'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Phone number'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $this->tempStore->get('phone_number'),
    ];

    $form['step']['address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Address'),
      '#maxlength' => 64,
      '#size' => 64,
      '#default_value' => $this->tempStore->get('address'),
    ];

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function nextStepTwoToThree(array &$form, FormStateInterface $form_state) {
    $this->saveProcessForm($form, $form_state);
    $form_state->set('step', 3);
    $form_state->setRebuild();
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function buildStepThreeForm(array $form, FormStateInterface $form_state) {
    $form['step'] = [
      '#type' => 'details',
      '#title' => $this->t('Step 3'),
      '#open' => TRUE,
    ];

    $message = $this->t('Do you want to create the user with the following data?. Click finish to confirm.');
    $message .= "<br><b>First name: </b>{$this->tempStore->get('first_name')}";
    $message .= "<br><b>Last name: </b>{$this->tempStore->get('last_name')}";
    $message .= "<br><b>Gender: </b>{$this->tempStore->get('gender')}";
    $message .= "<br><b>Date of birth: </b>{$this->tempStore->get('birthdate')}";
    $message .= "<br><b>City: </b>{$this->tempStore->get('city')}";
    $message .= "<br><b>Phone number: </b>{$this->tempStore->get('phone_number')}";
    $message .= "<br><b>Address: </b>{$this->tempStore->get('address')}<br>";

    $form['step']['message'] = [
      '#markup' => $message,
    ];

    return $form;
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function prevStepThreeToTwo(array &$form, FormStateInterface $form_state) {
    $form_state->set('step', 2);
    $form_state->setRebuild();
  }

}
