<x-app-layout>

<div class="max-w-4xl mx-auto py-6 space-y-6">

    <a href="{{ route('shop.index') }}" class="underline">&larr; Back to shop</a>

    @if(session('success'))
        <div class="p-3 rounded bg-green-100 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="p-3 rounded bg-red-100 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="p-6 rounded bg-white shadow">
        <h1 class="text-2xl font-bold">{{ $item->name }}</h1>
        <p class="mt-2 text-gray-700">{{ $item->description }}</p>

        <div class="mt-4 flex gap-6">
            <div><b>Price:</b> ฿{{ number_format($item->price) }}</div>
            <div><b>Stock:</b> {{ $item->stock }}</div>
        </div>
    </div>

    {{-- BUY (USER only) --}}
    @auth
        @if(auth()->user()->role === \App\Models\User::ROLE_USER)
        <div class="p-6 rounded bg-white shadow space-y-3">
            <h2 class="text-lg font-semibold">Buy</h2>

            <form method="POST" action="{{ route('orders.store', $item) }}" class="space-y-3">
                @csrf

                <div>
                    <label class="block text-sm font-medium">Quantity</label>
                    <input
                        type="number"
                        name="quantity"
                        min="1"
                        max="{{ max(1, $item->stock) }}"
                        value="1"
                        class="mt-1 w-40 border rounded p-2"
                        required
                    >
                    @error('quantity') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium">Shipping Address</label>
                    <textarea
                        name="shipping_address"
                        rows="2"
                        class="mt-1 w-full border rounded p-2"
                        required
                    ></textarea>
                    @error('shipping_address') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                </div>

                <button class="px-4 py-2 rounded bg-blue-600 text-white">
                    Place Order
                </button>
            </form>
        </div>
        @endif
    @endauth

    {{-- QUESTIONS --}}
    <div class="p-6 rounded bg-white shadow space-y-4">
        <h2 class="text-lg font-semibold">Questions</h2>

        {{-- Ask question (USER only) --}}
        @auth
            @if(auth()->user()->role === \App\Models\User::ROLE_USER)
            <form method="POST" action="{{ route('questions.store', $item) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-sm font-medium">Ask a question</label>
                    <input
                        type="text"
                        name="question_text"
                        class="mt-1 w-full border rounded p-2"
                        placeholder="Type your question..."
                        required
                    >
                    @error('question_text') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                </div>

                <button class="px-4 py-2 rounded bg-indigo-600 text-white">
                    Submit Question
                </button>
            </form>
            @endif
        @endauth

        <div class="space-y-4">
            @forelse($item->questions as $q)
                <div class="border rounded p-4">
                    <div class="text-sm text-gray-600">
                        {{ $q->asker_name ?? optional($q->asker)->name ?? 'Unknown' }}
                        • {{ $q->created_at?->format('Y-m-d H:i') }}
                    </div>

                    <div class="mt-2"><b>Q:</b> {{ $q->question_text }}</div>

                    @if($q->answer_text)
                        <div class="mt-3 p-3 rounded bg-gray-50">
                            <div class="text-sm text-gray-600">
                                Answered by {{ $q->admin_name ?? optional($q->admin)->name ?? 'Admin' }}
                            </div>
                            <div class="mt-1"><b>A:</b> {{ $q->answer_text }}</div>
                        </div>
                    @else
                        <div class="mt-3 text-sm text-gray-500">Pending admin answer</div>
                    @endif
                </div>
            @empty
                <div class="text-gray-500">No questions yet.</div>
            @endforelse
        </div>
    </div>

</div>


</x-app-layout>
