<?php

namespace Drupal\crud\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Class ImportForm.
 */
class ImportForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['upload_csv'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Upload CSV'),
      '#upload_location' => 'public://csv_users',
      '#upload_validators' => [
        'file_validate_extensions' => ['csv'],
      ],
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

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

    //@TODO implement batch  https://www.drupal.org/forum/support/module-development-and-code-questions/2012-06-30/solved-bulk-importing-from-csv-via
    //contrib\examples\batch_example\src\Form\BatchExampleForm.php
    $form_file = $form_state->getValue('upload_csv');

    if (isset($form_file[0]) && !empty($form_file[0])) {

      /** @var \Drupal\file\Entity\File $file */
      $file = File::load($form_file[0]);

      $destination = $file->getFileUri();

      $file = fopen($destination, "r");

      while (!feof($file)) {
        $users = fgetcsv($file);
        //@todo inject service
        \Drupal::database()->insert('crud_user')
          ->fields(array(
            'name' => $users[0],
          ))
          ->execute();
      }

      fclose($file);
      //@todo inject service
      \Drupal::messenger()->addMessage('CSV data added to the database');
    } else {
      \Drupal::messenger()->addError('empty file');
    }
  }

}
