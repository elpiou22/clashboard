let rows = hide_row();


/**
 * when clicked on a hid icon, hide the row
 * @returns {NodeListOf<Element>} all rows founds in the table
 */
function hide_row() {
  let rows = document.querySelectorAll("#cwl_table tr")
  rows.forEach((row, row_index) => {
    let hideIconContainer = row.querySelector('.hide_icon_container');
    if (hideIconContainer) {
      hideIconContainer.addEventListener("click", () => {
        row.classList.add('fade-out');
        document.querySelector(".member_number_" + (row_index - 1)).style.display = 'flex';
        setTimeout(() => {
          row.style.display = 'none';
          row.classList.remove('fade-out');
          }, 200);
      });
    }
  });
  return rows
}

function show_member_name(index) {
  rows[index].style.display = '';
}


/**
 * Show the row we have previously hidden and hide the member's name
 */
function hide_hidden_members_and_show_row() {
  let members = document.querySelectorAll(".member")
  members.forEach((member,index ) => {
    if (member) {
      member.addEventListener("click", () => {

        member.style.display = 'none';
        let row = rows[index+1];

        if (row){

          row.style.display = '';
          row.style.opacity = '0';

          requestAnimationFrame(() => {
            row.classList.add('fade-in');
            row.style.opacity = '1';
          });

          setTimeout(() => {
            row.classList.remove('fade-in');
            row.style.opacity = '';
          }, 200);
        }
      });
    }
  });
}

hide_hidden_members_and_show_row()


