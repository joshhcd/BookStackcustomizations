;(function(){
  const initLikeButtons = () => {
    // 1) Grab CSRF token
    const metaCsrf = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = metaCsrf
      ? metaCsrf.getAttribute('content')
      : (window.Laravel && window.Laravel.csrfToken)
        ? window.Laravel.csrfToken
        : null;

    // 2) Grab App URL base
    const metaApp = document.querySelector('meta[name="app-url"]');
    const appUrl = metaApp
      ? metaApp.getAttribute('content')
      : ''; // fallback to root

    console.log('LikeButton init', { csrfToken: !!csrfToken, appUrl });

    if (!csrfToken) {
      console.warn('LikeButton: No CSRF token found – POSTs may fail.');
    }

    // 3) Find all wrappers
    document.querySelectorAll('.like-button').forEach(el => {
      const btn     = el.querySelector('button');
      const countEl = el.querySelector('.like-count');
      const type    = el.dataset.type;
      const id      = el.dataset.id;
      const countUrl= `${appUrl}/likes/${type}/${id}`;
      const storeUrl= `${appUrl}/likes`;

      // Fetch & render current count
      const updateCount = () => {
        fetch(countUrl, { headers: { 'Accept': 'application/json' } })
          .then(r => r.json())
          .then(d => countEl.textContent = d.count)
          .catch(e => console.error('LikeButton: count error', e));
      };
      updateCount();

      // Wire up the click
      btn.addEventListener('click', () => {
        console.log('LikeButton: click', { type, id });
        fetch(storeUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept':       'application/json',
          },
          body: JSON.stringify({ type, item_id: id })
        })
        .then(r => r.json())
        .then(d => {
          console.log('LikeButton: stored', d);
          updateCount();
        })
        .catch(e => console.error('LikeButton: store error', e));
      });
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLikeButtons);
  } else {
    initLikeButtons();
  }
})();
