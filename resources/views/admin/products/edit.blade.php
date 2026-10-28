<x-layout :title="'Edit '.$product->name">
    <div class="mx-auto max-w-3xl">
        <h1 class="text-xl font-semibold tracking-tight">Edit product</h1>
        <p class="mt-1 text-sm text-gray-600">{{ $product->name }}</p>

        <form method="POST" action="{{ route('admin.products.update', $product) }}"
              class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')

            @include('admin.products.partials.form', ['product' => $product, 'categories' => $categories])

            <div class="mt-6 flex items-center gap-2">
                <button type="submit" class="btn btn-primary">Save changes</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-layout>
