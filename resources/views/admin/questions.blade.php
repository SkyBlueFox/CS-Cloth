<x-app-layout>

<div class="max-w-6xl mx-auto p-6">

    <div class="flex items-center justify-between gap-3 mb-4">
        <h1 class="text-2xl font-semibold">Pending Questions</h1>

        <form method="GET" action="{{ route('admin.questions.index') }}" class="flex gap-2 items-center flex-wrap">
            <input type="text" name="q" value="{{ request('q') }}"
                   class="border rounded px-3 py-2 w-64"
                   placeholder="Search...">

            <label class="text-sm flex items-center gap-2">
                <input type="checkbox" name="unanswered" value="1" {{ request('unanswered') ? 'checked' : '' }}>
                Unanswered only
            </label>

            <button class="border rounded px-3 py-2">Search</button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-4 p-3 border rounded bg-green-50">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="mb-4 p-3 border rounded bg-red-50">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php $me = auth()->user(); @endphp

    <div class="space-y-4">
        @foreach($questions as $q)
            @php
                $answeredByMe = $q->admin_id !== null && $q->admin_id === $me->id;
                $answeredByOther = $q->admin_id !== null && $q->admin_id !== $me->id;
            @endphp

            <div class="border rounded p-4">
                <div class="text-sm text-gray-600">
                    <b>Item:</b> {{ $q->item?->name ?? ('#'.$q->item_id) }}
                    • <b>Asker:</b> {{ $q->asker_name ?? ($q->asker?->name ?? ('#'.$q->asker_id)) }}
                    • {{ optional($q->created_at)->format('Y-m-d H:i') }}
                </div>

                <div class="mt-2 whitespace-pre-wrap"><b>Q:</b> {{ $q->question_text }}</div>

                <div class="mt-4">
                    <b>A:</b>

                    @if($answeredByOther)
                        <div class="mt-2 p-3 bg-gray-50 rounded">
                            <div class="text-sm text-gray-600">
                                Answered by: <b>{{ $q->admin_name ?? ($q->admin?->name ?? ('#'.$q->admin_id)) }}</b>
                            </div>
                            <div class="mt-1 whitespace-pre-wrap">{{ $q->answer_text }}</div>
                        </div>
                    @else
                        <form method="POST" action="{{ route('admin.questions.answer', $q->id) }}" class="mt-2">
                            @csrf
                            @method('PATCH')

                            <textarea name="answer_text" rows="3" class="w-full border rounded p-2"
                                      placeholder="Type your answer...">{{ old('answer_text', $q->answer_text) }}</textarea>

                            <div class="mt-2 flex gap-2">
                                <button class="border rounded px-4 py-2">Save</button>

                                @if($answeredByMe && !empty($q->answer_text))
                                    <form method="POST" action="{{ route('admin.questions.answer.delete', $q->id) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="border rounded px-4 py-2"
                                                onclick="return confirm('Remove this answer?')">
                                            Remove
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">{{ $questions->links() }}</div>
</div>
</x-app-layout>
