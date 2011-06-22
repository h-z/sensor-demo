#!/usr/bin/ruby -w
require 'rubygems'

require 'nokogiri'
require 'mysql2'

def poller 
  db = Mysql2::Client.new(:host => 'localhost', :username => 'shop', :password => '123p4ss', :database => 'shopguard')
  f = File.open('status.xml')
  doc = Nokogiri::XML(f)
  f.close
  sensors = doc.xpath('//UPRAISED/*')
  t = Time.new.to_i.to_s
  
  q = "SELECT * FROM upraised WHERE timestamp = (SELECT MAX(timestamp) AS timestamp FROM upraised)"
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
  previous_sensors = []
  res.each { |r|
    previous_sensors << r
  }
  sensors.each do |s|
    data = [s.name.gsub(/Sensor/, ''), s.text=='OFF'?'0':'1', t]
    if previous_sensors.detect { |ps| 
      ps['sensor_id'].to_s == s.name.gsub(/Sensor/, '') and ps['direction'].to_s != data[1] 
    }
      q = "INSERT INTO upraised (sensor_id, direction, timestamp) VALUES (" + data[0] + ", " + data[1] + ", " + data[2] + ")"
      db.query(q)
   end
  end
  sensors
end


def main
  sensor_data = nil
  while true
   sensor_data = poller
   sleep 2
  end
end

main
