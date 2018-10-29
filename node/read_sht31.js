const SHT31 = require(`sht31`);

// NOTE: you might need to change the I2C bus number or
// SHT31 address.
// In this case, SHT31 address is 0x44
// and I2C bus number is 1
const sht31 = new SHT31(0x44, 1); 

sht31
  .init()
  .then(() => sht31.readSensorData())
  .then((data) => {
    // data object follows this format:
    // { temperature: Number, humidity: Number }
    // temperature is in celcius unit.
    console.log(data);
  })
  .catch((err) => {
    // Handle error here
    // ...
  });
