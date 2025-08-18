const {parseDataAndExportToExcel} = require("./parseData");
module.exports = {
  get_data_parsed,
};


async function get_data_parsed(data, clanID, rules) {
  let dataToExcel;
  let membersInCWL;
  let clanName;

  if (data) {
    let currentData = await parseDataAndExportToExcel(data, clanID, rules);
    dataToExcel = currentData[0];
    membersInCWL = currentData[1];
    clanName = currentData[2];
    return [dataToExcel, membersInCWL, clanName];
  }
}