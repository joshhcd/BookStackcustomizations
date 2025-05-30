                    <aside class="tri-layout-right-contents">
                        @yield('right')
                        <div class="mb-4">
                          @if(isset($page))
                            {{-- Aside Like button on Page --}}
                            @include('likeable::components.like-button', [
                              'type' => 'pages',
                              'id'   => $page->slug
                            ])
                          @elseif(isset($book))
                            {{-- Aside Like button on Book --}}
                            @include('likeable::components.like-button', [
                              'type' => 'books',
                              'id'   => $book->slug
                            ])
                          @endif
                        </div>

                    </aside>