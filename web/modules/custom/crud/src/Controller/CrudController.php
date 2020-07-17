<?php

namespace Drupal\crud\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBase;
use Drupal\crud\Form\RegisterForm;
use Symfony\Component\HttpFoundation\Response;
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
   * @return \Symfony\Component\HttpFoundation\Response
   *   Return response.
   */
  public function exportUsers() {
    $rows = array();
    $select = \Drupal::service('database')
      ->select('crud_user', 'f')
      ->fields('f')
      ->execute();

    // Get all the results.
    $results = $select->fetchAll();

    foreach ($results as $result) {
      $rows[] = implode(',', (array) $result);
    }

    $content = implode("\n", $rows);
    $response = new Response($content);
    $response->headers->set('Content-Type', 'text/csv');
    $response->headers->set('Content-Disposition','attachment; filename="users.csv"');

    return $response;
  }


}
