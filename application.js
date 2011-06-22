function showSensor(id, page) {
 new Ajax.Request('index.php', {
   method: 'POST',
   parameters: {sensor_id: id, page: page},
   onSuccess: function(transport) {
     $('detailed').innerHTML = transport.responseText;
   }
 });
}