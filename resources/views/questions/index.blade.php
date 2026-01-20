<x-app-layout>

<div class="max-w-5xl mx-auto py-6 space-y-4">

    <h1 class="text-2xl font-bold">My Questions</h1>

    @if(session('success'))
        <div class="p-3 rounded bg-green-100 text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow rounded overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left p-3">Item</th>
                    <th class="text-left p-3">Question</th>
                    <th class="text-left p-3">Status</th>
                    <th class="text-left p-3">Created</th>
                </tr>
            </thead>
            <tbody>
                @forelse($questions as $q)
                    <tr class="border-t">
                        <td class="p-3">
                            <a class="underline" href="{{ route('shop.items.show', $q->item) }}">
                                {{ $q->item?->name ?? 'Item' }}
                            </a>
                        </td>
                        <td class="p-3">{{ $q->question_text }}</td>
                        <td class="p-3">
                            @if($q->answer_text)
                                <span class="px-2 py-1 rounded bg-green-100 text-green-800">Answered</span>
                            @else
                                <span class="px-2 py-1 rounded bg-yellow-100 text-yellow-800">Pending</span>
                            @endif
                        </td>
                        <td class="p-3">{{ $q->created_at?->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="p-3 text-gray-500" colspan="4">No questions.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>{{ $questions->links() }}</div>

</div>


</x-app-layout>
