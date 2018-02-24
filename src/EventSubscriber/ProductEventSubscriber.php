<?php

namespace Drupal\commerce_discounted_product\EventSubscriber;

use Drupal\commerce_discounted_product\DiscountedProductServiceInterface;
use Drupal\commerce_discounted_product\DiscountedProductStorageInterface;
use Drupal\commerce_product\Event\ProductEvent;
use Drupal\commerce_product\Event\ProductEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to product CUD operations and updates discounted product info.
 */
class ProductEventSubscriber implements EventSubscriberInterface {

  /**
   * The discounted product service.
   *
   * @var \Drupal\commerce_discounted_product\DiscountedProductServiceInterface
   */
  protected $discountedProductService;

  /**
   * The discounted product storage.
   *
   * @var \Drupal\commerce_discounted_product\DiscountedProductStorageInterface
   */
  protected $discountedProductStorage;

  /**
   * Constructs a new ProductEventSubscriber object.
   *
   * @param DiscountedProductServiceInterface $discounted_product_service
   *   The discounted product service.
   * @param DiscountedProductStorageInterface $discounted_product_storage
   *   The discounted product storage.
   */
  public function __construct(DiscountedProductServiceInterface $discounted_product_service, DiscountedProductStorageInterface $discounted_product_storage) {
    $this->discountedProductService = $discounted_product_service;
    $this->discountedProductStorage = $discounted_product_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      ProductEvents::PRODUCT_INSERT => ['onProductUpdate', 0],
      ProductEvents::PRODUCT_UPDATE => ['onProductUpdate', 0],
      ProductEvents::PRODUCT_DELETE => ['onProductDelete', 0],
    ];
    return $events;
  }

  /**
   * Deletes all discounted product info referring to the given product.
   *
   * @param \Drupal\commerce_product\Event\ProductEvent $event
   *   The product event.
   */
  public function onProductDelete(ProductEvent $event) {
    $this->discountedProductStorage->deleteByProductId($event->getProduct()->id());
  }

  /**
   * Updates discounted product info for the given product.
   *
   * @param \Drupal\commerce_product\Event\ProductEvent $event
   *   The product event.
   */
  public function onProductUpdate(ProductEvent $event) {
    $promotions = $this->discountedProductService->loadApplicablePromotions($event->getProduct());
    $this->discountedProductStorage->updateProduct($event->getProduct(), $promotions, TRUE);
  }

}
