<x-layout title="Log in">
    <div class="mx-auto max-w-md">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <h1 class="text-lg font-semibold tracking-tight">Log in</h1>
            <p class="mt-1 text-sm text-gray-600">Sign in to manage the product catalogue.</p>

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf

                <x-field label="Email" name="email" :required="true">
                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                           required autofocus autocomplete="username" class="form-control">
                </x-field>

                <x-field label="Password" name="password" :required="true">
                    <input id="password" name="password" type="password"
                           required autocomplete="current-password" class="form-control">
                </x-field>

                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="remember" value="1"
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Remember me
                </label>

                <button type="submit" class="btn btn-primary w-full">Log in</button>
            </form>
        </div>
    </div>
</x-layout>
