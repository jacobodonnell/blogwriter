<x-layouts.guest>
    @section('title', 'Reset Password')

    <div class="card bg-base-100 shadow-xl" id="reset-password-card">
        <div class="card-body">
            <h2 class="card-title text-2xl font-bold justify-center mb-6">Reset Password</h2>

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-error mb-4">
                    <i class="ph ph-x-circle text-xl shrink-0"></i>
                    <ul class="text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" x-target="reset-password-card" x-data="{ processing: false }" @submit="processing = true">
                @csrf

                {{-- Token --}}
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                {{-- Email Address (hidden/prefilled) --}}
                <input type="hidden" name="email" value="{{ $request->email ?? old('email') }}">

                {{-- Display Email (read-only) --}}
                <div class="form-control mb-4">
                    <label class="label">
                        <span class="label-text">Email</span>
                    </label>
                    <input
                        type="email"
                        value="{{ $request->email ?? old('email') }}"
                        class="input input-bordered w-full bg-base-200"
                        disabled
                    />
                </div>

                {{-- New Password --}}
                <div class="form-control mb-4">
                    <label for="password" class="label">
                        <span class="label-text">New Password</span>
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="input input-bordered w-full @error('password') input-error @enderror"
                        required
                        autofocus
                        autocomplete="new-password"
                    />
                    @error('password')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="form-control mb-6">
                    <label for="password_confirmation" class="label">
                        <span class="label-text">Confirm Password</span>
                    </label>
                    <input
                        type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="input input-bordered w-full @error('password_confirmation') input-error @enderror"
                        required
                        autocomplete="new-password"
                    />
                    @error('password_confirmation')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <div class="form-control">
                    <button type="submit" class="btn btn-primary w-full" :class="{ 'loading': processing }" :disabled="processing">
                        <span x-show="!processing">Reset Password</span>
                        <span x-show="processing">Resetting...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
