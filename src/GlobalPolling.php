<?php

namespace Drupal\recent_supporters;

class GlobalPolling implements \Drupal\polling\GlobalPluginInterface {
  public static function instance() {
    return new static();
  }

  public function __construct() {

  }

  public function getData() {
    $types_settings = variable_get('recent_supporters_settings_types', array())
      + _recent_supporters_get_types_defaults($GLOBALS['language']->language);
    $saved_settings = recent_supporters_settings();
    $types = array();
    foreach ($types_settings as $type => $setting) {
      if ($setting['enabled']) {
        $types[] = $type;
      }
    }

    $backend = Loader::instance()->getBackend($saved_settings['backend']);
    $params = $backend->buildParams($saved_settings, NULL, $types);
    $return['recent_supporters']  = $backend->recentOnAllActions($params);
    return $return;
  }
}
