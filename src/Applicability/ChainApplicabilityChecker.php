<?php

namespace Drupal\commerce_discounted_product\Applicability;

use Drupal\commerce_promotion\Entity\PromotionInterface;

/**
 * Default implementation of the chain applicability checker.
 */
class ChainApplicabilityChecker implements ChainApplicabilityCheckerInterface {

  /**
   * The checkers.
   *
   * @var \Drupal\commerce_discounted_product\Applicability\ApplicabilityCheckerInterface[]
   */
  protected $checkers = [];

  /**
   * Constructs a new ChainApplicabilityChecker object.
   *
   * @param \Drupal\commerce_discounted_product\Applicability\ApplicabilityCheckerInterface[] $checkers
   *   The checkers.
   */
  public function __construct(array $checkers = []) {
    $this->checkers = $checkers;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(PromotionInterface $promotion) {
    foreach ($this->checkers as $checker) {
      if ($checker->applies($promotion)) {
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
    foreach ($this->checkers as $checker) {
      if (!$checker->applies($promotion)) {
        continue;
      }
      $results[] = $checker->determineAffectedProductIds($promotion);
      if ($checker instanceof FinalDecisionApplicabilityCheckerInterface) {
        break;
      }
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

  /**
   * {@inheritdoc}
   */
  public function addChecker(ApplicabilityCheckerInterface $checker) {
    $this->checkers[] = $checker;
  }

  /**
   * {@inheritdoc}
   */
  public function getCheckers() {
    return $this->checkers;
  }

}
