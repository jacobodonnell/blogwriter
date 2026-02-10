<x-layouts.guest>
    @section('title', 'Forgot Password')

    <div class="card bg-base-100 shadow-xl" id="forgot-password-card">
        <div class="card-body">
            <h2 class="card-title text-2xl font-bold justify-center mb-2">Forgot Password</h2>
            <p class="text-base-content/70 text-center mb-6 text-sm">
                Enter your email address and we'll send you a password reset link.
            </p>

            {{-- Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-error mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <ul class="text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Success Message --}}
            @if (session('status'))
                <div class="alert alert-success mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}" x-target="forgot-password-card" x-data="{ processing: false }" @submit="processing = true">
                @csrf

                {{-- Email Address --}}
                <div class="form-control mb-6">
                    <label for="email" class="label">
                        <span class="label-text">Email</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="input input-bordered w-full @error('email') input-error @enderror"
                        required
                        autofocus
                        autocomplete="email"
                    />
                    @error('email')
                        <label class="label">
                            <span class="label-text-alt text-error">{{ $message }}</span>
                        </label>
                    @enderror
                </div>

                {{-- Submit Button --}}
                <div class="form-control">
                    <button type="submit" class="btn btn-primary w-full" :class="{ 'loading': processing }" :disabled="processing">
                        <span x-show="!processing">Send Reset Link</span>
                        <span x-show="processing">Sending...</span>
                    </button>
                </div>

                {{-- Back to Login Link --}}
                <div class="mt-4 text-center">
                    <a href="{{ route('login') }}" class="link link-primary text-sm">
                        Back to login
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.guest>
