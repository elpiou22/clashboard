const { startBot } = require('./bot.js');
const { getDataRequest, writeInFile } = require('./requests.js');

const { parseDataAndExportToExcel } = require('./parseData.js');

const {
    createExcel, 
} = require('./excel.js');

const { write } = require('fs');


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



let dataToExcel;
let membersInCWL;

async function run(clanID) {
    //console.log("Bien spécifier le clan a lire dans: ./js/main.js");
    //console.log("Bien spécifier les tokens ClashApi et DiscordApi dans: ./json/config.json");
    //console.log("Début du programme:");
    try {
        if (clanID[0] === "#") {
            clanID = "%23" + clanID.slice(1);
        } else {
            clanID = "%23" + clanID;
        }

        /*




        let dataRequest = await getDataRequest(clanID);
        writeInFile(dataRequest, "../json/2502_apo.json");
        let currentData = await parseDataAndExportToExcel(dataRequest, clanID);
        dataToExcel  = currentData[0];
        membersInCWL = currentData[1];

        //writeInFile(dataToExcel, "../json/dataExcel.json");
        //writeInFile(membersInCWL, "../json/membersCWL.json");
        //createExcel(dataToExcel, membersInCWL, excelPath);
        process.stdout.write(JSON.stringify([membersInCWL, dataToExcel]));



         */


        const fs = require('fs');
        fs.readFile('../ExcelApoloBot-main/json/2501_apo.json', 'utf8', async (err, data) => {
        //fs.readFile('../json/2501_apo.json', 'utf8', async (err, data) => {
            if (data) {

                let currentData = await parseDataAndExportToExcel(JSON.parse(data), clanID);
                dataToExcel = currentData[0];
                membersInCWL = currentData[1];

                //createExcel(dataToExcel, membersInCWL, excelPath);
                process.stdout.write(JSON.stringify([membersInCWL, dataToExcel]));
            } else {
                console.log(err)
            }
        });

    


        
    } catch (error) {
        process.stdout.write(JSON.stringify("Error, in script main.js" + error));
    }
}

const args = process.argv.slice(2);
if (args.length > 1) {
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
    run(args[0]);
}
