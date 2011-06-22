#!/usr/bin/ruby -w
require 'rubygems'

require 'nokogiri'
require 'mysql2'

def poller 
#  db = SQLite3::Database.new "shopguard.db"
  db = Mysql2::Client.new(:host => 'localhost', :username => 'shop', :password => '123p4ss', :database => 'shopguard')
  f = File.open('status.xml')
  doc = Nokogiri::XML(f)
  f.close
  sensors = doc.xpath('//UPRAISED/*')
  t = Time.now.to_i.to_s
  
  q = "SELECT * FROM upraised WHERE timestamp = (SELECT MAX(timestamp) AS timestamp FROM upraised)"
  res = db.query(q)
  previous_sensors = []
  res.each { |r|
    puts r.inspect
    previous_sensors << r
  }
#  previous_sensors = res.first
#  puts previous_sensors.inspect
  sensors.each do |s|
    data = [s.name.gsub(/Sensor/, ''), s.text=='OFF'?'0':'1', t]
    if previous_sensors.any? { |ps| ps[:sensor_id].to_s == s.name.gsub(/Sensor/, '') and ps[:direction].to_s != data[1] }
      puts 'difi:'
#      puts ps[:sensor_id].to_s.inspect
      puts s.name.gsub(/Sensor/, '').inspect
 #     puts ps[:direction].to_s.inpsect
      puts data[1].inspect
      q = "INSERT INTO upraised (sensor_id, direction, timestamp) VALUES (" + data[0] + ", " + data[1] + ", " + data[2] + ")"
      db.query(q)
   end
  end
  sensors
end

def db_init
  db = SQLite3::Database.new "shopguard.db"
  # Create a database
  rows = db.execute <<-SQL
    create table upraised (
      id INTEGER PRIMARY KEY,
      sensor_id INTEGER,
      direction INTEGER,
      timestamp INTEGER
    );
  SQL
end

def main
  sensor_data = nil
  while true
   sensor_data = poller
   sleep 2
  end
end

main
