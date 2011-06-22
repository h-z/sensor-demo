

/**
 * Creates AJAX request for one particular sensor data
 */
function showSensor(id, page) {
 new Ajax.Request('index.php', {
   method: 'POST',
   parameters: {sensor_id: id, page: page},
   onSuccess: function(transport) {
     $('detailed').innerHTML = transport.responseText;
   }
 });
}


/**
 * Starts periodical AJAX updater to refresh sensor's images
 */
function status() {
  
  new Ajax.Request('status.xml?'+parseInt(1000000*Math.random()), {
    method: 'GET',
    onSuccess: function(transport) {
      var childs = transport.responseXML.getElementsByTagName('UPRAISED')[0].childNodes
      var sensors = $('sensors');
      sensors.innerHTML = '';
      for (var i=0; i<childs.length; i++) {
        var c = childs[i];
        if (1 == c.nodeType) {
          var status = c.textContent=='ON'?1:0;
          var id = parseInt(c.tagName.replace(/Sensor/,''))
          sensors.insert('<div id="sensor-' + id + '" class="sensor-button" onclick="showSensor(' + id + ', 0)"><img src="' + (status?'green':'red') + '.png" width="50" height="50" alt="Sensor ' + id + '" title="Sensor ' + id + '"/></div>');
        }
      }
      setTimeout('status()', 5000);
    }
  });
}

Event.observe(window, 'load', function() {
  status();
});