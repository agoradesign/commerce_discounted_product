<?php

namespace Drupal\commerce_discounted_product\Applicability;

use Drupal\commerce_promotion\Entity\PromotionInterface;

/**
 * Applicability checker implementation for 'order_item_product' condition.
 */
class ProductReferenceApplicabilityChecker implements ApplicabilityCheckerInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(PromotionInterface $promotion) {
    foreach ($promotion->getConditions() as $condition) {
      if ($condition->getPluginId() == 'order_item_product') {
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
      if ($condition->getPluginId() != 'order_item_product') {
        continue;
      }
      $configuration = $condition->getConfiguration();
      $results[] = array_column($configuration['products'], 'product_id');
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
