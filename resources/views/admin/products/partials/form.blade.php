{{--
    Shared by the create and edit screens.

    @param \App\Models\Product|null $product    Existing product when editing.
    @param \Illuminate\Support\Collection $categories  Category names keyed by id.
--}}
@php
    $product ??= null;
@endphp

<div class="grid gap-4 sm:grid-cols-2">
    <x-field label="Name" name="name" :required="true" class="sm:col-span-2">
        <input id="name" name="name" type="text" required maxlength="255"
               value="{{ old('name', $product?->name) }}" class="form-control">
    </x-field>

    <x-field label="Category" name="category_id" :required="true">
        <select id="category_id" name="category_id" required class="form-control">
            <option value="">Select a category</option>
            @foreach ($categories as $id => $name)
                <option value="{{ $id }}" @selected((int) old('category_id', $product?->category_id) === $id)>
                    {{ $name }}
                </option>
            @endforeach
        </select>
    </x-field>

    <x-field label="Price" name="price" :required="true">
        <input id="price" name="price" type="number" step="0.01" min="0" required
               value="{{ old('price', $product?->price) }}" class="form-control">
    </x-field>

    <x-field label="Stock" name="stock" :required="true">
        <input id="stock" name="stock" type="number" step="1" min="0" required
               value="{{ old('stock', $product?->stock ?? 0) }}" class="form-control">
    </x-field>

    <x-field label="Status" name="enabled">
        {{--
            The hidden input makes the browser submit a value even when the
            checkbox is unchecked, so "enabled" is always present and the
            form request can require it.
        --}}
        <input type="hidden" name="enabled" value="0">
        <label class="flex items-center gap-2 pt-2 text-sm text-gray-700">
            <input id="enabled" name="enabled" type="checkbox" value="1"
                   @checked(old('enabled', $product?->enabled ?? true))
                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
            Visible in the catalogue
        </label>
    </x-field>

    <x-field label="Description" name="description" class="sm:col-span-2">
        <textarea id="description" name="description" rows="4" maxlength="5000"
                  class="form-control">{{ old('description', $product?->description) }}</textarea>
    </x-field>
</div>
