<section class="auth-page"><p class="eyebrow">Hima account</p><h1>{{ __('store.reset_password') }}</h1>
    <form class="auth-form" wire:submit="resetPassword">
        <label>{{ __('store.email') }}<input type="email" wire:model.blur="email" autocomplete="email" maxlength="120" required></label>
        @error('email')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        <label>{{ __('store.new_password') }}<input type="password" wire:model="password" autocomplete="new-password" minlength="10" required></label>
        @error('password')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        <label>{{ __('store.confirm_password') }}<input type="password" wire:model="password_confirmation" autocomplete="new-password" minlength="10" required></label>
        <button class="button button-dark" type="submit">{{ __('store.save_password') }}</button>
    </form>
</section>
