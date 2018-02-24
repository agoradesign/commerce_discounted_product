<?php

namespace Drupal\commerce_discounted_product\Applicability;

use Drupal\commerce_promotion\Entity\PromotionInterface;

/**
 * Defines the interface for product promotion applicability checkers.
 *
 * As this module is about finding out and storing information about which
 * products are discounted by being affected by Commerce promotion entities,
 * we have to identify what this relationship first. Different kind of
 * promotion conditions require different kind of analysis. This is, where our
 * applicability checkers come into the game. Each checker implementation
 * defines, if it is able to answer this question, and if yes, what products are
 * affected by a certain promotion rule.
 */
interface ApplicabilityCheckerInterface {

  /**
   * Returns whether this checker is able to resolve product relationships.
   *
   * @param PromotionInterface $promotion
   *   The promotion entity.
   *
   * @return bool
   *   TRUE, if this checker is able to resolve the product relationships for
   *   the given promotion entity. E.g. one checker can be responsible for
   *   detecting affected products for promotion conditions that reference a
   *   certain taxonomy vocabulary, but not for direct product relationships.
   */
  public function applies(PromotionInterface $promotion);

  /**
   * Determines the affected product IDs for the given promotion entity.
   *
   * Please note, that there is an important semantic difference on empty
   * results, whether the checker is applicable (@see static::applies()) at all.
   * Non applicable checkers will always return an empty result, while
   * applicable checkers returning an empty array haven't found any suitable
   * product. The caller is responsible for differentiating here, only asking
   * applicable checkers. Our ChainApplicabilityChecker will take care of
   * this. Calling any single applicability checker is inappropriate, always
   * ask the chain checker.
   *
   * @param \Drupal\commerce_promotion\Entity\PromotionInterface $promotion
   *   The promotion entity.
   *
   * @return int[]
   *   The product IDs that are affected by the given promotion entity.
   */
  public function determineAffectedProductIds(PromotionInterface $promotion);

}
