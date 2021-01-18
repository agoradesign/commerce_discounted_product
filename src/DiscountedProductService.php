<?php

namespace Drupal\commerce_discounted_product;

use Drupal\commerce_discounted_product\Applicability\ApplicabilityCheckerInterface;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Default discounted products service implementation.
 */
class DiscountedProductService implements DiscountedProductServiceInterface {

  /**
   * The applicability checker.
   *
   * @var \Drupal\commerce_discounted_product\Applicability\ApplicabilityCheckerInterface
   */
  protected $applicabilityChecker;

  /**
   * The promotion storage.
   *
   * @var \Drupal\commerce_promotion\PromotionStorageInterface
   */
  protected $promotionStorage;

  /**
   * The time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new DiscountedProductService object.
   *
   * @param \Drupal\commerce_discounted_product\Applicability\ApplicabilityCheckerInterface $applicability_checker
   *   The applicability checker.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   */
  public function __construct(ApplicabilityCheckerInterface $applicability_checker, EntityTypeManagerInterface $entity_type_manager, TimeInterface $time) {
    $this->applicabilityChecker = $applicability_checker;
    $this->promotionStorage = $entity_type_manager->getStorage('commerce_promotion');
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public function loadApplicablePromotions(ProductInterface $product) {
    $today = gmdate('Y-m-d\TH:i:s', $this->time->getRequestTime());

    $query = $this->promotionStorage->getQuery();
    $or_condition = $query->orConditionGroup()
      ->condition('end_date', $today, '>=')
      ->notExists('end_date');
    $query->condition('status', TRUE);
    $query->condition($or_condition);
    $query->notExists('coupons');
    $query->sort('weight');
    $result = $query->execute();
    if (empty($result)) {
      return [];
    }
    /** @var \Drupal\commerce_promotion\Entity\PromotionInterface[] $promotions */
    $promotions = $this->promotionStorage->loadMultiple($result);
    $filtered_promotions = [];
    foreach ($promotions as $promotion) {
      $product_ids = $this->applicabilityChecker->determineAffectedProductIds($promotion);
      if (in_array($product->id(), $product_ids)) {
        $filtered_promotions[$promotion->id()] = $promotion;
      }
    }
    return $filtered_promotions;
  }

}
