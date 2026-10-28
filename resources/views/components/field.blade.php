@props([
    'label',
    'name',
    'required' => false,
])

<div {{ $attributes->only('class') }}>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700">
        {{ $label }}
        @if ($required)
            <span class="text-red-600" aria-hidden="true">*</span>
        @endif
    </label>

    <div class="mt-1">
        {{ $slot }}
    </div>

    <x-input-error :field="$name" />
</div>
