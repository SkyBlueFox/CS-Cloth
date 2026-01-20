<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            My Orders
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">

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
        </div>
    </div>
</x-app-layout>
