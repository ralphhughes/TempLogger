const sqlite3 = require('sqlite3').verbose();

let db = new sqlite3.Database('../database/myDB.sqlite');

// insert one row into the langs table
db.run(`INSERT INTO temps(timestamp, sensor, value) 
VALUES(?,?,?)`, ['timestamp','SHT31',36.25789123], 
function(err) {
    if (err) {
      return console.log(err.message);
    }
    // get the last insert id
    console.log(`A row has been inserted with rowid ${this.lastID}`);
});

// close the database connection
db.close();
