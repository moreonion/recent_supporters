<?php

namespace Drupal\recent_supporters_webform;

use Drupal\recent_supporters\BackendBase;
use Drupal\recent_supporters\RequestParams;

/**
 * Backend plugin that loads data from the {recent_supporters_webform} table.
 */
class WebformBackend extends BackendBase {

  /**
   * Get label of this plugin for the admin interface.
   */
  public static function label() {
    return t('Webform submissions');
  }

  /**
   * Get recent supporters for one action and its translations.
   *
   * @param \Drupal\recent_supporters\RequestParams $params
   *   Parameters for the query.
   *
   * @return string[][]
   *   Array of data about recent supporters. Each entry is an associative array
   *   containing the data of one supporter.
   */
  public function recentOnOneAction(RequestParams $params) {
    $config = $params->getParams();
    $comment_field = $config['comment_toggle'] ? ', w.comment' : '';
    $sql = <<<SQL
SELECT n.nid, n.tnid, w.first_name, w.last_name, w.timestamp, w.country$comment_field
FROM {recent_supporters_webform} w
  INNER JOIN {node} n USING(nid)
WHERE n.status = 1
    AND (n.nid = :nid OR n.nid IN (SELECT tn.nid FROM {node} tn INNER JOIN {node} n USING(tnid) WHERE n.nid = :nid AND n.tnid != 0))
ORDER BY w.timestamp DESC
SQL;
    $result = db_query_range($sql, 0, $config['limit'], array(':nid' => $config['nid']));
    return $this->buildSupporterList($result, $config['name_display']);
  }

  /**
   * Get recent supporters for all live actions in this installation.
   *
   * @param \Drupal\recent_supporters\RequestParams $params
   *   Parameters for the query.
   *
   * @return string[][]
   *   Array of data about recent supporters. Each entry is an associative array
   *   containing the data of one supporter.
   */
  public function recentOnAllActions(RequestParams $params) {
    $config = $params->getParams();
    if (!$config['types']) {
      return [];
    }
    $comment_field = $config['comment_toggle'] ? ', w.comment' : '';
    // Get translated node data in the following order of precendence:
    // 1. nt - A translation of the webform submission node to the current
    //         user’s language.
    // 2. no - The translation source of the submission node.
    // 3. na - The node of the webform submission.
    $sql = <<<SQL
SELECT w.first_name, w.last_name, w.timestamp, w.country$comment_field,
  na.nid, na.tnid, na.type AS action_type, na.status,
  COALESCE(nt.title, no.title, na.title) AS action_title,
  COALESCE(nt.tnid, no.tnid, na.nid) AS action_nid,
  COALESCE(nt.language, no.language, na.language) AS action_lang
FROM {recent_supporters_webform} w
  INNER JOIN {node} na ON w.nid = na.nid
  LEFT OUTER JOIN {node} nt ON na.tnid != 0 AND nt.tnid = na.tnid AND nt.language = :lang AND nt.status>0
  LEFT OUTER JOIN {node} no ON na.tnid = no.nid AND no.status>0
WHERE na.status = 1 AND na.type IN (:types)
  ORDER BY w.timestamp DESC
SQL;
    $result = db_query_range($sql, 0, $config['limit'], array(':types' => $config['types'], ':lang' => $config['lang']));
    return $this->buildSupporterList($result, $config['name_display']);
  }

}
