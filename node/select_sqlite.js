const sqlite3 = require('sqlite3').verbose();

// open the database
let db = new sqlite3.Database('../database/myDB.sqlite', sqlite3.OPEN_READWRITE, (err) => {
  if (err) {
    console.error(err.message);
  }
  console.log('Connected to the database.');
});

db.serialize(() => {
  db.each(`SELECT *
           FROM temps limit 10`, (err, row) => {
    if (err) {
      console.error(err.message);
    }
    console.log(row.id + "\t" + row.timestamp + "\t" + row.sensor + "\t" + row.value);
  });
});

db.close((err) => {
  if (err) {
    console.error(err.message);
  }
  console.log('Close the database connection.');
});
