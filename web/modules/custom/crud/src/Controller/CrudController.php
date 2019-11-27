<?php

namespace Drupal\crud\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\crud\Form\RegisterForm;
use Symfony\Component\DependencyInjection\ContainerInterface;


//use Drupal\Core\Database\Connection;


/**
 * Class DefaultController.
 */
class CrudController extends ControllerBase {

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
   * @return array
   *   Return Hello string.
   */
  public function register() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: register')
    ];
  }

  /**
   * @return array
   *   Return read string.
   */
  public function load() {

    $query = $this->connection->select('crud_user', 'f')
      ->fields('f');
    $result = $query->execute();

    $users = [];
    while($record = $result->fetchAssoc()) {
      $users[] = $record;
    }


    return [
      '#theme' => 'crud',
      '#users' => $users
    ];

  }

  /**
   * @return array
   *   Return read string.
   */
  public function import() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: import')
    ];

  }

}
