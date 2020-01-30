import smbus
import time

import sqlite3
from sqlite3 import Error

def create_connection(db_file):
    """ create a database connection to the SQLite database
        specified by db_file
    :param db_file: database file
    :return: Connection object or None
    """
    conn = None
    try:
        conn = sqlite3.connect(db_file)
    except Error as e:
        print(e)

    return conn

def logValueToDB(conn, values):
    sql = ''' INSERT INTO temps (timestamp, sensor, value) VALUES (datetime(), ?, ? ) '''
    cur = conn.cursor()
    cur.execute(sql, values)
    return cur.lastrowid

def main():
    database = r"/var/www/TempLogger/database/myDB.sqlite"

    # create a database connection
    conn = create_connection(database)
    with conn:
        print("Connected to SQLite database OK...")
        shtVals = readSHT();

        tempValues=('SHT_Temp',shtVals[0]);
        logValueToDB(conn, tempValues);

        humidityValues=('SHT_Humidity', shtVals[1]);
        logValueToDB(conn, humidityValues);


def readSHT():
    # Get I2C bus
    bus = smbus.SMBus(1)

    # SHT31 address, 0x44(68)
    bus.write_i2c_block_data(0x44, 0x2C, [0x06])

    time.sleep(0.5)

    # SHT31 address, 0x44(68)
    # Read data back from 0x00(00), 6 bytes
    # Temp MSB, Temp LSB, Temp CRC, Humididty MSB, Humidity LSB, Humidity CRC
    data = bus.read_i2c_block_data(0x44, 0x00, 6)

    # Convert the data
    temp = data[0] * 256 + data[1]
    cTemp = -45 + (175 * temp / 65535.0)
    humidity = 100 * (data[3] * 256 + data[4]) / 65535.0

    # Output data to screen
    print ("Temperature in Celsius is : %.2f C" %cTemp)
    print ("Relative Humidity is : %.2f %%RH" %humidity)

    return (cTemp, humidity);

if __name__ == '__main__':
    main()


