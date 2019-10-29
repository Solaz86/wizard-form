<?php

namespace Drupal\wizard_form\Form\Multistep;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract class MultistepFormBase.
 */
abstract class WizardFormBase extends FormBase {

  /**
   * Drupal\Core\TempStore\PrivateTempStore definition.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStore;

  /**
   * Drupal\Core\Session\AccountProxyInterface definition.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a new MultistepFormBase object.
   */
  public function __construct(
    PrivateTempStoreFactory $tempstore_private,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    MessengerInterface $messenger) {
    $this->tempStore = $tempstore_private->get('user_info');
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private'),
      $container->get('current_user'),
      $container->get('entity_type.manager'),
      $container->get('messenger')
    );
  }

  /**
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  protected function saveData() {
    // Logic for saving data goes here...
    $this->messenger->addMessage('The information has been saved successfully.');
  }

  /**
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  protected function deleteTempStore($keys) {
    foreach ($keys as $key) {
      $this->tempStore->delete($key);
    }
  }

  /**
   * @param $key
   * @param $value
   */
  protected function saveTempStore($key, $value) {
    $this->tempStore->set($key, $value);
  }

  protected function getDataTheme() {

    $message = 'User data. Click finish to proceed';

    $user = [
        'first_name' => $this->tempStore->get('first_name'),
        'last_name' => $this->tempStore->get('last_name'),
        'gender' => $this->tempStore->get('gender'),
        'birthday' => $this->tempStore->get('birthdate'),
        'city' => $this->tempStore->get('city'),
        'phone_number' => $this->tempStore->get('phone_number'),
        'address' => $this->tempStore->get('address'),
      ];


    $build = [
      '#theme' => 'wizard_form_result',
      '#message' => $message,
//      'user_result' => $user,
    ];

    return $build;
  }

}
