<article class="content-page">
    @if ($page->image_path)<img class="content-hero-image" src="{{ asset('uploads/'.$page->image_path) }}" alt="">@endif
    <p class="eyebrow">Hima</p>
    <h1>{{ app()->getLocale() === 'ar' ? $page->title_ar : $page->title_en }}</h1>
    <div class="content-body">{{ app()->getLocale() === 'ar' ? $page->body_ar : $page->body_en }}</div>
    @if ($page->slug === 'about')<a class="button button-dark" href="{{ route('shop') }}">{{ __('store.shop_now') }}</a>@endif
</article>
