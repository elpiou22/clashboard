/**
 * Show th regarding its index
 * @param index
 */
function show_th(index) {
  if (index === 10 || index === 11) {
    let table = document.getElementById('cwl_table');
    for (let row of table.rows) {
      if (row.cells[index - 1]) {
        //console.log(row.cells[index - 1].style.display)
        row.cells[index - 1].style.display = "table-cell";
      }
    }
  }
}


/**
 * Hide th regarding its index
 * @param index
 */
function hide_th(index) {
  if (index === 10 || index === 11) {
    let table = document.getElementById('cwl_table');
    for (let row of table.rows) {
      if (row.cells[index - 1]) {
        //console.log(row.cells[index - 1].style.display)
        row.cells[index - 1].style.display = 'none';
      }
    }
  }
}


// 11/08/2025 - permet d'afficher la colonne total par default
options_checkbox_changed("total")

/**
 * Hide and show column by its name
 * @param checkbox_name name of the column
 */
function options_checkbox_changed(checkbox_name) {
  let checkbox_edit = document.getElementById('options_col');
  let checkbox_total = document.getElementById('total_col');
  let checkbox_editMode = document.getElementById('edit_mode');
  let save_button = document.getElementById('saveButton');
  const editableFields = document.querySelectorAll('.result_day_data');

  if (checkbox_name === 'options') {
    if (checkbox_edit.checked) {
      //console.log('Edit mode enabled');
      show_th(10);

    } else {
      //console.log('Edit mode disabled');
      hide_th(10);
    }
  } else if (checkbox_name === 'total'){
    if (checkbox_total.checked) {
      //console.log('Total mode enabled');
      show_th(11);
    } else {
      //console.log('Total mode disabled');
      hide_th(11);
    }
  } else if (checkbox_name === 'edit_mode') {
    // @todo empecher le bouton de disparaitre si des données ne sont pas sauvegardées
    let container = checkbox_editMode.closest(".options_container");
    if (checkbox_editMode.checked) {
      container.style.backgroundColor = "#8a1c1c";
      save_button.style.display = 'block';
      editableFields.forEach(function(field) {
        field.disabled = false;
      });
    } else {

      container.style.removeProperty('background-color');
      save_button.style.display = 'none';
      editableFields.forEach(function(field) {
        field.disabled = true;
      });
    }
  }
}

(function () {
  const ok = document.getElementById('statusOkButton');
  const pop = document.getElementById('statusPopup');
  const bd  = document.getElementById('statusBackdrop');
  function close() { pop?.remove(); bd?.remove(); }
  ok?.addEventListener('click', close);
  document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });
  setTimeout(() => ok?.focus(), 0);
})();

