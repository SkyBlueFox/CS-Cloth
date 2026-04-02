<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Shop
        </h2>
    </x-slot>
    <div class="max-w-5xl mx-auto p-6">
      <h1 class="text-2xl font-bold mb-4">Shop</h1>

      @if(session('success'))
        <div class="p-3 mb-4 bg-green-100 rounded">{{ session('success') }}</div>
      @endif

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @foreach($items as $item)
          <a class="border rounded p-4 hover:bg-gray-50" href="{{ route('shop.items.show', $item) }}">
            <div class="font-semibold">{{ $item->name }}</div>
            <div class="text-sm text-gray-600 line-clamp-2">{{ $item->description }}</div>
            <div class="mt-2">฿{{ $item->price }}</div>
            <div class="text-sm text-gray-600">Stock: {{ $item->stock }}</div>
          </a>
        @endforeach
      </div>

      <div class="mt-6">
        {{ $items->links() }}
      </div>
    </div>
</x-app-layout>>
