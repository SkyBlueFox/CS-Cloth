<x-app-layout>
<div class="max-w-5xl mx-auto p-6">
    <h1 class="text-2xl font-semibold mb-4">My Questions</h1>

    <div class="space-y-4">
        @forelse($questions as $q)
            <div class="border rounded p-4">
                <div class="text-sm text-gray-600">
                    Item:
                    <a class="underline"
                       href="{{ route('shop.items.show', $q->item_id) }}">
                        {{ $q->item?->name ?? ('#'.$q->item_id) }}
                    </a>
                    • {{ optional($q->created_at)->format('Y-m-d H:i') }}
                </div>

                <div class="mt-2 whitespace-pre-wrap"><b>Q:</b> {{ $q->question_text }}</div>

                @if(!empty($q->answer_text))
                    <div class="mt-3 p-3 bg-gray-50 rounded">
                        <div class="text-sm text-gray-600">
                            Answered by <b>{{ $q->admin_name ?? ($q->admin?->name ?? 'Admin') }}</b>
                        </div>
                        <div class="mt-1 whitespace-pre-wrap"><b>A:</b> {{ $q->answer_text }}</div>
                    </div>
                @else
                    <div class="mt-3 text-sm text-gray-500">No answer yet.</div>
                @endif
            </div>
        @empty
            <div class="text-sm text-gray-500">No questions yet.</div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $questions->links() }}
    </div>
</div>
</x-app-layout>
