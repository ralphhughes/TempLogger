# TempLogger
Temperature &amp; humidity logger for Raspberry Pi (SQLite backend, javascript charting front end)


## Hardware setup
Connect one of more DS18B20 sensors, or DHT22/AM2302 sensors to the GPIO pins on the pi. Make a note of the pins you connected them to.

## Installation instructions

###### Install dependencies
`sudo apt-get install git nginx php5-fpm sqlite3 php5-sqlite`


###### Setup nginx
[https://www.raspberrypi.org/documentation/remote-access/web-server/nginx.md](Follow this raspberry pi official doc)

`sudo nano /etc/nginx/sites-enabled/default`
Add `index.php` after `index`
Uncomment `location .php {` section

###### Install code
`cd /var/www`

`git clone https://github.com/ralphhughes/TempLogger.git`


###### Browse to the web interface

Fire up your browser and point it at `http://ip-address-of-pi/SnugPi`
