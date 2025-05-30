@parent
{{-- Keep all of the default head content, then add ours --}}
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="app-url"     content="{{ url('') }}">
<style>
  .like-button {
    padding-top: .5rem;
    padding-bottom: .5rem;
  }
  .like-button .icon-sm {
    color: #6b7280;          /* gray-500 */
    transition: color .2s;
    cursor: pointer;
  }
  .like-button:hover .icon-sm {
    color: #111827;          /* gray-900 on hover */
  }
  .like-button.liked .icon-sm {
    color: #2563eb;          /* blue-600 when liked */
  }
  .like-count {
    font-weight: 600;
    margin-left: 0.25rem;
  }
</style>

