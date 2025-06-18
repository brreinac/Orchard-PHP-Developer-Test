<?php
namespace Drupal\product_of_the_day\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Database\Database;

class ProductForm extends FormBase {
  public function getFormId() {
    return 'pod_product_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $id = NULL) {
    $conn = Database::getConnection();
    $values = ['name'=>'','summary'=>'','image_fid'=>'','is_featured'=>0];
    if ($id) {
      $values = $conn->select('product_of_the_day','p')
        ->fields('p')
        ->condition('id',$id)
        ->execute()
        ->fetchAssoc();
    }

    $form['id'] = ['type'=>'hidden','#value'=>$id];
    $form['name'] = [
      '#type'=>'textfield',
      '#title'=>'Name',
      '#required'=>TRUE,
      '#default_value'=>$values['name'],
    ];
    $form['summary'] = [
      '#type'=>'textarea',
      '#title'=>'Summary',
      '#default_value'=>$values['summary'],
    ];
    $form['image_fid'] = [
      '#type'=>'managed_file',
      '#title'=>'Image',
      '#upload_location'=>'public://pod_images/',
      '#default_value'=>$values['image_fid'] ? [$values['image_fid']] : [],
      '#required'=>TRUE,
    ];
    $form['is_featured'] = [
      '#type'=>'checkbox',
      '#title'=>'Featured (max 5)',
      '#default_value'=>$values['is_featured'],
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type'=>'submit',
      '#value'=>$id ? 'Update' : 'Add product',
    ];
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getValue('is_featured')) {
      $count = Database::getConnection()->select('product_of_the_day','p')
        ->condition('is_featured',1)
        ->countQuery()
        ->execute()
        ->fetchField();
      if ($count >= 5 && !$form_state->getValue('id')) {
        $form_state->setErrorByName('is_featured','Only 5 products can be featured.');
      }
    }
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    /** @var \Drupal\file\FileInterface $file */
    $file = File::load($values['image_fid'][0]);
    $file->setPermanent();
    $file->save();

    $conn = Database::getConnection();
    $fields = [
      'name'=>$values['name'],
      'summary'=>$values['summary'],
      'image_fid'=>$file->id(),
      'is_featured'=>$values['is_featured'],
      'changed'=>\Drupal::time()->getRequestTime(),
    ];
    if ($id = $values['id']) {
      $conn->update('product_of_the_day')
        ->fields($fields)
        ->condition('id',$id)
        ->execute();
    }
    else {
      $fields['created'] = \Drupal::time()->getRequestTime();
      $conn->insert('product_of_the_day')
        ->fields($fields)
        ->execute();
    }
    $this->messenger()->addStatus('Product saved.');
    $form_state->setRedirect('product_of_the_day.product_list');
  }
}
