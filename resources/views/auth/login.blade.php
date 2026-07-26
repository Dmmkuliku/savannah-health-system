<x-guest-layout>
    <div class="mb-8">
        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-brand-600">Staff Sign In</p>
        <h2 class="mt-2 font-display text-3xl text-ink-900">Karibu</h2>
        <p class="mt-1 text-sm text-ink-700/70">Sign in to Savannah Health System.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <div>
            <label class="mp-label" for="email">Email</label>
            <input id="email" class="mp-input" type="email" name="email" value="{{ old('email') }}" required autofocus>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div>
            <label class="mp-label" for="password">Password</label>
            <input id="password" class="mp-input" type="password" name="password" required>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>
        <label class="inline-flex items-center gap-2 text-sm text-ink-700">
            <input type="checkbox" name="remember" class="rounded border-brand-300 text-brand-700 focus:ring-brand-500">
            Remember this workstation
        </label>
        <button class="mp-btn w-full">Sign in to Savannah Health</button>
    </form>

    <div class="mt-6 rounded-xl bg-brand-50 p-3 text-xs text-brand-800">
        Admin: <strong>admin@savannah.health</strong> / <strong>Savannah@Admin1</strong>
        <p class="mt-1 text-brand-700/80">Register other hospital staff from Staff Users after login.</p>
    </div>
</x-guest-layout>
