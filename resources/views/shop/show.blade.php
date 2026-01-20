<x-app-layout>

<div class="max-w-5xl mx-auto p-6">
  <a class="text-sm underline" href="{{ route('shop.index') }}">← Back</a>

  @if(session('success'))
    <div class="p-3 my-4 bg-green-100 rounded">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="p-3 my-4 bg-red-100 rounded">
      <ul class="list-disc pl-5">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="mt-4 border rounded p-4">
    <h1 class="text-2xl font-bold">{{ $item->name }}</h1>
    <p class="mt-2 text-gray-700">{{ $item->description }}</p>
    <div class="mt-2">Price: ฿{{ $item->price }}</div>
    <div class="text-sm text-gray-600">Stock: {{ $item->stock }}</div>
  </div>

  @auth
  <div class="mt-4 border rounded p-4">
    <h2 class="font-semibold mb-2">Place order</h2>
    <form method="POST" action="{{ route('orders.store', $item) }}" class="flex gap-2 items-end flex-wrap">
      @csrf
      <div>
        <label class="text-sm">Qty</label>
        <input name="quantity" type="number" min="1" max="10" value="1" class="border rounded p-2 w-24">
      </div>
      <div class="flex-1 min-w-[240px]">
        <label class="text-sm">Shipping address (optional)</label>
        <input name="shipping_address" class="border rounded p-2 w-full" placeholder="Address...">
      </div>
      <button class="px-4 py-2 bg-black text-white rounded">Order</button>
    </form>

    @if(isset(auth()->user()->wallet_balance))
      <div class="text-sm text-gray-600 mt-2">Wallet: ฿{{ auth()->user()->wallet_balance }}</div>
    @endif
  </div>
  @endauth
</div>
</x-app-layout>>
