<?php

namespace Drupal\crud\Form;

use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Class RegisterForm.
 */
class RegisterForm extends FormBase {

  /**
   * @var Connection
   */
  protected $connection;

  /**
   * RegisterForm constructor.
   * @param Connection $connection
   */
  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * @param ContainerInterface $container
   * @return FormBase|static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'register_form';
  }

  /**
   * Helper method so we can have consistent dialog options.
   *
   * @return string[]
   *   An array of jQuery UI elements to pass on to our dialog form.
   */
  protected static function getDataDialogOptions() {
    return [
      'width' => '50%',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['message'] = [
      '#type' => 'markup',
      '#markup' => '<div class="result_message"></div>'
    ];

    $form['user_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Nombre del usuario'),
      '#description' => $this->t('Ingrese el nombre del usuario a registrar.'),
      '#size' => 64,
      '#weight' => '0',

      '#suffix' => '<span class="user-name-valid-message"></span>'
    ];

    $form['use_ajax_container']['use_ajax'] = [
      '#type' => 'link',
      '#title' => $this->t('See this form as a modal.'),
      '#attributes' => [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => json_encode(static::getDataDialogOptions()),
        // Add this id so that we can test this form.
        'id' => 'ajax-example-modal-link',
      ],
    ];

    $form['actions'] = [
      '#type' => 'button',
      '#value' => $this->t('Submit'),

      '#ajax' => [
        'callback' => '::setMessage',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function setMessage(array $form, FormStateInterface $form_state) {
    $user_name = $form_state->getValue('user_name');
    $response = new AjaxResponse();
    $loaded_name = $this->loadUserbyName($user_name);
    $is_invalid = $this->validateName($loaded_name['name'], $user_name);
    if (empty($is_invalid)) {
      try {
        $new_user = $this->connection->insert('crud_user')
          ->fields([
            'name' => $user_name,
          ])
          ->execute();
        $response->addCommand(
          new HtmlCommand(
            '.result_message',
            '<div class="my_top_message">' . t('The user @user_name has been saved successfully',
              ['@user_name' => ($user_name )]) . '</div>')
        );

        $content = [
          '#type' => 'item',
          '#markup' => $this->t("Your new assigned Id is '%id'.", ['%id' => $new_user]),
        ];

        $title = 'Congratulation';
        $response->addCommand(new OpenModalDialogCommand($title, $content, static::getDataDialogOptions()));
      } catch (Exception $e) {
        watchdog_exception('error', $e);
      }
    } else {
      $response->addCommand(
        new HtmlCommand(
          '.result_message',
          '<div class="my_top_message">' . $is_invalid . '</div>'
        )
      );
    }
    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function validateName(string $loaded_name = null, string $user_name) {

    // Check if the name is repeated
    $message = '';
    if (isset($loaded_name) && $loaded_name === $user_name) {
      $message = $this->t('Use a different name, ' . $loaded_name . ' it`s already taken');
    }

    // Check input length
    if (isset($user_name) &&  strlen($user_name) < 5) {
      $message .= $this->t('The user name must be at least 5 characters long.');
    }

    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function loadUserbyName($name) {
    $query = $this->connection->select('crud_user', 'f')
      ->fields('f')
      ->condition('name', $name);
    return $query->execute()->fetchAssoc();
  }


  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
