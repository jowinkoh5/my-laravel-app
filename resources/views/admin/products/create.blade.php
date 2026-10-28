<x-layout title="New product">
    <div class="mx-auto max-w-3xl">
        <h1 class="text-xl font-semibold tracking-tight">New product</h1>

        <form method="POST" action="{{ route('admin.products.store') }}"
              class="mt-6 rounded-lg border border-gray-200 bg-white p-6 shadow-sm">
            @csrf

            @include('admin.products.partials.form', ['product' => null, 'categories' => $categories])

            <div class="mt-6 flex items-center gap-2">
                <button type="submit" class="btn btn-primary">Create product</button>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</x-layout>
