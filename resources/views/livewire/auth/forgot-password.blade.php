<section class="auth-page"><p class="eyebrow">Hima account</p><h1>{{ __('store.forgot_password') }}</h1>
    @if (session('status'))<p class="form-status" role="status">{{ session('status') }}</p>@endif
    <form class="auth-form" wire:submit="sendLink">
        <p>{{ __('store.reset_instruction') }}</p>
        <label>{{ __('store.email') }}<input type="email" wire:model.blur="email" autocomplete="email" maxlength="120" required></label>
        @error('email')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        <button class="button button-dark" type="submit">{{ __('store.send_reset_link') }}</button>
    </form>
</section>
