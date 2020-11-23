<?php

namespace Drupal\commerce_discounted_product\Plugin\views\filter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Query\Condition;
use Drupal\Core\Form\FormStateInterface;
use Drupal\views\Plugin\views\filter\FilterPluginBase;

/**
 * Plugin implementation of the 'commerce_discounted_product' Views filter.
 *
 * @ViewsFilter("commerce_discounted_product")
 */
class DiscountedProductFilter extends FilterPluginBase {

  /**
   * {@inheritdoc}
   */
  public function adminSummary() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  protected function operatorForm(&$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function canExpose() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    $table = $this->ensureMyTable();
    $today = gmdate('Y-m-d\TH:i:s', \Drupal::time()->getRequestTime());
    $end_date_condition = new Condition('OR');
    $end_date_condition->condition($table . '.end_date', $today, '>=');
    $end_date_condition->isNull($table . '.end_date');
    $product_condition = new Condition('AND');
    $product_condition->isNotNull(sprintf('%s.%s', $table, $this->realField));
    $this->query->addWhere($this->options['group'], $product_condition);
    $this->query->addWhere($this->options['group'], $end_date_condition);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cache_tags = \Drupal::entityTypeManager()->getDefinition('commerce_promotion')
      ->getListCacheTags();
    return Cache::mergeTags(parent::getCacheTags(), $cache_tags);
  }

}
