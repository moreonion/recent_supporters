<?php

namespace Drupal\recent_supporters;

class FieldPolling implements \Drupal\polling\FieldTypePluginInterface {
  protected $name;
  protected $entity;
  protected $items;

  public static function instance($entity, $field, $instance) {
    return new static($entity, $field, $instance);
  }

  public function __construct($entity, $field, $instance) {
    $entity_type = $instance['entity_type'];
    $this->name = $field['field_name'];
    $this->entity = $entity;
    $this->items = field_get_items($entity_type, $entity, $this->name);
  }

  public function getData() {
    $data['recent_supporters'] = [];
    $defaults = \recent_supporters_settings();
    foreach ($this->items as $delta => $item) {
      $options = $item['options'] + $defaults;
      $backend = Loader::instance()->getBackend($options['backend']);
      $params = $backend->buildParams($options, $this->entity);
      $supporters = $backend->recentOnOneAction($params);
      $data['recent_supporters'][$this->name][$delta] = $supporters;
    }
    return $data;
  }
}
