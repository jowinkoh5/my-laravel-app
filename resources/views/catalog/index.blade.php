<x-layout title="Catalogue">
    <div>
        <h1 class="text-xl font-semibold tracking-tight">Catalogue</h1>
        <p class="mt-1 text-sm text-gray-600">{{ $products->total() }} product(s) available.</p>
    </div>

    <form method="GET" action="{{ route('catalog.index') }}"
          class="mt-6 grid gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-3">
        <x-field label="Search" name="search">
            <input id="search" name="search" type="search" value="{{ $filter->search }}"
                   placeholder="Name or description" class="form-control">
        </x-field>

        <x-field label="Category" name="category_id">
            <select id="category_id" name="category_id" class="form-control">
                <option value="">All categories</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected($filter->categoryId === $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </x-field>

        <div class="flex items-end gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('catalog.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($products as $product)
            <article class="flex flex-col rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-xs font-medium tracking-wide text-indigo-600 uppercase">
                    {{ $product->category->name }}
                </p>

                <h2 class="mt-2 text-base font-semibold">
                    <a href="{{ route('catalog.show', $product) }}" class="hover:text-indigo-600">
                        {{ $product->name }}
                    </a>
                </h2>

                <p class="mt-2 line-clamp-3 grow text-sm text-gray-600">{{ $product->description }}</p>

                <div class="mt-4 flex items-center justify-between">
                    <span class="text-lg font-semibold">{{ Number::currency((float) $product->price) }}</span>
                    <span class="text-xs text-gray-500">
                        {{ $product->stock > 0 ? $product->stock.' in stock' : 'Out of stock' }}
                    </span>
                </div>
            </article>
        @empty
            <p class="text-sm text-gray-500 sm:col-span-2 lg:col-span-3">No products available.</p>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $products->links() }}
    </div>
</x-layout>
