const SHT31 = require(`sht31`);

// NOTE: you might need to change the I2C bus number or
// SHT31 address.
// In this case, SHT31 address is 0x44
// and I2C bus number is 1
const sht31 = new SHT31(0x44, 1);


function intervalFunc() {
    sht31.readSensorData().then((data) => {
        console.log(new Date() + "\t" + data.temperature + "\t" + data.humidity);
    })
    .catch((err) => {
	console.log(err);
    });
}

sht31.init().then(
    setInterval(intervalFunc,5000)
);
