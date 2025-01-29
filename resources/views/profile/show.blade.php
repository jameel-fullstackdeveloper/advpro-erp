@extends('layouts.master')
@section('title')
    Profile
@endsection
@section('content')



@role(['Super Admin','Administrator'])
<div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between bg-galaxy-transparent">
                <h4 class="mb-sm-0">{{ __('Profile') }}</h4>
                    <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="javascript: void(0);">User</a></li>
                                        <li class="breadcrumb-item active">{{ __('Profile') }}</li>
                                    </ol>
                    </div>
            </div>
        </div>
</div>

<div class="row">
    <div class="col">

    <div>
        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            @if (Laravel\Fortify\Features::canUpdateProfileInformation())
                @livewire('profile.update-profile-information-form')

            @endif

            @if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::updatePasswords()))
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.update-password-form')
                </div>

            @endif

            @if (Laravel\Fortify\Features::canManageTwoFactorAuthentication())
                <div class="mt-10 sm:mt-0">
                    @livewire('profile.two-factor-authentication-form')
                </div>

            @endif

            <div class="mt-10 sm:mt-0">
                @livewire('profile.logout-other-browser-sessions-form')
            </div>

            @if (Laravel\Jetstream\Jetstream::hasAccountDeletionFeatures())

                <div class="mt-10 sm:mt-0 d-none">
                    @livewire('profile.delete-user-form')
                </div>
            @endif



        </div>
    </div>


    </div>
</div>

@else

<div class="flex items-center pt-8 sm:justify-start sm:pt-0">

    <div class="ml-4 text-lg text-gray-500 uppercase tracking-wider">
        <h4 class="text-danger">User does not have the right permissions.</h4>
    </div>
</div>

@endrole


@endsection
@section('script')
    <script src="{{ URL::asset('build/js/app.js') }}"></script>
@endsection

