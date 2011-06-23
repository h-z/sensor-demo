<?
$conn = mysql_connect('localhost', 'shop', '123p4ss');
mysql_select_db('shop');

/**
 * Gets the sensors' current statuses
 */
function current_status() {
  $q = "SELECT
        u1.sensor_id,
        u1.direction,
        u1.timestamp
      FROM
       upraised u1
       JOIN (
         SELECT
           sensor_id,
           MAX(timestamp) AS ts
         FROM
           upraised
         GROUP BY
           sensor_id) u2 ON u1.sensor_id = u2.sensor_id AND u1.timestamp = u2.ts";
  $res = mysql_query($q);
  $current_status = array();
  while ($row = mysql_fetch_assoc($res)) {
    $current_status[$row['sensor_id']] = array(
      'sensor_id' => $row['sensor_id'],
      'direction' => $row['direction'],
      'duration' => (time() - $row['timestamp'])
    );
  }
  return $current_status;
}

/**
 * Shows the changes since the timestamp
 */
function changes_since($timestamp) {
print_r(time());
  if (!$timestamp) {
    $timestamp = time();
  }
  $ret = array();
  $q = "SELECT 
          *
        FROM
          upraised
        WHERE
          timestamp > ".(int)$timestamp."
      ";
  $res = mysql_query($q);
  while ($row = mysql_fetch_assoc($res)) {
    $ret[$row['sensor_id']] = $row;
    $ret[$row['sensor_id']]['since'] =  (time() - $row['timestamp']);
    $ret[$row['sensor_id']]['ts'] =  time();
    
 }
  
  return $ret;
}

/**
 * Gets one sensor's data
 * Also creates some aggregated data
 */
function sensor_data($sensor_id) {
  if (!$sensor_id) {
    return;
  }
  $ret = array();
  $q = "SELECT
          *
        FROM 
          upraised
        WHERE
          sensor_id = ".(int)$sensor_id."
        ORDER BY
          timestamp DESC
  ";
  $res = mysql_query($q);
  $ret['history'] = array();
  $ret['current'] = array();
  $i = 0;
  while ($row = mysql_fetch_assoc($res)) {
    $ret['history'][$row['id']] = $row;
    $ret['current']['direction'] = $row['direction'];
    if (1 == $row['direction']) {
      $ret['times'][$i]['start'] = $row['timestamp'];
      $ret['times'][$i]['formatted_start'] = date('Y-m-d H:i:s', $row['timestamp']);
    }
    if (0 == $row['direction'] && isset($ret['times'][$i]['start'])) {
      $ret['times'][$i]['end'] = $row['timestamp'];
      $ret['times'][$i]['duration'] = $ret['times'][$i]['start'] - $row['timestamp'];
      $i++;
    }
  }
  return $ret;
}

/**
 * HTML header
 * only for normal requests (not for AJAX)
 */
function head() {
  $out = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
  <html lang="hu">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js"></script>
    <script type="text/javascript" src="application.js"></script>
    <link type="text/css" rel="stylesheet" href="application.css"> 
    <title>Demo</title>
    </head><body><div id="main">';
  return $out;
}

/**
 * HTML footer
 * only for normal requests (not for AJAX)
 */
function footer() {
  $out = '</div></body></html>';
  return $out;
}

/**
 * Time formatter
 */
function format_time($sec) {
  $out = array();
  
  if ($sec > 3600) {
    $out[] = ((int)($sec/3600)).' órára';
    $sec -= 3600*((int)($sec/3600));
  }
  if ($sec > 60) {
    $out[] = ((int)($sec/60)).' percre';
    $sec -= 60*((int)($sec/60));
  }
  $out[] = $sec. ' másodpercre ';
  
  return implode(', ', $out);
}

/**
 * Creates pager div if data is lomger than pagesize
 */
function pager($sensor_id, $max_pages, $current_page) {
  $out = '';
  
  $rows_per_page = 10;
  if ($max_pages > $rows_per_page) { 
    $out .= '<div class="pager">';
    if (0 != $current_page) {
      $out .= '<a href="javascript:void(0);" onclick="showSensor('.(int)$sensor_id.', '.($current_page-1).');">&lt;&lt;</a>';
    } else {
      $out .= '&nbsp;&nbsp;&nbsp;';
    }
    $out .= '('.($current_page+1).')';
    if (($max_pages / $rows_per_page) > $current_page+1) {
      $out .= '<a href="javascript:void(0);" onclick="showSensor('.(int)$sensor_id.', '.($current_page+1).');">&gt;&gt;</a>';
    } else {
      $out .= '&nbsp;&nbsp;&nbsp;';
    }
    $out .= '</div>';
  }
  return $out;
}




/**
 * Main function
 * handles GET parameters
 * handles normal and AJAX requests
 */
function main() {
  $out = '';
  if (isset($_POST['sensor_id'])) {
    $sensor_id = $_POST['sensor_id'];
    $data = sensor_data($sensor_id);
    if (!isset($_POST['page'])) {
      $page = 0;
    } else {
      $page = $_POST['page'];
    }
    $out .= '<h2>Sensor '.$sensor_id.'</h2>';
    $sum_time = 0;
    if (!empty($data['times'])) {
      foreach($data['times'] as $t) {
        $sum_time += $t['duration'];
      }
    }
    $out .= '<h3>';
    $out .= 'Összesen '.count($data['times']). ' alkalommal, '.format_time($sum_time).' vették fel a szenzort.';
    $out .= '</h3>';
    $out .= pager($sensor_id, count($data['times']), $page);
    $rows_per_page = 10;
    for($i = $page * $rows_per_page; $i < ($page+1) * $rows_per_page; $i++) {
      if (isset($data['times'][$i])) {
        $out .= '<div class="detail-row">';
        $out .= '<span>'.$data['times'][$i]['formatted_start'].'</span>-kor felvették <span title="'.$data['times'][$i]['duration'].' másodperc">'.format_time($data['times'][$i]['duration']).'</span>';
        $out .= '</div>';
        $out .= "\n";
      }
    }
    $out .= pager($sensor_id, count($data['times']), $page);
  } else {
    $data = current_status();
    $out .= head();
    $out .= "\n";
    $out .= '<div id="sensors">';
    $out .= "\n";
    foreach($data as $sensor_id => $sensor) {
      $out .= '<div class="sensor-button" id="sensor-'.$sensor_id.'" onclick="showSensor('.$sensor_id.', 0)">';
      $out .= "\n";
      $out .= '<img src="'.(0 == $sensor['direction'] ? 'red':'green').'.png" width="50" height="50" alt="Sensor '.$sensor_id.'" title="Sensor '.$sensor_id.'">';
      $out .= "\n";
      $out .= '</div>';
      $out .= "\n";
    }
    $out .= '</div><br style="clear: both"/><div id="detailed"><div style="text-align:center">Válasszon a fenti szenzorok közül!</div></div>';
    $out .= "\n";
    $out .= footer();
  }
  return $out;
}

print main();

?>