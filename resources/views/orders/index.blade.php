@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto p-6">
  <h1 class="text-2xl font-bold mb-4">My Orders</h1>

  @if(session('success'))
    <div class="p-3 mb-4 bg-green-100 rounded">{{ session('success') }}</div>
  @endif

  <div class="space-y-3">
    @foreach($orders as $o)
      <div class="border rounded p-4">
        <div class="flex justify-between flex-wrap gap-2">
          <div>
            <div class="font-semibold">Order #{{ $o->id }}</div>
            <div class="text-sm text-gray-600">{{ $o->created_at }}</div>
          </div>
          <div>
            <div>Status: <span class="font-semibold">{{ $o->status }}</span></div>
            <div>Total: ฿{{ $o->total_price }}</div>
          </div>
        </div>

        <div class="mt-3">
          @foreach($o->items as $oi)
            <div class="text-sm">
              {{ $oi->item->name ?? 'Item' }} × {{ $oi->quantity }}
              (฿{{ $oi->price_at_purchase }})
            </div>
          @endforeach
        </div>

        <div class="mt-3 flex gap-2">
          @if($o->status === 'pending')
            <form method="POST" action="{{ route('orders.cancel', $o) }}">
              @csrf
              <button class="px-4 py-2 bg-gray-800 text-white rounded">Cancel</button>
            </form>
          @endif

          @if($o->status === 'shipped')
            <form method="POST" action="{{ route('orders.refund.request', $o) }}">
              @csrf
              <button class="px-4 py-2 bg-orange-600 text-white rounded">Request refund</button>
            </form>
          @endif
        </div>
      </div>
    @endforeach
  </div>

  <div class="mt-6">
    {{ $orders->links() }}
  </div>
</div>
@endsection
