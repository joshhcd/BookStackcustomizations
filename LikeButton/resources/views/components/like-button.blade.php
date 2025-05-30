@props(['type','id'])
@php
    $userLiked = auth()->check()
      && \BookStack\Likeable\Models\Like::where([
           ['likeable_type', '\\BookStack\\Entities\\Models\\'.ucfirst(rtrim($type,'s'))],
           ['likeable_id',   $id],
           ['user_id',       auth()->id()]
         ])->exists();
@endphp

<div 
  id="like-btn-{{ $type }}-{{ $id }}"
  class="like-button inline-flex items-center"
  data-type="{{ $type }}"
  data-id="{{ $id }}"
>
  <span class="like-icon {{ $userLiked ? 'liked' : '' }}">👍</span>
  <span class="like-count">{{ $userLiked ? '' : '' }}</span>
</div>
