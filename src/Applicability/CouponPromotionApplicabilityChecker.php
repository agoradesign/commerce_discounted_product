<?php

namespace Drupal\commerce_discounted_product\Applicability;

use Drupal\commerce_promotion\Entity\PromotionInterface;

/**
 * Applicability checker implementations for promotions having coupons.
 *
 * Any promotion with attached coupons should be skipped, as there won't be a
 * discount without coupon redemption. We only want to index promotions that
 * automatically provide discounts.
 */
class CouponPromotionApplicabilityChecker implements FinalDecisionApplicabilityCheckerInterface {

  /**
   * {@inheritdoc}
   */
  public function applies(PromotionInterface $promotion) {
    return $promotion->hasCoupons();
  }

  /**
   * {@inheritdoc}
   */
  public function determineAffectedProductIds(PromotionInterface $promotion) {
    return [];
  }

}
