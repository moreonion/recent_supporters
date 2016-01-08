<?php

/**
 * Implements hook_entity_info_alter().
 *
 * Recent supporters lets you piggyback the information for its global block
 * onto the entity bundle information. So to pass that information you must
 * implement hook_entity_info_alter().
 */
function recent_supporters_entity_info_alter(&$info) {
  $info['node']['bundles']['webform']['recent_supporters'] = [
    'default_text' => t('!supporter_name just signed up at !action_title'),
  ];
}
