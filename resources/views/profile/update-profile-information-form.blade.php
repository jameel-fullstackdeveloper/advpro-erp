<form wire:submit.prevent="updateProfileInformation">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-1 text-white">{{ __('Profile Information') }}</h4>
        </div>

        <div class="card-body">

        <p class="text-muted mb-4">{{ __('Update your account\'s profile information and email address.') }}</p>


            <!-- Profile Photo -->
            @if (Laravel\Jetstream\Jetstream::managesProfilePhotos())
                <div x-data="{ photoName: null, photoPreview: null }" class="mb-4">
                    <!-- Profile Photo File Input -->
                    <input type="file" id="photo" class="d-none"
                        wire:model.live="photo"
                        x-ref="photo"
                        x-on:change="
                            photoName = $refs.photo.files[0].name;
                            const reader = new FileReader();
                            reader.onload = (e) => {
                                photoPreview = e.target.result;
                            };
                            reader.readAsDataURL($refs.photo.files[0]);
                        " />

                    <label for="photo" class="form-label">{{ __('Profile Photo') }}</label>

                    <div class="d-flex align-items-center">
                        <!-- Current Profile Photo -->
                        <div class="me-3" x-show="!photoPreview">
                            <img src="{{ $this->user->profile_photo_url }}" alt="{{ $this->user->name }}" class="rounded-circle img-thumbnail" style="height: 80px; width: 80px;">
                        </div>

                        <!-- New Profile Photo Preview -->
                        <div x-show="photoPreview" style="display: none;">
                            <span class="d-block rounded-circle img-thumbnail" style="height: 80px; width: 80px; background-size: cover; background-position: center;"
                                x-bind:style="'background-image: url(\'' + photoPreview + '\');'">
                            </span>
                        </div>

                        <!-- Buttons -->
                        <div>
                            <button class="btn btn-outline-secondary me-2" type="button" x-on:click.prevent="$refs.photo.click()">
                                {{ __('Select A New Photo') }}
                            </button>

                            @if ($this->user->profile_photo_path)
                                <button class="btn btn-danger" type="button" wire:click="deleteProfilePhoto">
                                    {{ __('Remove Photo') }}
                                </button>
                            @endif
                        </div>
                    </div>

                    <x-input-error for="photo" class="mt-2" />
                </div>
            @endif

            <!-- Name -->
            <div class="mb-4">
                <label for="name" class="form-label">{{ __('Name') }}</label>
                <input id="name" type="text" class="form-control shadow-sm" wire:model="state.name" required autocomplete="name" placeholder="Enter your name" />
                <x-input-error for="name" class="mt-2" />
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label for="email" class="form-label">{{ __('Email') }}</label>
                <input id="email" type="email" class="form-control shadow-sm" wire:model="state.email" required autocomplete="username" placeholder="Enter your email address" />
                <x-input-error for="email" class="mt-2" />

                @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::emailVerification()) && ! $this->user->hasVerifiedEmail())
                    <div class="mt-3">
                        <p class="text-muted mb-2">
                            {{ __('Your email address is unverified.') }}
                            <button type="button" class="btn btn-link p-0 text-decoration-underline" wire:click.prevent="sendEmailVerification">
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if ($this->verificationLinkSent)
                            <p class="text-success">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <div class="card-footer bg-light d-flex justify-content-end bg-white">
            <div wire:loading wire:target="photo" class="spinner-border spinner-border-sm me-2" role="status">
                <span class="sr-only">Loading...</span>
            </div>

            <x-action-message class="me-3" on="saved">
                {{ __('Saved.') }}
            </x-action-message>

            <button type="submit" class="btn btn-primary px-4" wire:loading.attr="disabled">
                {{ __('Save') }}
            </button>
        </div>
    </div>
</form>
