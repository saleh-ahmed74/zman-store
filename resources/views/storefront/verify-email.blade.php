<section class="auth-page"><p class="eyebrow">Hima account</p><h1>{{ __('store.verify_email') }}</h1><p>{{ __('store.verify_email_message') }}</p>
    @if (session('status'))<p class="form-status" role="status">{{ session('status') }}</p>@endif
    <form method="POST" action="{{ route('verification.send') }}">@csrf<button class="button button-dark" type="submit">{{ __('store.send_verification') }}</button></form>
</section>
