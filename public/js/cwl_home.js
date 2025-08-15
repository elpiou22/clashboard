/**
 * Permet de changer la classe de la result box (pour l'affichage de la couleur)
 * @param select
 */
function applySelectClass(select) {
  select.classList.remove(
      'btn_value_none',
      'btn_value_0',
      'btn_value_1',
      'btn_value_-1',
      'btn_value_11'
  );

  switch (select.value) {
    case " ":
      select.classList.add('btn_value_none');
      break;
    case "0":
      select.classList.add('btn_value_0');
      break;
    case "1":
      select.classList.add('btn_value_1');
      break;
    case "-1":
      select.classList.add('btn_value_-1');
      break;
    case "11":
      select.classList.add('btn_value_11');
      break;
  }
}

document.querySelectorAll('select[name="result[]"], select[name="exceptions_result[]"]')
    .forEach(select => applySelectClass(select));

document.body.addEventListener('change', function(e) {
  if (e.target.matches('select[name="result[]"], select[name="exceptions_result[]"]')) {
    applySelectClass(e.target);
  }
});

