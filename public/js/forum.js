/**
 * Permet de cacher et d'afficher les replies box
 */
document.addEventListener('DOMContentLoaded', function() {
  const replyButtons = document.querySelectorAll('.reply_script');
  let openReplyBox = null;

  replyButtons.forEach(button => {
    button.addEventListener('click', function() {
      const replyBox = button.closest('.post').querySelector('.reply-box');

      if (openReplyBox && openReplyBox !== replyBox) {
        openReplyBox.style.display = 'none';
      }

      if (replyBox) {
        if (replyBox.style.display === 'flex') {
          replyBox.style.display = 'none';
          openReplyBox = null;
        } else {
          replyBox.style.display = 'flex';
          openReplyBox = replyBox;
        }
      }
    });
  });
});


/**
 * Requête AJAX après clic sur bouton
 * @param {???} button
 * @param {number} id - si c'est la box du post ou d'une réponse - 0: post; 1: reply
 */
function call_controller(button, id) {
  const replyBox = button.closest('.reply-box');
  const textarea = replyBox.querySelector('textarea');
  const content = textarea.value;
  const posts = document.getElementById("posts_container");

  if (!content.trim()) {
    //console.log("Le message est vide !");
    return;
  }

  const formData = new FormData();
  formData.append('content', content);
  formData.append('id', id);
  const url = '/post_create';

  fetch(url, {
    method: 'POST',
    body: formData,
    headers: {
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
      .then(response => {
        if (!response.ok) throw new Error("Erreur réseau");
        return response.text();
      })
      .then(data => {
        textarea.value = '';
        if (id !== 0) {
          replyBox.style.display = 'none';
        }
        //console.log('Message envoyé !');
        // 13/08/2025 - On va plutot F5 car flemme de trouver un moyen de mettre le nouveau box au bon endroit
        location.reload();
        /*
        const newPost = document.createElement('div');
        newPost.classList.add('post');
        if (id !== 0) {
          newPost.classList.add("reply")
        }
        newPost.innerHTML = `
          <div class="author">
            <span>xxxxxx</span>
          </div>

          <div class="content">`+ content +`</div>
          <div class="actions">
            <div class="button">
              <span class="number_of">XXX</span>
              <svg class="svgbutton" viewBox="0 0 24 24" width="24" height="24">
                <path fill="white" d="M12.781 2.375c-.381-.475-1.181-.475-1.562 0l-8 10A1.001 1.001 0 0 0 4 14h4v7a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-7h4a1.001 1.001 0 0 0 .781-1.625l-8-10zM15 12h-1v8h-4v-8H6.081L12 4.601 17.919 12H15z"/>
              </svg>
            </div>
            <div class="button">
              <span class="number_of">XXX</span>
              <svg class="svgbutton down" viewBox="0 0 24 24" width="24" height="24">
                <path fill="white" d="M12.781 2.375c-.381-.475-1.181-.475-1.562 0l-8 10A1.001 1.001 0 0 0 4 14h4v7a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-7h4a1.001 1.001 0 0 0 .781-1.625l-8-10zM15 12h-1v8h-4v-8H6.081L12 4.601 17.919 12H15z"/>
              </svg>
            </div>
            <div class="button">
              <span class="number_of">XXX</span>
              <svg viewBox="0 0 24 24" width="24" height="24">
                <path fill="white" d="M1.751 10c0-4.42 3.584-8 8.005-8h4.366c4.49 0 8.129 3.64 8.129 8.13 0 2.96-1.607 5.68-4.196 7.11l-8.054 4.46v-3.69h-.067c-4.49.1-8.183-3.51-8.183-8.01zm8.005-6c-3.317 0-6.005 2.69-6.005 6 0 3.37 2.77 6.08 6.138 6.01l.351-.01h1.761v2.3l5.087-2.81c1.951-1.08 3.163-3.13 3.163-5.36 0-3.39-2.744-6.13-6.129-6.13H9.756z"></path>
              </svg>
            </div>
          </div>
          <div class="reply-box" style="display: none;">
            <div class="label_container">
                <textarea placeholder="Votre réponse..."></textarea>
            </div>
            <div class="button_container">
              <button class="send-reply">Envoyer</button>
            </div>
        </div>
`
        posts.append(newPost)
        */
      })
      .catch(error => {
        console.error(error);
      });
}

/** Envoyer au serveur l'upvote ou downvote et permet aussi de changer la valeur dans le html sans recharger la page
 *
 * @param button - la div du bouton
 * @param {int }post_id - l'id du post voté
 * @param {string} vote_type - le type de vote: up ou down
 */
function vote(button, post_id, vote_type){
  fetch('/vote', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ post_id, vote_type })
  })
    .then(res => res.json())
    .then(data => {
      if (data.error) {
        console.log("Erreur vote():", data.error);
      } else {
        //console.log("Vote OK ! Nouveau vote_weight:", data.vote_weight);
        const span = button.querySelector('.number_of');
        if (vote_type === 'up') {
          span.textContent = parseInt(span.textContent) + 1;
        } else if (vote_type === 'down') {
          span.textContent = parseInt(span.textContent) + 1;
        }
      }
    });
}

/**
 * Redirige vers une page de reply spécifique.
 *
 * @param {number|string} post_id - L'identifiant du post/reply.
 * @returns {void}
 */
function full_reply(post_id){
  window.location.href = "/post/" + post_id;
}


/**
 * Redirige vers la page du forum en construisant dynamiquement l'URL.
 *
 * @param {string} clanId - L'identifiant du clan.
 * @param {string} date - La date ou identifiant du forum.
 * @param {string} firstarg - Premier argument numérique, sera formaté sur 2 chiffres.
 * @param {string} secondarg - Second argument numérique, sera formaté sur 1 chiffre.
 * @returns {void}
 */
function back_at_beginning(clanId, date, firstarg, secondarg){
  let url = "../forum/"+clanId+"/"+date+firstarg+secondarg
  //console.log(url);
  window.location.href = url;
}
