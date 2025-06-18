<?php

namespace Drupal\product_of_the_day\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form for adding/editing products.
 */
class ProductForm extends FormBase {

  /** @var \Drupal\Core\Database\Connection */
  protected $connection;

  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('database'));
  }

  public function getFormId() {
    return 'product_of_the_day_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $values = [
      'name' => '',
      'summary' => '',
      'image_fid' => NULL,
      'is_featured' => 0,
    ];
    if ($id) {
      $record = $this->connection->select('product_of_the_day', 'p')
        ->fields('p')
        ->condition('id', $id)
        ->execute()
        ->fetchAssoc();
      if ($record) {
        $values = $record;
      }
    }

    $form['id'] = ['#type' => 'hidden', '#value' => $id];
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name'),
      '#required' => TRUE,
      '#default_value' => $values['name'],
    ];
    $form['summary'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Summary'),
      '#default_value' => $values['summary'],
    ];
    $form['image_fid'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Image'),
      '#upload_location' => 'public://pod_images/',
      '#default_value' => $values['image_fid'] ? [$values['image_fid']] : NULL,
      '#required' => TRUE,
    ];
    $form['is_featured'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Featured (max 5)'),
      '#default_value' => $values['is_featured'],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $id ? $this->t('Update') : $this->t('Add'),
    ];

    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('is_featured')) {
      $count = $this->connection->select('product_of_the_day', 'p')
        ->condition('is_featured', 1)
        ->countQuery()
        ->execute()
        ->fetchField();
      // If adding new or toggling on, and already 5 featured.
      if ($count >= 5 && ! $form_state->getValue('id')) {
        $form_state->setErrorByName('is_featured', $this->t('Only up to 5 products can be featured.'));
      }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    // Handle file.
    $fid = $values['image_fid'][0];
    /** @var \Drupal\file\Entity\File $file */
    $file = File::load($fid);
    $file->setPermanent();
    $file->save();

    $fields = [
      'name' => $values['name'],
      'summary' => $values['summary'],
      'image_fid' => $fid,
      'is_featured' => $values['is_featured'],
      'changed' => \Drupal::time()->getRequestTime(),
    ];
    if ($id = $values['id']) {
      // Update.
      $this->connection->update('product_of_the_day')
        ->fields($fields)
        ->condition('id', $id)
        ->execute();
    }
    else {
      // Insert.
      $fields['created'] = \Drupal::time()->getRequestTime();
      $this->connection->insert('product_of_the_day')
        ->fields($fields)
        ->execute();
    }

    $this->messenger()->addStatus($this->t('Product has been saved.'));
    $form_state->setRedirect('product_of_the_day.list');
  }
}
