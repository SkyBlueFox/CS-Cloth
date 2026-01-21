<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Reports') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <h3 class="text-lg font-bold mb-4">All Reports</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reporter</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reported Admin</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase min-w-[200px]">Question</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase min-w-[200px]">Reported Answer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                            </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($reports as $report)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap align-top">{{ $report->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap align-top">{{ $report->reporter_name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap align-top text-red-600 font-semibold">{{ $report->admin_name }}</td>

                                    <td class="px-6 py-4 align-top text-sm text-gray-700">
                                        {{ Str::limit($report->question_text_snapshot, 100) }}
                                    </td>

                                    <td class="px-6 py-4 align-top text-sm text-red-600">
                                        {{ Str::limit($report->answer_text_snapshot, 100) }}
                                    </td>

                                    <td class="px-6 py-4 align-top text-sm">
                                        {{ Str::limit($report->reason, 50) }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap align-top">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            {{ $report->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                            {{ $report->status === 'resolved' ? 'bg-green-100 text-green-800' : '' }}
                                            {{ $report->status === 'dismissed' ? 'bg-gray-100 text-gray-800' : '' }}">
                                            {{ ucfirst($report->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap align-top text-sm font-medium">
                                        @if($report->status === \App\Models\Report::STATUS_PENDING)
                                            <div class="flex space-x-2">
                                                <form action="{{ route('superadmin.reports.resolve', $report->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="text-gray-700 bg-green-400 hover:bg-green-700 px-3 py-1 rounded-md text-xs transition">
                                                        Resolve
                                                    </button>
                                                </form>

                                                <form action="{{ route('superadmin.reports.dismiss', $report->id) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <button type="submit"
                                                            class="text-gray-700 bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded-md text-xs transition">
                                                        Dismiss
                                                    </button>
                                                </form>
                                            </div>
                                        @else
                                            <span class="text-gray-400 text-xs italic">
            Action taken
        </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $reports->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
