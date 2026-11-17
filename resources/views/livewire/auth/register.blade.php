<section class="auth-page"><p class="eyebrow">Hima account</p><h1>{{ __('store.register') }}</h1>
    <form class="auth-form" wire:submit="register">
        <label>{{ __('store.full_name') }}<input type="text" wire:model.blur="name" autocomplete="name" minlength="3" maxlength="60" required></label>
        @error('name')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        <label>{{ __('store.email') }}<input type="email" wire:model.blur="email" autocomplete="email" maxlength="120" required></label>
        @error('email')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        <label>{{ __('store.password') }}<input type="password" wire:model="password" autocomplete="new-password" minlength="10" required></label>
        @error('password')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        <label>{{ __('store.confirm_password') }}<input type="password" wire:model="password_confirmation" autocomplete="new-password" minlength="10" required></label>
        <button class="button button-dark" type="submit">{{ __('store.create_account') }}</button>
    </form>
    <div class="auth-links"><a href="{{ route('login') }}">{{ __('store.have_account') }}</a></div>
</section>
