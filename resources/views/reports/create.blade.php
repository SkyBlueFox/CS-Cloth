<x-app-layout>
    <div class="max-w-2xl mx-auto py-12">
        <div class="bg-white p-6 rounded shadow-sm">
            <h2 class="text-xl font-bold mb-4">Report Offensive Answer</h2>

            <div class="mb-6 p-4 bg-gray-50 rounded border text-sm">
                <p><b>Question:</b> {{ $question->question_text }}</p>
                <p class="mt-2 text-red-600"><b>Answered by {{ $question->admin_name }}:</b> {{ $question->answer_text }}</p>
            </div>

            <form action="{{ route('reports.store', $question) }}" method="POST">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Reason for reporting</label>
                    <textarea
                        name="reason"
                        rows="4"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-red-500 focus:border-red-500"
                        placeholder="Please explain why this answer is offensive or incorrect..."
                        required
                    ></textarea>
                    @error('reason') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('questions.index') }}" class="px-4 py-2 text-gray-600">Cancel</a>
                    <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 font-bold">
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
