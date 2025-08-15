
const { get_data_parsed } = require('./main_parse_data.js');
const { get_request_data } = require('./main_request.js');
const { write } = require('fs');
const fs = require('fs');
const { writeInFile } = require('./requests.js');

////////////////////////////////////////////
// Clan tags
let hashtag     = "%23";
let apo         = hashtag + "P990YPPV";
let hib         = hashtag + "2GQPVRC8J";
////////////////////////////////////////////
// variable to edit
let clanTocheck = apo;
let excelPath   = "../export/v0.xlsx";
////////////////////////////////////////////



async function run(clanID, rules) {
  try {
    if (clanID[0] === "#") {
      clanID = "%23" + clanID.slice(1);
    } else {
      clanID = "%23" + clanID;
    }

    let data;
    //let path = "../json/2503_apo.json";
    let path = "../ExcelApoloBot-main/json/2501_apo.json"
    if (fs.existsSync(path)) {
      const fileData = fs.readFileSync(path, 'utf-8');
      data = JSON.parse(fileData);
    } else {
      data = await get_request_data(path, clanID);
      writeInFile(data, path);
    }

    let data_parsed = await get_data_parsed(data, clanID, rules);
    let dataToExcel = data_parsed[0];
    let membersInCWL = data_parsed[1];

    process.stdout.write(JSON.stringify([membersInCWL, dataToExcel]));
  } catch (error) {
    process.stdout.write(JSON.stringify("Error, in script app.js" + error));
  }
}

const args = process.argv.slice(2);
if (args.length > 2) {
  process.stdout.write(JSON.stringify("Error, too many arguments"));
} else if (args.length === 0) {
  process.stdout.write(JSON.stringify("Error, 0 argument given"));
}
/*
else if (args[0].length > 9 || args[0].length < 8) {
    process.stdout.write(JSON.stringify("Error, Clan ID incorrect"));
} else if (args[0].length === 9 && args[0][0] !== "#") {
    process.stdout.write(JSON.stringify("Error, Clan ID incorrect"));
} else if (args[0].length === 8 && args[0][0] === "#") {
    process.stdout.write(JSON.stringify("Error, Clan ID incorrect"));
}
*/
else {
  run(args[0], args[1]);
}
