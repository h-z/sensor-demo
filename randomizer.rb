#!/usr/bin/ruby -w
require 'rubygems'

require 'nokogiri'

# Creates new XML file based on sensor states
#
def sensor_xml sensors
  builder = Nokogiri::XML::Builder.new(:encoding => 'UTF-8') do |xml|
    xml.OCTOPUS {
      xml.UPRAISED {
        # creating sensor nodes
        sensors.each_with_index do |s, i|
          # sending message to xml object
          xml.send('Sensor'+(i+1).to_s, (s==0)?'OFF':'ON')
        end
      }
      xml.SET {
        sensors.each_with_index { |s, i| xml.send('Sensor'+(i+1).to_s, 'ON') }
      }
      xml.ALARM {
        sensors.each_with_index { |s, i| xml.send('Sensor'+(i+1).to_s, 'OFF') }
      }
    }
  end
  # returns XML object's string
  builder.to_xml
end

# Adds some randomness into the sensors' data
#
def random_action sensors
  # only one of the sensors should change
  change = rand(16)
  sensors[change] = (1 - sensors[change]).abs
  sensors
end

# Main loop for creating random XML files
#
def main
 # initial vector for sensors
 sensors = []
 # all put down
 16.times { sensors << 0 }
 while true
   # a random action
   sensors = random_action sensors
   # xml variable to make filewriting quicker
   xml = sensor_xml(sensors)
   File.open('status.xml', 'w') {|f| 
     # only one instruction inside of file operations
     f.write(xml) 
   }
   #sensor data varies by random intervals
   sleep rand(15)+2
 end
end

#main loop started
main