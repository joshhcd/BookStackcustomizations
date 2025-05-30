@props(['type','id'])
@php
  $userLiked = auth()->check()
    && \BookStack\Likeable\Models\Like::where([
         ['likeable_type', '\\BookStack\\'.ucfirst(rtrim($type,'s'))],
         ['likeable_id', $id],
         ['user_id', auth()->id()]
       ])->exists();
@endphp
<div id="like-btn-{{ $type }}-{{ $id }}" class="like-button" data-type="{{ $type }}" data-id="{{ $id }}">
  <button class="btn btn-outline-secondary btn-sm">
    {{ $userLiked ? '★' : '☆' }} Like (<span class="like-count">0</span>)
  </button>
</div>
