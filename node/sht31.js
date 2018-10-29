const SHT31 = require(`sht31`);

// NOTE: you might need to change the I2C bus number or
// SHT31 address.
// In this case, SHT31 address is 0x44
// and I2C bus number is 1
const sht31 = new SHT31(0x44, 1);

const sqlite3 = require('sqlite3').verbose();

let db = new sqlite3.Database('../database/myDB.sqlite');


function intervalFunc() {
    sht31.readSensorData().then((data) => {
        console.log(new Date() + "\t" + data.temperature + "\t" + data.humidity);
db.run(`INSERT INTO temps(timestamp, sensor, value)
VALUES(?,?,?)`, [new Date(),'SHT31_humidity',data.humidity],
function(err) {
    if (err) {
      return console.log(err.message);
    }
});
// db.close();

    })
    .catch((err) => {
	console.log(err);
    });
}

sht31.init().then(
    setInterval(intervalFunc,300000)
);

