<x-layouts.guest>
    @section('title', 'Verify Email')

    <div class="card bg-base-100 shadow-xl" id="verify-email-card">
        <div class="card-body">
            <h2 class="card-title text-2xl font-bold justify-center mb-4">Verify Your Email</h2>

            <p class="text-base-content/70 text-center mb-6 text-sm">
                Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn't receive the email, we will gladly send you another.
            </p>

            {{-- Success Message --}}
            @if (session('status') == 'verification-link-sent')
                <div class="alert alert-success mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 shrink-0 stroke-current" fill="none" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>A new verification link has been sent to the email address you provided during registration.</span>
                </div>
            @endif

            <div class="flex flex-col gap-4">
                {{-- Resend Verification Email Form --}}
                <form method="POST" action="{{ route('verification.send') }}" x-target="verify-email-card" x-data="{ processing: false }" @submit="processing = true">
                    @csrf

                    <button type="submit" class="btn btn-primary w-full" :class="{ 'loading': processing }" :disabled="processing">
                        <span x-show="!processing">Resend Verification Email</span>
                        <span x-show="processing">Sending...</span>
                    </button>
                </form>

                {{-- Logout Form --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="btn btn-ghost w-full">
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.guest>
