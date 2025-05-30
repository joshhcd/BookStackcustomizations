document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.like-button').forEach(el => {
    const btn      = el.querySelector('button');
    const countEl  = el.querySelector('.like-count');
    const type     = el.dataset.type;
    const id       = el.dataset.id;
    const fetchCount = () => {
      fetch(`/likes/${type}/${id}`)
        .then(r => r.json())
        .then(d => countEl.textContent = d.count);
    };
    fetchCount();
    btn.addEventListener('click', () => {
      fetch('/likes', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': window.Laravel.csrfToken
        },
        body: JSON.stringify({ type, item_id: id })
      }).then(fetchCount);
    });
  });
});