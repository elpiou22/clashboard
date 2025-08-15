const smallBoxes = document.querySelectorAll('.small_box');
const popup = document.getElementById('popup');
const showNbPostsElement = document.getElementById('posts_content');
let currentBox = null;
let cwlTd, cwlTr;
let attackerTH, defenderTH, stars, percentage, infosContent;

/**
 * Permet de mettre un event "mouseover" à chaque case du tableau. Cet event servira à modifier le visuel de la cellule ainsi que d'afficher une popup
 */
function construct_events() {
  smallBoxes.forEach(smallBox => {
    smallBox.addEventListener('mouseover', (event) => {
      // 11/08/2025 - Plus de popup si colonne "total" ou "more"
      if (smallBox.classList.contains('show_popup')) {
        event.target.classList.add("hovered");
        event.target.style.padding = '0';
        event.target.style.height = '100%';
        event.target.style.fontSize = 'large';

        // 11/08/2025 - La div ne contient plus de label:
        /*
        if (event.target.tagName === "INPUT"){
          let label = event.target.closest("label");
          label.style.height = "100%";
        }
       */

        cwlTd = event.target.closest('td');
        if (cwlTd) {
          cwlTd.classList.add('cwl_hovered');
        }

        changeNbPosts(event)

        popup.style.display = 'block';
        popup.style.position = 'fixed';

        // 11/08/2025 - info attaque:
        attackerTH = smallBox.querySelector('.attackerTH')?.innerText || '?';
        defenderTH = smallBox.querySelector('.defenderTH')?.innerText || '?';
        stars = smallBox.querySelector('.stars')?.innerText || '?';
        percentage = smallBox.querySelector('.percentage')?.innerText || '?';
        infosContent = document.querySelector('.infos_content');
        if (infosContent) {
          infosContent.innerHTML = `th${attackerTH} vs th${defenderTH} <br> ${stars}⭐ : ${percentage}%`;
        }


        const rect = smallBox.getBoundingClientRect();
        popup.style.top = `${rect.top - 10}px`;
        popup.style.left = `${rect.left + rect.width + 1}px`;

        currentBox = smallBox;
      }
    });

    smallBox.addEventListener('mouseleave', (event) => {
      if (currentBox !== null && !popup.contains(event.relatedTarget)) {
        event.target.style.removeProperty('padding');
        event.target.style.removeProperty('height');
        event.target.style.removeProperty('font-size');

        if (event.target.tagName === "INPUT"){
          let label = event.target.closest("label");
          label.style.removeProperty('height');
        }


        popup.style.display = 'none';
        event.target.classList.remove("hovered");
        if (cwlTd) {
          cwlTd.classList.remove('cwl_hovered');
        }
        currentBox = null;
      }
    });
  });
}

/**
 * Permet de laisser la popup visible lorsqu'on passe la souris dessus (on quitte donc la cellule smallbox)
 */
function show_popup() {
  popup.addEventListener('mouseenter', () => {
    popup.style.display = 'block';
  });
}

/**
 * Cache la popup lorsqu'on enlève la souris de la popup
 */
function remove_Popup() {
  popup.addEventListener('mouseleave', () => {
    popup.style.display = 'none';
    currentBox.style.removeProperty('padding');
    currentBox.style.removeProperty('height');
    currentBox.style.removeProperty('font-size');
    currentBox.classList.remove("hovered");
    if (cwlTd) {
      cwlTd.classList.remove('cwl_hovered');
    }
  });
}

/**
 * Récupère la valeur du nombre de posts présent dans la div id:nbPosts. Et écrit dans la div id:posts_content le nombre de posts présent pour une donnée du tableau
 */
function changeNbPosts(event) {
  let   nbPosts = '';
  const nbPostsElement = event.currentTarget.querySelector('#nbPosts');

  if (nbPostsElement) {
    nbPosts = parseInt(nbPostsElement.textContent.trim(),10);
    if (nbPosts === 0 ){
      showNbPostsElement.innerHTML = "<p>Il n'y a aucun post</p>";
    } else if (nbPosts === 1 ) {
      showNbPostsElement.innerHTML = "<p>Il y a " + nbPosts + " post</p>";
    } else {
      showNbPostsElement.innerHTML = "<p>Il y a " + nbPosts + " posts</p>";
    }
  }
}

construct_events();
show_popup();
remove_Popup();