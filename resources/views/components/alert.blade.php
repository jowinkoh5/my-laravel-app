@props([
    'variant' => 'success',
])

@php
    $styles = [
        'success' => 'border-green-200 bg-green-50 text-green-800',
        'error' => 'border-red-200 bg-red-50 text-red-800',
    ][$variant];
@endphp

<div {{ $attributes->merge(['class' => "rounded-md border px-4 py-3 text-sm {$styles}"]) }} role="status">
    {{ $slot }}
</div>
