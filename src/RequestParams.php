<?php

namespace Drupal\recent_supporters;

class RequestParams {
  protected $params;

  public function __construct(array $params) {
    $this->params = $params + array(
      'limit' => 10,
      'name_display' => RECENT_SUPPORTERS_NAME_DISPLAY_DEFAULT,
      'comment_toggle' => FALSE,
      'hash' => '',
    );
  }

  public function getParams() {
    return $this->params;
  }

  public function getBackend() {
    $class = $this->params['backend'];
    return new $class();
  }
}
