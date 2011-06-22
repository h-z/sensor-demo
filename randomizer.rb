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
 sensors = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
 while true
   sensors = random_action sensors
   # xml variable to make filewriting quicker
   xml = sensor_xml(sensors)
   File.open('status.xml', 'w') {|f| 
     # only one instruction inside of file operations
     f.write(xml) 
   }
   #sensor data varies by random intervals
   sleep rand(15)
 end
end

#main loop started
main