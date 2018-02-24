<?php

namespace Drupal\commerce_discounted_product\EventSubscriber;

use Drupal\commerce_discounted_product\Applicability\ApplicabilityCheckerInterface;
use Drupal\commerce_discounted_product\DiscountedProductStorageInterface;
use Drupal\commerce_promotion\Event\PromotionEvent;
use Drupal\commerce_promotion\Event\PromotionEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to promotion CUD operations and updates discounted product info.
 */
class PromotionEventSubscriber implements EventSubscriberInterface {

  /**
   * The applicability checker.
   *
   * @var \Drupal\commerce_discounted_product\Applicability\ApplicabilityCheckerInterface
   */
  protected $applicabilityChecker;

  /**
   * The discounted product storage.
   *
   * @var \Drupal\commerce_discounted_product\DiscountedProductStorageInterface
   */
  protected $discountedProductStorage;

  /**
   * Constructs a new PromotionEventSubscriber object.
   *
   * @param \Drupal\commerce_discounted_product\Applicability\ApplicabilityCheckerInterface
   *   The applicability checker.
   * @param DiscountedProductStorageInterface $discounted_product_storage
   *   The discounted product storage.
   */
  public function __construct(ApplicabilityCheckerInterface $applicability_checker, DiscountedProductStorageInterface $discounted_product_storage) {
    $this->applicabilityChecker = $applicability_checker;
    $this->discountedProductStorage = $discounted_product_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      PromotionEvents::PROMOTION_INSERT => ['onPromotionUpdate', 0],
      PromotionEvents::PROMOTION_UPDATE => ['onPromotionUpdate', 0],
      PromotionEvents::PROMOTION_DELETE => ['onPromotionDelete', 0],
    ];
    return $events;
  }

  /**
   * Deletes all discounted product info referring to the given promotion.
   *
   * @param \Drupal\commerce_promotion\Event\PromotionEvent $event
   *   The promotion event.
   */
  public function onPromotionDelete(PromotionEvent $event) {
    $this->discountedProductStorage->deleteByPromotionId($event->getPromotion()->id());
  }

  /**
   * Updates discounted product info for the given promotion.
   *
   * @param \Drupal\commerce_promotion\Event\PromotionEvent $event
   *   The promotion event.
   */
  public function onPromotionUpdate(PromotionEvent $event) {
    $product_ids = $this->applicabilityChecker->determineAffectedProductIds($event->getPromotion());
    $this->discountedProductStorage->update($event->getPromotion(), $product_ids, TRUE);
  }

}
