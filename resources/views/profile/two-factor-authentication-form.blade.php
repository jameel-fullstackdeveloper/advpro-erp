<div class="card shadow-sm border-0">
    <div class="card-header bg-primary">
        <h4 class="mb-1 text-white">{{ __('Two Factor Authentication') }}</h4>
       </div>

    <div class="card-body">
    <p class="mb-4 text-muted">{{ __('Add additional security to your account using two-factor authentication.') }}</p>

        <h5 class="card-title" style="border:none;">
            @if ($this->enabled)
                @if ($showingConfirmation)
                    {{ __('Finish enabling two-factor authentication.') }}
                @else
                <span class="text-success">{{ __('You have enabled two-factor authentication.') }}</span>
                @endif
            @else
            <span class="text-danger">{{ __('You have not enabled two-factor authentication.') }}</span>
            @endif
        </h5>

        <p class="text-muted">
            {{ __('When two-factor authentication is enabled, you will be prompted for a secure, random token during authentication. You can retrieve this token from your phone\'s Google Authenticator application.') }}
        </p>

        @if ($this->enabled)
            @if ($showingQrCode)
                <div class="alert alert-info mt-3">
                    <strong>
                        @if ($showingConfirmation)
                            {{ __('To finish enabling two-factor authentication, scan the following QR code using your phone\'s authenticator application or enter the setup key and provide the generated OTP code.') }}
                        @else
                            {{ __('Two-factor authentication is now enabled. Scan the following QR code using your phone\'s authenticator application or enter the setup key.') }}
                        @endif
                    </strong>
                </div>

                <div class="mt-4 p-3 bg-light border rounded d-flex justify-content-center">
                    {!! $this->user->twoFactorQrCodeSvg() !!}
                </div>

                <div class="mt-3">
                    <p><strong>{{ __('Setup Key') }}:</strong> {{ decrypt($this->user->two_factor_secret) }}</p>
                </div>

                @if ($showingConfirmation)
                    <div class="form-group mt-4">
                        <label for="code">{{ __('Code') }}</label>
                        <input id="code" type="text" class="form-control w-50 shadow-sm" inputmode="numeric" autofocus autocomplete="one-time-code"
                            wire:model="code" wire:keydown.enter="confirmTwoFactorAuthentication" placeholder="Enter the authentication code">
                        @error('code') <span class="text-danger">{{ $message }}</span> @enderror
                    </div>
                @endif
            @endif

            @if ($showingRecoveryCodes)
                <div class="alert alert-warning mt-4">
                    <strong>{{ __('Store these recovery codes in a secure password manager. They can be used to recover access to your account if your two-factor authentication device is lost.') }}</strong>
                </div>

                <div class="bg-light p-3 rounded">
                    @foreach (json_decode(decrypt($this->user->two_factor_recovery_codes), true) as $code)
                        <div>{{ $code }}</div>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="mt-4">
            @if (! $this->enabled)
                <button type="button" class="btn btn-primary" wire:click="enableTwoFactorAuthentication" wire:loading.attr="disabled">
                    {{ __('Enable') }}
                </button>
            @else
                @if ($showingRecoveryCodes)
                    <button type="button" class="btn btn-secondary me-2" wire:click="regenerateRecoveryCodes" wire:loading.attr="disabled">
                        {{ __('Regenerate Recovery Codes') }}
                    </button>
                @elseif ($showingConfirmation)
                    <button type="button" class="btn btn-primary me-2" wire:click="confirmTwoFactorAuthentication" wire:loading.attr="disabled">
                        {{ __('Confirm') }}
                    </button>
                @else
                    <button type="button" class="btn btn-secondary me-2" wire:click="showRecoveryCodes" wire:loading.attr="disabled">
                        {{ __('Show Recovery Codes') }}
                    </button>
                @endif

                @if ($showingConfirmation)
                    <button type="button" class="btn btn-danger" wire:click="disableTwoFactorAuthentication" wire:loading.attr="disabled">
                        {{ __('Cancel') }}
                    </button>
                @else
                    <button type="button" class="btn btn-danger" wire:click="disableTwoFactorAuthentication" wire:loading.attr="disabled">
                        {{ __('Disable') }}
                    </button>
                @endif
            @endif
        </div>
    </div>
</div>
