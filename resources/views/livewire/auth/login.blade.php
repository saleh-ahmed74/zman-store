<section class="auth-page"><p class="eyebrow">Hima account</p><h1>{{ __('store.login') }}</h1>
    <form class="auth-form" wire:submit="authenticate">
        <label>{{ __('store.email') }}<input type="email" wire:model.blur="email" autocomplete="email" maxlength="120" required aria-invalid="@error('email') true @else false @enderror"></label>
        @error('email')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        <label>{{ __('store.password') }}<input type="password" wire:model="password" autocomplete="current-password" required></label>
        @error('password')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        <label class="remember-row"><input type="checkbox" wire:model="remember"> {{ __('store.remember_me') }}</label>
        <button class="button button-dark" type="submit">{{ __('store.login') }}</button>
    </form>
    <div class="auth-links"><a href="{{ route('register') }}">{{ __('store.create_account') }}</a><a href="{{ route('password.request') }}">{{ __('store.forgot_password') }}</a></div>
</section>
