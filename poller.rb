#!/usr/bin/ruby -w
require 'rubygems'

require 'nokogiri'
require 'mysql2'

# Reads the XML file, inserts changes into database
#
def poller 
  #mysql connection
  db = Mysql2::Client.new(:host => 'localhost', :username => 'shop', :password => '123p4ss', :database => 'shop')
  f = File.open('status.xml')
  # XML object
  doc = Nokogiri::XML(f)
  f.close
  # parsing XML file
  sensors = doc.xpath('//UPRAISED/*')
  #one timestamp for every change in this iteration
  t = Time.new.to_i.to_s
  
  q = "SELECT 
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
             sensor_id) u2 ON u1.sensor_id = u2.sensor_id AND u1.timestamp = u2.ts"
             
  res = db.query(q)
  # previous states from the database
  previous_sensors = []
  res.each { |r|
    previous_sensors << r
  }
  sensors.each do |s|
    # array of strings
    data = [s.name.gsub(/Sensor/, ''), s.text=='OFF'?'0':'1', t]
    # from the given data only the new ones should be inserted
    if previous_sensors.detect { |ps| 
      ps['sensor_id'].to_s == s.name.gsub(/Sensor/, '') and ps['direction'].to_s != data[1] 
    }
      q = "INSERT INTO upraised (sensor_id, direction, timestamp) VALUES (" + data[0] + ", " + data[1] + ", " + data[2] + ")"
      db.query(q)
   end
  end
  sensors
end

# Main loop for polling the XML file into the database
#
def main
  sensor_data = nil
  while true
   sensor_data = poller
   #sleep 2 seconds
   sleep 2
  end
end

main
