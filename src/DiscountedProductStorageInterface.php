<?php

namespace Drupal\commerce_discounted_product;

use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_promotion\Entity\PromotionInterface;

/**
 * Defines the discounted products storage interface.
 *
 * Note, that we don't store the information about discounted products as
 * entities. Neither the storage is inheriting any entity storage related
 * classes or interfaces.
 */
interface DiscountedProductStorageInterface {

  /**
   * The name of the database table storing the disount-product relationships.
   */
  const DATABASE_TABLE_NAME = 'commerce_discounted_product';

  /**
   * Purges outdated records (end date in the past).
   */
  public function cleanupOutdatedRecords();

  /**
   * Deletes all records that are referring to the given product ID.
   *
   * @param int $product_id
   *   The product entity ID.
   */
  public function deleteByProductId($product_id);

  /**
   * Deletes all records that are referring to the given promotion ID.
   *
   * @param int $promotion_id
   *   The promotion entity ID.
   */
  public function deleteByPromotionId($promotion_id);

  /**
   * Deletes all records that are referring to the given product ID.
   *
   * @param int $product_id
   *   The product entity ID.
   *
   * @return object[]
   *   An array of result rows (plain objects as returned from fetching the
   *   database query result), indexed by promotion ID.
   */
  public function loadByProductId($product_id);

  /**
   * Deletes all records that are referring to the given promotion ID.
   *
   * @param int $promotion_id
   *   The promotion entity ID.
   *
   * @return object[]
   *   An array of result rows (plain objects as returned from fetching the
   *   database query result), indexed by product ID.
   */
  public function loadByPromotionId($promotion_id);

  /**
   * Returns a select query, initialized with current date conditions.
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   *   The select query, initialized for currently (today) discounted products.
   */
  public function query();

  /**
   * Return the currently discounted product IDs.
   *
   * The entries stored in 'commerce_discounted_product' database table will be
   * queried and filtered by the current date - the start and the optional end
   * dates must match.
   *
   * @return int[]
   *   A list of currently discounted product IDs.
   */
  public function getDiscountedProductIds();

  /**
   * Updates records for the given promotion and products.
   *
   * Please note that updating in this context means, that either rows will be
   * inserted or updated, depending if entries for the given combination of
   * promotion and product already exist. Optionally, any existing database row
   * for the given promotion and a product not contained in the given array,
   * can be deleted.
   *
   * Please also note, that implementing classes should not do status checks
   * for the given promotion. The caller is responsible to only do update calls
   * with promotions having active status, as well as deleting rows for disabled
   * promotions by calling static::deleteByPromotionId().
   *
   * @param \Drupal\commerce_promotion\Entity\PromotionInterface $promotion
   *   The promotion entity.
   * @param int[] $product_ids
   *   An array of product entity IDs, that are affected by the given promotion.
   * @param bool $cleanup_leftover_rows
   *   Whether to delete rows referencing the given promotion, but having a
   *   product ID that is not contained in the given array of product IDs.
   *   Defaults to TRUE.
   */
  public function update(PromotionInterface $promotion, array $product_ids, $cleanup_leftover_rows = TRUE);

  /**
   * Updates records for the given product and promotions.
   *
   * Please note that updating in this context means, that either rows will be
   * inserted or updated, depending if entries for the given combination of
   * promotion and product already exist. Optionally, any existing database row
   * for the given product and a promotion not contained in the given array,
   * can be deleted.
   *
   * @param \Drupal\commerce_product\Entity\Product $product
   *   The product entity.
   * @param \Drupal\commerce_promotion\Entity\PromotionInterface[] $applicable_promotions
   *   The applicable promotions for the product. Must be indexed by ID.
   * @param bool $cleanup_leftover_rows
   *   Whether or not to delete rows referencing the given product, but having a
   *   promotion ID that is not contained in the given array of promotions.
   *   Defaults to TRUE.
   */
  public function updateProduct(Product $product, array $applicable_promotions, $cleanup_leftover_rows = TRUE);

}
