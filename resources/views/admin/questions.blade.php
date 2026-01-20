<x-app-layout>

<div class="max-w-5xl mx-auto py-6 space-y-4">

    <h1 class="text-2xl font-bold">Pending Questions</h1>

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

    <div class="space-y-4">
        @forelse($questions as $q)
            <div class="bg-white shadow rounded p-5 space-y-2">
                <div class="text-sm text-gray-600">
                    Item:
                    <a class="underline" href="{{ route('shop.items.show', $q->item) }}">
                        {{ $q->item?->name ?? 'Item' }}
                    </a>
                    • Asked by: {{ $q->asker_name ?? optional($q->asker)->name ?? 'User' }}
                    • {{ $q->created_at?->format('Y-m-d H:i') }}
                </div>

                <div><b>Q:</b> {{ $q->question_text }}</div>

                <form method="POST" action="{{ route('admin.questions.answer', $q) }}" class="space-y-2">
                    @csrf
                    @method('PATCH')

                    <textarea name="answer_text" rows="3" class="w-full border rounded p-2" required></textarea>
                    @error('answer_text') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror

                    <button class="px-4 py-2 rounded bg-blue-600 text-white">
                        Submit Answer
                    </button>
                </form>
            </div>
        @empty
            <div class="text-gray-500">No pending questions.</div>
        @endforelse
    </div>

    <div>{{ $questions->links() }}</div>

</div>

</x-app-layout>
