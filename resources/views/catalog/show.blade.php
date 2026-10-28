<x-layout :title="$product->name">
    <div class="mx-auto max-w-3xl">
        <a href="{{ route('catalog.index') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
            &larr; Back to catalogue
        </a>

        <article class="mt-4 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            <p class="text-xs font-medium tracking-wide text-indigo-600 uppercase">
                {{ $product->category->name }}
            </p>

            <h1 class="mt-2 text-2xl font-semibold tracking-tight">{{ $product->name }}</h1>

            <p class="mt-4 text-3xl font-semibold">{{ Number::currency((float) $product->price) }}</p>

            <p class="mt-1 text-sm text-gray-500">
                {{ $product->stock > 0 ? $product->stock.' in stock' : 'Out of stock' }}
            </p>

            @if ($product->description)
                <div class="mt-6 border-t border-gray-100 pt-6">
                    <h2 class="text-sm font-semibold text-gray-900">Description</h2>
                    <p class="mt-2 text-sm leading-relaxed text-gray-600">{{ $product->description }}</p>
                </div>
            @endif
        </article>
    </div>
</x-layout>
