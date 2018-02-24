<?php

namespace Drupal\commerce_discounted_product\Applicability;

/**
 * Defines the final decision applicability checker interface.
 *
 * Classes implementing this interface provide final decisions, meaning that
 * no other check should be run in the chain after this one, given it applies.
 *
 * This makes sense for promotions that should be excluded from indexing under
 * all circumstances, e.g inactive or outdated ones.
 */
interface FinalDecisionApplicabilityCheckerInterface extends ApplicabilityCheckerInterface {

}
