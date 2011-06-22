<?
$conn = mysql_connect('localhost', 'shop', '123p4ss');
mysql_select_db('shopguard');

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
      'since' => (time() - $row['timestamp'])
    );
  }
  return $current_status;
}

function changes_since($timestamp) {
print_r(time());
  if (!$timestamp) {
    $timetamp = time();
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
          timestamp
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
    if (0 == $row['direction']) {
      $ret['times'][$i]['end'] = $row['timestamp'];
      $ret['times'][$i]['duration'] = $ret['times'][$i]['start'] - $row['timestamp'];
      $i++;
    }
  }
     
  return $ret;
}

function head() {
  $out = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
  <html lang="hu">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/prototype/1.7.0.0/prototype.js"></script>
    <script type="text/javascript" src="application.js"></script>
    <link type="text/css" rel="stylesheet" href="application.css"> 
    </head><body><div id="main">';
  return $out;
}

function footer() {
  $out = '</div></body></html>';
  return $out;
}


function main() {
  $out = '';
  if (isset($_GET['sensor_id'])) {
    $data = sensor_data($_GET['sensor_id']);
  } else {
    $data = current_status();
    $out .= head();
//    print_r($data);
    $out .= '<div id="sensors">';
    foreach($data as $sensor_id => $sensor) {
      $out .= '<div class="sensor-button" id="sensor-'.$sensor_id.'" onclick="showSensor('.$sensor_id.')">';
      $out .= '<img src="green.png" width="50" height="50">';
      $out .= '</div>';
    }
    $out .= '</div><br style="clear: both"/><div id="detailed"></div>';
    $out .= footer();
  }
  echo $out;
}

main();
?>