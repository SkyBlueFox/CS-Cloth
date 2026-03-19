<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserAddress;
use App\Support\ApiData;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->get();

        return response()->json([
            'data' => $addresses->map(fn (UserAddress $address) => ApiData::address($address))->values()->all(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateAddress($request);
        $address = $this->persistAddress($request, new UserAddress(), $validated);

        return response()->json([
            'address' => ApiData::address($address),
        ], 201);
    }

    public function update(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $validated = $this->validateAddress($request);
        $address = $this->persistAddress($request, $address, $validated);

        return response()->json([
            'address' => ApiData::address($address),
        ]);
    }

    public function destroy(Request $request, UserAddress $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $wasDefault = $address->is_default;
        $address->delete();

        if ($wasDefault) {
            $next = $request->user()->addresses()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return response()->json(['message' => 'Address deleted.']);
    }

    private function validateAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'line_1' => ['required', 'string', 'max:255'],
            'line_2' => ['nullable', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:255'],
            'province' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'is_default' => ['nullable', 'boolean'],
        ]);
    }

    private function persistAddress(Request $request, UserAddress $address, array $validated): UserAddress
    {
        if (($validated['is_default'] ?? false) === true) {
            $request->user()->addresses()->update(['is_default' => false]);
        }

        if (!$request->user()->addresses()->exists()) {
            $validated['is_default'] = true;
        }

        $address->fill($validated);
        $address->user_id = $request->user()->id;
        $address->save();

        return $address->fresh();
    }
}
