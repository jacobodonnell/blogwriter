<x-layouts.guest>
    @section('title', 'Log In')

    <div class="card bg-base-100 shadow-xl" id="login-card">
        <div class="card-body">
            <h2 class="card-title text-2xl font-bold justify-center mb-6">Log In to BlogWriter</h2>

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

            {{-- Session Status --}}
            @if (session('status'))
                <div class="alert alert-success mb-4">
                    <i class="ph ph-check-circle text-xl shrink-0"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" x-target="login-card" x-target.away="_top" x-data="{ processing: false }" @submit="processing = true">
                @csrf

                {{-- Email Address --}}
                <div class="form-control mb-4">
                    <label for="email" class="label">
                        <span class="label-text">Email</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        data-test="login-email"
                        value="{{ old('email') }}"
                        class="input input-bordered w-full @error('email') input-error @enderror"
                        required
                        autofocus
                        autocomplete="username"
                    />
                    @error('email')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="form-control mb-4">
                    <label for="password" class="label">
                        <span class="label-text">Password</span>
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        data-test="login-password"
                        class="input input-bordered w-full @error('password') input-error @enderror"
                        required
                        autocomplete="current-password"
                    />
                    @error('password')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="form-control mb-6">
                    <label class="label cursor-pointer justify-start gap-2">
                        <input
                            type="checkbox"
                            name="remember"
                            class="checkbox checkbox-sm"
                            {{ old('remember') ? 'checked' : '' }}
                        />
                        <span class="label-text">Remember me</span>
                    </label>
                </div>

                {{-- Submit Button --}}
                <div class="form-control">
                    <button type="submit" class="btn btn-primary w-full" data-test="login-submit" :class="{ 'loading': processing }" :disabled="processing">
                        <span x-show="!processing">Log in</span>
                        <span x-show="processing">Logging in...</span>
                    </button>
                </div>

                {{-- Forgot Password Hint --}}
                <div class="mt-4 text-center">
                    <p class="text-base-content/50 text-xs">
                        Forgot your password? Run <code>php artisan blogwriter:user:reset-password</code> via SSH.
                    </p>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
