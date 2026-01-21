<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Items') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="flex justify-end mb-4">
                <a href="{{ route('admin.items.create') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 font-bold shadow-sm transition">
                    + Add New Item
                </a>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if(session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Image</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($items as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->image_path)
                                            <img src="{{ Storage::url($item->image_path) }}" class="h-12 w-12 object-cover rounded border border-gray-200">
                                        @else
                                            <div class="h-12 w-12 bg-gray-100 rounded flex items-center justify-center text-xs text-gray-400">No Img</div>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $item->name }}</td>

                                    <td class="px-6 py-4 whitespace-nowrap">฿{{ number_format($item->price) }}</td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->stock == 0)
                                            <span class="text-red-600 font-bold">Out of Stock</span>
                                        @elseif($item->stock < 10)
                                            <span class="text-orange-600 font-bold">{{ $item->stock }} (Low)</span>
                                        @else
                                            <span class="text-green-600">{{ $item->stock }}</span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <form action="{{ route('admin.items.toggle', $item->id) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit"
                                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full cursor-pointer transition
                                                    {{ $item->is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-800 hover:bg-gray-200' }}">
                                                {{ $item->is_active ? 'Active' : 'Hidden' }}
                                            </button>
                                        </form>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3">
                                        <a href="{{ route('admin.items.edit', $item->id) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>

                                        <form action="{{ route('admin.items.destroy', $item->id) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Are you sure you want to delete this item?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $items->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
