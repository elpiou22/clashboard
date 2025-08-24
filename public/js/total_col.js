/**
 * Attaches an event listener to all `.input_more` elements to capture user input and update the corresponding sum for the player
 */
function initializeInputListeners() {
  document.querySelectorAll(".input_more").forEach((cell) => {
    cell.addEventListener("input", (e) => {
      let input_value = e.target.value;
      if (!input_value) {
        e.target.style.removeProperty("background-color");
        updateDaySum(e, 0, "event");
      } else if(isNaN(parseFloat(input_value))){
        e.target.style.backgroundColor = "#ff2e2e";
      } else {
        updateDaySum(e, parseFloat(input_value), "event");
        e.target.style.removeProperty("background-color");
      }
    });
    updateDaySum(cell, 0, "cell");
  });
}

/**
 * Rounds a number to a specific number of decimal places.
 * @param {number} value - The number to round.
 * @param {number} decimals - The number of decimal places to keep.
 * @returns {number} - The rounded number.
 */
function roundToDecimals(value, decimals) {
  let factor = Math.pow(10, decimals);
  return Math.round(value * factor) / factor;
}


/**
 * Updates the total sum of all days in a row and displays it in the `.sum_days` cell.
 * @param {Event | HTMLElement} e - The event object or the target element that triggered the update.
 * @param {number} input_value - The value to add to the total sum.
 * @param {string} mode - The mode of the operation: either "event" for dynamic input or "cell" for initialization.
 */
function updateDaySum(e, input_value, mode) {
  let row;
  let total_cell;
  if (mode === "event"){
    row = e.target.closest("tr");
    total_cell = e.target.closest("tr").querySelector(".sum_days");
  } else {
    row = e.closest("tr");
    total_cell = e.closest("tr").querySelector(".sum_days");
  }

  let sum = 0;
  let nbdays = 0;
  row.querySelectorAll("td").forEach((cell) => {
    if ((cell.classList).contains("td_value")) {
      let data = cell.querySelector(".result_day_data");

      if (data !== null) {
        nbdays ++;
        let day_value = data.value;
        if (day_value !== "?"){
          let data_number = parseFloat(day_value)
          sum = roundToDecimals(data_number + sum,3);
        }
      }
    }
  });
  sum += input_value;
  if (nbdays !== 0) {
    //console.log(sum + " / " + nbdays); // -> 3 / 4
    //console.log(sum / nbdays);        // -> 0.75
    total_cell.textContent = roundToDecimals(sum / nbdays * 100, 1) + "%"; // -> 1%
  } else {
    total_cell.textContent = "0%";
  }

}



function editMode_checkbox(){
  if (checked){

  }
}




initializeInputListeners();

