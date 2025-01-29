<form wire:submit.prevent="updatePassword">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0 text-white">{{ __('Update Password') }}</h5>
        </div>

        <div class="card-body">
            <p class="text-muted mb-4">
                {{ __('Ensure your account is using a long, random password to stay secure.') }}
            </p>

            <div class="row">
                <!-- Current Password -->
                <div class="col-12 col-md-6 mb-4">
                    <label for="current_password" class="form-label">{{ __('Current Password') }}</label>
                    <input id="current_password" type="password" class="form-control mt-1 shadow-sm" wire:model="state.current_password" autocomplete="current-password" placeholder="Enter your current password">
                    @error('current_password')
                        <span class="text-danger mt-2 small">{{ $message }}</span>
                    @enderror
                </div>

                <!-- New Password -->
                <div class="col-12 col-md-6 mb-4">
                    <label for="password" class="form-label">{{ __('New Password') }}</label>
                    <input id="password" type="password" class="form-control mt-1 shadow-sm" wire:model="state.password" autocomplete="new-password" placeholder="Enter your new password">
                    @error('password')
                        <span class="text-danger mt-2 small">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="col-12 col-md-6 mb-4">
                    <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
                    <input id="password_confirmation" type="password" class="form-control mt-1 shadow-sm" wire:model="state.password_confirmation" autocomplete="new-password" placeholder="Confirm your new password">
                    @error('password_confirmation')
                        <span class="text-danger mt-2 small">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card-footer bg-light d-flex justify-content-end bg-white">
            <span x-data="{ show: false }" x-show="show" x-init="@this.on('saved', () => { show = true; setTimeout(() => show = false, 2000); })" class="me-3 text-success small">
                {{ __('Saved.') }}
            </span>

            <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled">
                {{ __('Save') }}
            </button>
        </div>
    </div>
</form>
