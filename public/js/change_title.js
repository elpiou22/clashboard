

/**
 * hange le titre en fonction du lien
 */
function change_title() {
  const months_FR = [
    'Janvier','Février','Mars','Avril','Mai','Juin',
    'Juillet','Août','Septembre','Octobre','Novembre','Décembre'
  ];
  const path = window.location.pathname;
  const last = path.split('/').filter(Boolean).pop() || '';
  let yy, mm, code;
  let title;
  if (path.split('/')[1] === "forum") {
    code = last.slice(0, 4);                 // "2501"
    title = document.querySelector('.main_title')
    title.textContent += " - ";
  } else if (path.split('/')[1] === "bonusdata") {
    code = document.getElementById("cwlDate").textContent;
    title = document.querySelector('#title_date')

    title.textContent = "";
  }
  if (code.length < 4) return;
  yy = Number(code.slice(0, 2));           // 25
  mm = Number(code.slice(2, 4));           // 01
  if (mm < 1 || mm > 12) return;

  const year = 2000 + yy;                        // 2025 (base 2000)
  const date = `${months_FR[mm - 1]} ${year}`;

  title.textContent+= date;
}



change_title();

