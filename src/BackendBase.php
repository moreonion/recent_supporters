<?php

namespace Drupal\recent_supporters;

/**
 * Loader for recent supporters.
 */
abstract class BackendBase {
  /**
   * Print an empty supporters list.
   */
  public function emptyJson() {
    $this->send(array('supporters' => array()));
  }

  public function buildParams($options, $node = NULL, $types = NULL) {
    $config = array(
      'backend' => get_called_class(),
      'limit' => $options['query_limit'],
      'name_display' => $options['name_display'],
      'comment_toggle' => $options['comment_toggle'],
    );
    if ($node) {
      return new RequestParams($config + array('nid' => $node->nid));
    }
    else {
      return new RequestParams(array(
        'types' => (array) $types,
        'lang' => $GLOBALS['language']->language,
      ) + $config);
    }
  }

  function buildSupporterList($result, $name_display) {
    $supporters = array();

    // resolve "default" name display
    if ((int)$name_display === RECENT_SUPPORTERS_NAME_DISPLAY_DEFAULT) {
      $name_display_default = variable_get('recent_supporters_name_display_default', RECENT_SUPPORTERS_NAME_DISPLAY_INITIAL);
      // if $name_display_default is still RECENT_SUPPORTERS_NAME_DISPLAY_DEFAULT
      // (e.g. it was explicitly set in the variable) we override it manually, as
      // 'default' would make no sense here any more
      if ($name_display_default === RECENT_SUPPORTERS_NAME_DISPLAY_DEFAULT) {
        $name_display_default = RECENT_SUPPORTERS_NAME_DISPLAY_INITIAL;
      }

      $name_display = $name_display_default;
    }

    foreach ($result as $item) {
      $supporter = (array) $item;

      // no RECENT_SUPPORTERS_NAME_DISPLAY_DEFAULT any more in $name_display
      switch ($name_display) {
        case RECENT_SUPPORTERS_NAME_DISPLAY_FIRST_ONLY:
          // set last_name to empty string
          $supporter['last_name'] = "";
          break;
        case RECENT_SUPPORTERS_NAME_DISPLAY_INITIAL:
          // substitute last_name with initial
          // convert every component of last_name to it's first letter
          // also last names with van, von, de, ... and those consisting
          // of two words
          // @TODO with hyphens Last-Name
          if (!empty($supporter['last_name'])) {
            $ln_array = preg_split("/ +/", $supporter['last_name']);
            $supporter['last_name'] = implode(' ', array_map('_recent_supporters_strip_callback', $ln_array));
          }
          break;
        case RECENT_SUPPORTERS_NAME_DISPLAY_FULL:
        default:
          // nothing to do (full name loaded already
          break;
      }

      $supporter['rfc8601']    = date('c', $item->timestamp);
      if (isset($supporter['action_nid'])) {
        $supporter['action_url']   = $GLOBALS['base_url'] . '/node/' . $supporter['action_nid'];
      }

      $supporter['first_name'] = check_plain($supporter['first_name']);
      $supporter['last_name'] = check_plain($supporter['last_name']);
      $supporter['comment'] = isset($supporter['comment']) ? check_plain($supporter['comment']) : '';
      $supporters[] = $supporter;
    }

    return $supporters;
  }
}
