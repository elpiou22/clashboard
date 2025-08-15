const fs = require('fs').promises;
const {getDataRequest} = require("./requests.js");

module.exports = {
  get_request_data,
};




// temp
async function get_request_data(path, tag) {
  try {
    //const data = await fs.readFile(path, 'utf8');
    const data = await getDataRequest(tag);
    return data;
  } catch (err) {
    console.error("Error reading file:", err);
    return null;
  }
}
