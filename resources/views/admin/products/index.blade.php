<x-layout title="Products">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold tracking-tight">Products</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $products->total() }} product(s) match the current filters.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.products.export', $filter->toQueryParameters()) }}" class="btn btn-secondary">
                Export to Excel
            </a>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">New product</a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('admin.products.index') }}"
          class="mt-6 grid gap-3 rounded-lg border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-2 lg:grid-cols-5">
        <x-field label="Search" name="search" class="lg:col-span-2">
            <input id="search" name="search" type="search" value="{{ $filter->search }}"
                   placeholder="Name or description" class="form-control">
        </x-field>

        <x-field label="Status" name="status">
            <select id="status" name="status" class="form-control">
                <option value="">All statuses</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected($filter->status === $status)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </x-field>

        <x-field label="Category" name="category_id">
            <select id="category_id" name="category_id" class="form-control">
                <option value="">All categories</option>
                @foreach ($categories as $id => $name)
                    <option value="{{ $id }}" @selected($filter->categoryId === $id)>{{ $name }}</option>
                @endforeach
            </select>
        </x-field>

        <div class="flex items-end gap-2">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    {{-- Bulk delete wraps the table so the row checkboxes belong to it. --}}
    <form method="POST" action="{{ route('admin.products.bulk-destroy') }}" class="mt-6"
          data-bulk-delete
          onsubmit="return confirm('Delete the selected products?')">
        @csrf
        @method('DELETE')

        <x-input-error field="ids" class="mb-2" />
        <x-input-error field="ids.*" class="mb-2" />

        <div class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between gap-3 border-b border-gray-200 px-4 py-3">
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" data-select-all
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    Select all on this page
                </label>

                <button type="submit" class="btn btn-danger">Delete selected</button>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-semibold tracking-wide text-gray-600 uppercase">
                            <th scope="col" class="px-4 py-3"><span class="sr-only">Select</span></th>
                            <th scope="col" class="px-4 py-3">Name</th>
                            <th scope="col" class="px-4 py-3">Category</th>
                            <th scope="col" class="px-4 py-3">Price</th>
                            <th scope="col" class="px-4 py-3">Stock</th>
                            <th scope="col" class="px-4 py-3">Status</th>
                            <th scope="col" class="px-4 py-3"><span class="sr-only">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="table-cell-base">
                                    <input type="checkbox" name="ids[]" value="{{ $product->id }}"
                                           data-row-checkbox
                                           aria-label="Select {{ $product->name }}"
                                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                                <td class="table-cell-base font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="table-cell-base text-gray-600">{{ $product->category->name }}</td>
                                <td class="table-cell-base text-gray-600">{{ Number::currency((float) $product->price) }}</td>
                                <td class="table-cell-base text-gray-600">{{ $product->stock }}</td>
                                <td class="table-cell-base">
                                    @if ($product->enabled)
                                        <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-800">Enabled</span>
                                    @else
                                        <span class="rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">Disabled</span>
                                    @endif
                                </td>
                                <td class="table-cell-base text-right">
                                    <a href="{{ route('admin.products.edit', $product) }}"
                                       class="font-medium text-indigo-600 hover:text-indigo-500">Edit</a>

                                    {{--
                                        The row's delete form lives outside this bulk-delete form, because
                                        HTML forbids nesting forms. The `form` attribute links them.
                                    --}}
                                    <button type="submit" form="delete-product-{{ $product->id }}"
                                            class="ml-3 font-medium text-red-600 hover:text-red-500">Delete</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">
                                    No products found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    {{-- One delete form per row, kept out of the bulk-delete form above. --}}
    @foreach ($products as $product)
        <form id="delete-product-{{ $product->id }}" method="POST" class="hidden"
              action="{{ route('admin.products.destroy', $product) }}"
              onsubmit="return confirm('Delete this product?')">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    <div class="mt-6">
        {{ $products->links() }}
    </div>
</x-layout>
