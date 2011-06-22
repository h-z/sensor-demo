#!/usr/bin/ruby -w
require 'rubygems'

require 'nokogiri'


def sensor_xml sensors
  builder = Nokogiri::XML::Builder.new(:encoding => 'UTF-8') do |xml|
    xml.OCTOPUS {
      xml.UPRAISED {
        sensors.each_with_index do |s, i|
          xml.send('Sensor'+(i+1).to_s, (s==0)?'OFF':'ON')
        end
      }
    }
  end
  builder.to_xml
end


def random_action sensors
  change = rand(16)
  sensors[change] = (1 - sensors[change]).abs
  sensors
end

def main
 sensors = [0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0]
 while true
   sensors = random_action sensors
   xml = sensor_xml sensors
   File.open('status.xml', 'w') {|f| f.write(xml) }
   sleep 5
 end
end

main