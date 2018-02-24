<?php

/**
 * @file
 * Views integration for Commerce Discounted Product.
 */

use Drupal\commerce_discounted_product\DiscountedProductStorageInterface;

/**
 * Implements hook_views_data().
 *
 * Adds a custom filter to commerce products on whether a product is discounted.
 */
function commerce_discounted_product_views_data() {
  $data = [];
  $table = DiscountedProductStorageInterface::DATABASE_TABLE_NAME;

  $data[$table]['table']['group'] = t('Product');

  $data[$table]['table']['join'] = [
    'commerce_product_field_data' => [
      'left_field' => 'product_id',
      'field' => 'product_id',
    ],
  ];

  $data[$table]['product_id'] = [
    'title' => t('Discounted'),
    'help' => t('Filter by discounted products'),
    'filter' => [
      'id' => 'commerce_discounted_product',
    ],
  ];

  return $data;
}
