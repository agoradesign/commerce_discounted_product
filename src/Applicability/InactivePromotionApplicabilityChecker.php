<?php

namespace Drupal\commerce_discounted_product\Applicability;

use Drupal\commerce_promotion\Entity\PromotionInterface;
use Drupal\Component\Datetime\TimeInterface;

/**
 * Applicability checker implementations for inactive and outdated promotions.
 */
class InactivePromotionApplicabilityChecker implements FinalDecisionApplicabilityCheckerInterface {

  /**
   * The time.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * Constructs a new InactivePromotionApplicabilityChecker object.
   *
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time.
   */
  public function __construct(TimeInterface $time) {
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(PromotionInterface $promotion) {
    if (!$promotion->isEnabled()) {
      return TRUE;
    }
    if ($promotion->get('end_date')->isEmpty()) {
      return FALSE;
    }
    $end_date = $promotion->get('end_date')->value;
    $today = gmdate('Y-m-d', $this->time->getRequestTime());
    return $end_date < $today;
  }

  /**
   * {@inheritdoc}
   */
  public function determineAffectedProductIds(PromotionInterface $promotion) {
    return [];
  }

}
