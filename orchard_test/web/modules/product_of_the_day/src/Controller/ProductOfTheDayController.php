<?php

namespace Drupal\product_of_the_day\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Controller for listing Product of the Day entries.
 */
class ProductListController extends ControllerBase {

  /** @var \Drupal\Core\Database\Connection */
  protected $connection;

  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('database'));
  }

  public function list() {
    $header = [
      'id'          => $this->t('ID'),
      'name'        => $this->t('Name'),
      'is_featured' => $this->t('Featured'),
      'operations'  => $this->t('Operations'),
    ];

    $rows = [];
    $query = $this->connection->select('product_of_the_day', 'p')
      ->fields('p', ['id','name','is_featured'])
      ->orderBy('id');
    $results = $query->execute()->fetchAllAssoc('id');

    foreach ($results as $id => $record) {
      $operations = [];
      $operations['edit'] = Link::fromTextAndUrl(
        $this->t('Edit'),
        Url::fromRoute('product_of_the_day.edit', ['id' => $id])
      );
      $rows[] = [
        'id'          => $id,
        'name'        => $record->name,
        'is_featured' => $record->is_featured ? $this->t('Yes') : $this->t('No'),
        'operations'  => ['data' => ['#type' => 'operations', '#links' => $operations]],
      ];
    }

    $build = [
      'table' => [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $this->t('No products found.'),
      ],
      'add' => [
        '#type' => 'link',
        '#title' => $this->t('Add product'),
        '#url' => Url::fromRoute('product_of_the_day.add'),
        '#attributes' => ['class' => ['button', 'button--primary']],
      ],
    ];

    return $build;
  }
}
