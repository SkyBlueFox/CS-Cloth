<x-app-layout>
<div class="max-w-6xl mx-auto py-6 space-y-6">

    @if(session('success'))
        <div class="p-3 rounded bg-green-100 text-green-800">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="p-3 rounded bg-red-100 text-red-800">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded shadow p-6">
        <h1 class="text-2xl font-bold">Pending Questions</h1>
        <p class="text-sm text-gray-600 mt-1">คำถามที่ยังไม่มีคำตอบ</p>

        <div class="mt-4 space-y-4">
            @forelse($pending as $q)
                <div class="border rounded p-4 space-y-2">
                    <div class="text-xs text-gray-600">
                        Item:
                        <a class="underline" href="{{ route('shop.items.show', $q->item_id) }}">
                            {{ $q->item?->name ?? ('#'.$q->item_id) }}
                        </a>
                        • Asked by: {{ $q->asker_name ?? ($q->asker?->name ?? 'User') }}
                        • {{ optional($q->created_at)->format('Y-m-d H:i') }}
                    </div>

                    <div><b>Q:</b> {{ $q->question_text }}</div>

                    <form method="POST" action="{{ route('admin.questions.answer', $q->id) }}" class="space-y-2">
                        @csrf
                        @method('PATCH')
                        <textarea name="answer_text" rows="3" class="w-full border rounded p-2" required></textarea>
                        @error('answer_text') <div class="text-red-600 text-sm">{{ $message }}</div> @enderror
                        <button class="px-4 py-2 rounded bg-blue-600 text-white">Submit Answer</button>
                    </form>
                </div>
            @empty
                <div class="text-gray-500">ไม่มีคำถามค้าง</div>
            @endforelse
        </div>

        <div class="mt-4">{{ $pending->links() }}</div>
    </div>

    <div class="bg-white rounded shadow p-6">
        <h2 class="text-xl font-bold">My Answer History</h2>
        <p class="text-sm text-gray-600 mt-1">คำถามที่คุณตอบไปแล้ว (admin คนนี้)</p>

        <div class="mt-4 space-y-4">
            @forelse($answeredByMe as $q)
                <div class="border rounded p-4 space-y-2">
                    <div class="text-xs text-gray-600">
                        Item:
                        <a class="underline" href="{{ route('shop.items.show', $q->item_id) }}">
                            {{ $q->item?->name ?? ('#'.$q->item_id) }}
                        </a>
                        • {{ optional($q->updated_at)->format('Y-m-d H:i') }}
                    </div>

                    <div><b>Q:</b> {{ $q->question_text }}</div>
                    <div class="p-3 rounded bg-gray-50"><b>A:</b> {{ $q->answer_text }}</div>

                    <form method="POST" action="{{ route('admin.questions.answer.delete', $q->id) }}">
                        @csrf
                        @method('DELETE')
                        <button class="px-3 py-2 rounded bg-red-600 text-white"
                                onclick="return confirm('ลบคำตอบนี้?');">
                            Delete Answer
                        </button>
                    </form>
                </div>
            @empty
                <div class="text-gray-500">ยังไม่มีประวัติการตอบ</div>
            @endforelse
        </div>

        <div class="mt-4">{{ $answeredByMe->links() }}</div>
    </div>

</div>
</x-app-layout>
