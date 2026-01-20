<x-app-layout>

<div class="max-w-5xl mx-auto p-6">

    {{-- Back --}}
    <div class="mb-4">
        <a href="{{ route('shop.index') }}" class="underline text-sm">← Back to shop</a>
    </div>

    {{-- Flash / Errors --}}
    @if(session('success'))
        <div class="mb-4 p-3 border rounded bg-green-50">
            {{ session('success') }}
        </div>
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

    {{-- Item info --}}
    <div class="border rounded p-4">
        <h1 class="text-2xl font-semibold">{{ $item->name }}</h1>

        @if(!empty($item->image_path))
            <div class="mt-3">
                <img src="{{ asset('storage/' . $item->image_path) }}"
                     alt="{{ $item->name }}"
                     class="max-h-80 rounded border">
            </div>
        @endif

        <div class="mt-3 text-gray-700 whitespace-pre-wrap">
            {{ $item->description }}
        </div>

        <div class="mt-3 flex gap-6 flex-wrap">
            <div><b>Price:</b> ฿{{ $item->price }}</div>
            <div><b>Stock:</b> {{ $item->stock }}</div>
        </div>
    </div>

    {{-- QUESTIONS --}}
@php
    $questions = $item->questions()->latest()->get();
@endphp

<div class="mt-8 border rounded p-4">
    <h2 class="text-xl font-semibold mb-3">Questions</h2>

    @if(session('success'))
        <div class="mb-4 p-3 border rounded bg-green-50">{{ session('success') }}</div>
    @endif

    @auth
        @if((auth()->user()->role ?? null) === 'user')
            <form method="POST" action="{{ route('shop.questions.store', $item->id) }}">
                @csrf
                <textarea name="question_text" rows="3" class="w-full border rounded p-2"
                          placeholder="Ask a question...">{{ old('question_text') }}</textarea>
                @error('question_text')
                    <div class="text-sm text-red-600 mt-1">{{ $message }}</div>
                @enderror
                <button class="mt-2 border rounded px-4 py-2">Send</button>
            </form>
        @endif
    @endauth

    <div class="mt-5 space-y-3">
        @forelse($questions as $q)
            <div class="border rounded p-3">
                <div class="text-sm text-gray-600">
                    <b>{{ $q->asker_name ?? ($q->asker->name ?? 'User') }}</b>
                    • {{ optional($q->created_at)->format('Y-m-d H:i') }}
                </div>
                <div class="mt-1 whitespace-pre-wrap"><b>Q:</b> {{ $q->question_text }}</div>

                @if(!empty($q->answer_text))
                    <div class="mt-2 p-3 bg-gray-50 rounded">
                        <div class="text-sm text-gray-600">
                            Answered by <b>{{ $q->admin_name ?? ($q->admin->name ?? 'Admin') }}</b>
                        </div>
                        <div class="mt-1 whitespace-pre-wrap"><b>A:</b> {{ $q->answer_text }}</div>
                    </div>
                @else
                    <div class="mt-2 text-sm text-gray-500">No answer yet.</div>
                @endif
            </div>
        @empty
            <div class="text-sm text-gray-500">No questions yet.</div>
        @endforelse
    </div>
</div>

</x-app-layout>
