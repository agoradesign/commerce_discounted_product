<?php

namespace Drupal\commerce_discounted_product;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_promotion\Entity\PromotionInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Database\Connection;

/**
 * Default discounted products storage implementation.
 */
class DiscountedProductStorage implements DiscountedProductStorageInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * {@inheritdoc}
   */
  public function cleanupOutdatedRecords() {
    $today = gmdate('Y-m-d\TH:i:s', $this->time->getRequestTime());
    $this->database->delete(static::DATABASE_TABLE_NAME)
      ->isNotNull('end_date')
      ->condition('end_date', $today, '<')
      ->execute();
  }

  /**
   * Constructs a new DiscountedProductsStorage object.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   */
  public function __construct(Connection $database, TimeInterface $time) {
    $this->database = $database;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteByProductId($product_id) {
    $this->database->delete(static::DATABASE_TABLE_NAME)
      ->condition('product_id', $product_id)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function deleteByPromotionId($promotion_id) {
    $this->database->delete(static::DATABASE_TABLE_NAME)
      ->condition('promotion_id', $promotion_id)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function loadByProductId($product_id) {
    $query = $this->database->select(static::DATABASE_TABLE_NAME, 'cdp');
    $query->condition('cdp.product_id', $product_id);
    $query->fields('cdp');
    return $query->execute()->fetchAllAssoc('promotion_id');
  }

  /**
   * {@inheritdoc}
   */
  public function loadByPromotionId($promotion_id) {
    $query = $this->database->select(static::DATABASE_TABLE_NAME, 'cdp');
    $query->condition('cdp.promotion_id', $promotion_id);
    $query->fields('cdp');
    return $query->execute()->fetchAllAssoc('product_id');
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $today = gmdate('Y-m-d\TH:i:s', $this->time->getRequestTime());
    $query = $this->database->select(static::DATABASE_TABLE_NAME, 'cdp');
    $or_condition = $query->orConditionGroup()
      ->condition('cdp.end_date', $today, '>=')
      ->isNull('cdp.end_date');
    $query->condition('cdp.start_date', $today, '<=');
    $query->condition($or_condition);
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function getDiscountedProductIds() {
    $query = $this->query();
    $query->fields('cdp', ['product_id']);
    return $query->execute()->fetchCol();
  }

  /**
   * {@inheritdoc}
   */
  public function update(PromotionInterface $promotion, array $product_ids, $cleanup_leftover_rows = TRUE) {
    $existing_entries = $this->loadByPromotionId($promotion->id());
    $existing_product_ids = array_keys($existing_entries);
    $products_to_update = array_intersect($product_ids, $existing_product_ids);
    $products_to_insert = array_diff($product_ids, $existing_product_ids);
    $products_to_delete = $cleanup_leftover_rows ? array_diff($existing_product_ids, $product_ids) : [];

    if (!empty($products_to_insert)) {
      $insert_query = $this->database->insert(static::DATABASE_TABLE_NAME);
      $insert_query->fields([
        'promotion_id',
        'product_id',
        'start_date',
        'end_date',
      ]);
      foreach ($products_to_insert as $product_id) {
        $insert_query->values($this->buildRowArray($promotion, $product_id));
      }
      $insert_query->execute();
    }

    if (!empty($products_to_update)) {
      foreach ($products_to_update as $product_id) {
        $update_query = $this->database->update(static::DATABASE_TABLE_NAME);
        $update_query->condition('promotion_id', $promotion->id());
        $update_query->condition('product_id', $product_id);
        $update_query->fields($this->buildRowArray($promotion, $product_id, FALSE));
        $update_query->execute();
      }
    }

    if (!empty($products_to_delete)) {
      $this->database->delete(static::DATABASE_TABLE_NAME)
        ->condition('promotion_id', $promotion->id())
        ->condition('product_id', $products_to_delete, 'IN')
        ->execute();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateProduct(Product $product, array $applicable_promotions, $cleanup_leftover_rows = TRUE) {
    $promotion_ids = array_keys($applicable_promotions);
    $existing_entries = $this->loadByProductId($product->id());
    $existing_promotion_ids = array_keys($existing_entries);
    $promotions_to_update = array_intersect($promotion_ids, $existing_promotion_ids);
    $promotions_to_insert = array_diff($promotion_ids, $existing_promotion_ids);
    $promotions_to_delete = $cleanup_leftover_rows ? array_diff($existing_promotion_ids, $promotion_ids) : [];

    if (!empty($promotions_to_insert)) {
      $insert_query = $this->database->insert(static::DATABASE_TABLE_NAME);
      $insert_query->fields([
        'promotion_id',
        'product_id',
        'start_date',
        'end_date',
      ]);
      foreach ($promotions_to_insert as $promotion_id) {
        $promotion = $applicable_promotions[$promotion_id];
        $insert_query->values($this->buildRowArray($promotion, $product->id()));
      }
      $insert_query->execute();
    }

    if (!empty($promotions_to_update)) {
      foreach ($promotions_to_update as $promotion_id) {
        $promotion = $applicable_promotions[$promotion_id];
        $update_query = $this->database->update(static::DATABASE_TABLE_NAME);
        $update_query->condition('promotion_id', $promotion_id);
        $update_query->condition('product_id', $product->id());
        $update_query->fields($this->buildRowArray($promotion, $product->id(), FALSE));
        $update_query->execute();
      }
    }

    if (!empty($promotions_to_delete)) {
      $this->database->delete(static::DATABASE_TABLE_NAME)
        ->condition('product_id', $product->id())
        ->condition('promotion_id', $promotions_to_delete, 'IN')
        ->execute();
    }
  }

  /**
   * Helper function building an array representing a database row.
   *
   * The resulting array can be used for inserting or updating rows in the
   * 'commerce_discounted_product' database table.
   *
   * @param \Drupal\commerce_promotion\Entity\PromotionInterface $promotion
   *   The promotion entity.
   * @param int $product_id
   *   The product ID.
   * @param bool $set_keys
   *   Whether to set the primary keys (product_id and promotion_id). Defaults
   *   to TRUE.
   *
   * @return array
   *   An array suitable for inserting or updating a row in the
   *   'commerce_discounted_product' database table.
   */
  protected function buildRowArray(PromotionInterface $promotion, $product_id, $set_keys = TRUE) {
    $row = [
      'start_date' => $promotion->get('start_date')->value,
      'end_date' => $promotion->get('end_date')->value,
    ];
    if ($set_keys) {
      $row['product_id'] = $product_id;
      $row['promotion_id'] = $promotion->id();
    }
    return $row;
  }

}
