<script nonce="{{ $cspNonce }}">
(function(){
  // Grab the nonce in JS
  const nonce = '{{ $cspNonce }}';
  // Function to inject our external script
  const loadLikeScript = () => {
    const meta    = document.querySelector('meta[name="app-url"]');
    const baseUrl = meta ? meta.getAttribute('content') : '';
    const url     = baseUrl
      + '/vendor/likeable/js/like-button.js'
      + '?v='
      + '{{ file_exists(public_path("vendor/likeable/js/like-button.js")) 
           ? filemtime(public_path("vendor/likeable/js/like-button.js")) 
           : time() }}';

    const s = document.createElement('script');
    s.src = url;
    s.setAttribute('nonce', nonce);     // ← pass the nonce here
    document.head.appendChild(s);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadLikeScript);
  } else {
    loadLikeScript();
  }
})();
</script>

<script nonce="{{ $cspNonce }}">
  feather.replace();
</script>

