let lastRowIndex = null;
let lastCellIndex = null;

/**
 * Stock les index de la case que l'utilisateur focus
 */
function get_cell_indexes() {
  document.querySelectorAll("#cwl_table td").forEach((cell) => {
    cell.addEventListener("mouseover", (event) => {
      const td = event.target.closest("td");
      if (td) {
        lastRowIndex = td.parentElement.rowIndex;
        lastCellIndex  = td.cellIndex;
        //console.log(lastRowIndex, lastCellIndex);
      }
    });
  });
}


/**
 * Récupère le bouton d'envoi et lui ajoute un event qui changera l'utilisateur de page
 */
function find_send_button() {
  document.getElementById("sendButton").addEventListener("click", () => {
    if (lastRowIndex !== null && lastCellIndex !== null) {
      if (lastRowIndex <10){
        lastRowIndex = "0" + lastRowIndex;
      }

      if ((clanId[0]) === "#"){
        clanId = clanId.slice(1);
      }

      window.location.href = `/forum/${clanId}/2501${lastRowIndex}${lastCellIndex-1}`;
    }
  });
}

get_cell_indexes();
find_send_button();
