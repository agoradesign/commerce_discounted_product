<?php

namespace Drupal\commerce_discounted_product\Applicability;

/**
 * Runs a chain of added applicability checkers.
 *
 * Each checker in the chain can be another chain, which is why this interface
 * extends the base interface.
 *
 * This is a similar architecture to the known resolver pattern in Commerce,
 * e.g. price resolvers. An important difference is, that the calling the
 * chained checkers won't stop after receiving the first result. As promotion
 * can have multiple conditions and different applicability checkers can answer
 * different conditions, we have to take all answers into account. The only
 * exception is, that when a checker applies that is implementing the
 * FinalDecisionApplicabilityCheckerInterface, then the execution should be
 * stopped immediately.
 *
 * @see \Drupal\commerce_discounted_product\Applicability\ApplicabilityCheckerInterface
 * @see \Drupal\commerce_discounted_product\Applicability\FinalDecisionApplicabilityCheckerInterface
 */
interface ChainApplicabilityCheckerInterface extends ApplicabilityCheckerInterface {

  /**
   * Adds a checker.
   *
   * @param \Drupal\commerce_discounted_product\Applicability\ApplicabilityCheckerInterface $checker
   *   The checker.
   */
  public function addChecker(ApplicabilityCheckerInterface $checker);

  /**
   * Gets all added checkers.
   *
   * @return \Drupal\commerce_discounted_product\Applicability\ApplicabilityCheckerInterface[]
   *   The checkers.
   */
  public function getCheckers();

}
