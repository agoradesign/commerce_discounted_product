<?php

namespace Drupal\commerce_discounted_product;

use Drupal\commerce_product\Entity\ProductInterface;

/**
 * Defines the discounted products service interface.
 */
interface DiscountedProductServiceInterface {

  /**
   * Load the applicable promotions for the given product.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *   The product entity.
   *
   * @return \Drupal\commerce_promotion\Entity\PromotionInterface[]
   *   The applicable promotions. This includes future starting promotions and
   *   ignores usage limitations, but excludes promotions having coupons on the
   *   other side, as well of inactive and outdated ones of course.
   */
  public function loadApplicablePromotions(ProductInterface $product);

}
