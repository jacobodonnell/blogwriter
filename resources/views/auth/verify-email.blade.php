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
                    <i class="ph ph-check-circle text-xl shrink-0"></i>
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
