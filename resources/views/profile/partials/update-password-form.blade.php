<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        @php
            $isAdmin = Auth::user()->role === \App\Models\User::ROLE_ADMIN;
            $disabledClasses = $isAdmin ? 'bg-gray-100 cursor-not-allowed opacity-75' : '';
        @endphp

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password"
                          class="mt-1 block w-full {{ $disabledClasses }}"
                          autocomplete="current-password"
                          :disabled="$isAdmin" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input id="update_password_password" name="password" type="password"
                          class="mt-1 block w-full {{ $disabledClasses }}"
                          autocomplete="new-password"
                          :disabled="$isAdmin" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password"
                          class="mt-1 block w-full {{ $disabledClasses }}"
                          autocomplete="new-password"
                          :disabled="$isAdmin" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            @if(!$isAdmin)
                <x-primary-button>{{ __('Save') }}</x-primary-button>

                @if (session('status') === 'password-updated')
                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)" class="text-sm text-gray-600">
                        {{ __('Saved.') }}
                    </p>
                @endif
            @else
                <p class="text-red-500 text-sm font-bold bg-red-50 px-3 py-1 border border-red-200 rounded">
                    Admins cannot change their password here.
                </p>
            @endif
        </div>
    </form>
</section>
