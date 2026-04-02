<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Edit Admin: {{ $admin->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">

                <form method="POST" action="{{ route('superadmin.admins.update', $admin->id) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700" for="name">Name</label>
                        <input class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                               id="name" type="text" name="name" value="{{ old('name', $admin->name) }}" required />
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700" for="email">Email</label>
                        <input class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                               id="email" type="email" name="email" value="{{ old('email', $admin->email) }}" required />
                        @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block font-medium text-sm text-gray-700" for="password">New Password (Optional)</label>
                        <input class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm block mt-1 w-full"
                               id="password" type="password" name="password" placeholder="Leave blank to keep current password" />
                        @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('superadmin.create-admin') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                            Cancel
                        </a>
                        <button type="submit" class="px-4 py-2 bg-blue-800 text-white rounded-md hover:bg-blue-700">
                            Update Admin
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>
