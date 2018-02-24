<?php

namespace Drupal\commerce_discounted_product\Applicability;

use Drupal\commerce_promotion\Entity\PromotionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Applicability checker impl. for 'order_item_variation_type' condition.
 */
class ProductVariationTypeApplicabilityChecker implements ApplicabilityCheckerInterface {

  /**
   * The product entity storage.
   *
   * @var \Drupal\Core\Entity\ContentEntityStorageInterface
   */
  protected $productStorage;

  /**
   * Constructs a new ProductVariationTypeApplicabilityChecker object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->productStorage = $entity_type_manager->getStorage('commerce_product');
  }

  /**
   * {@inheritdoc}
   */
  public function applies(PromotionInterface $promotion) {
    foreach ($promotion->getConditions() as $condition) {
      if ($condition->getPluginId() == 'order_item_variation_type') {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function determineAffectedProductIds(PromotionInterface $promotion) {
    $results = [];
    foreach ($promotion->getConditions() as $condition) {
      if ($condition->getPluginId() != 'order_item_variation_type') {
        continue;
      }
      $configuration = $condition->getConfiguration();
      $bundles = $configuration['variation_types'];
      $query = $this->productStorage->getQuery();
      $query->condition('status', TRUE);
      $query->condition('variations.entity.type', $bundles, 'IN');
      $results[] = $query->execute();
    }

    $product_ids = [];
    foreach ($results as $result) {
      if ($promotion->getConditionOperator() === 'OR') {
        $product_ids = array_merge($product_ids, $result);
      }
      else {
        if (empty($result)) {
          return [];
        }
        $product_ids = !empty($product_ids) ? array_intersect($product_ids, $result) : $result;
        if (empty($product_ids)) {
          return [];
        }
      }
    }
    return $product_ids;
  }

}
