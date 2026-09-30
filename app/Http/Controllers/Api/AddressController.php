<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Http\Resources\AddressResource;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AddressController extends Controller
{
    /**
     * Get all addresses for the authenticated user.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $addresses = auth()->user()->addresses()->whereNull('deleted_at')->get();

        return response()->json([
            'data' => AddressResource::collection($addresses),
        ], Response::HTTP_OK);
    }

    /**
     * Store a new address.
     *
     * @param  StoreAddressRequest  $request
     * @return JsonResponse
     */
    public function store(StoreAddressRequest $request): JsonResponse
    {
        // Check max addresses
        $count = auth()->user()->addresses()->whereNull('deleted_at')->count();
        if ($count >= 10) {
            return response()->json([
                'message' => 'Maximum number of addresses (10) reached.',
                'errors' => ['addresses' => ['Maximum number of addresses reached.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $data = $request->validated();
        $data['user_id'] = auth()->id();
        $data['country'] = $data['country'] ?? 'CA';

        // If setting as default, unset previous default
        if ($data['is_default'] ?? false) {
            auth()->user()->addresses()->update(['is_default' => false]);
        }

        $address = Address::create($data);

        return response()->json(
            new AddressResource($address),
            Response::HTTP_CREATED
        );
    }

    /**
     * Get a specific address.
     *
     * @param  Address  $address
     * @return JsonResponse
     */
    public function show(Address $address): JsonResponse
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json(
            new AddressResource($address),
            Response::HTTP_OK
        );
    }

    /**
     * Update a specific address.
     *
     * @param  UpdateAddressRequest  $request
     * @param  Address  $address
     * @return JsonResponse
     */
    public function update(UpdateAddressRequest $request, Address $address): JsonResponse
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], Response::HTTP_FORBIDDEN);
        }

        $address->update($request->validated());

        return response()->json(
            new AddressResource($address),
            Response::HTTP_OK
        );
    }

    /**
     * Delete a specific address.
     *
     * @param  Address  $address
     * @return JsonResponse
     */
    public function destroy(Address $address): JsonResponse
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Check if address is default
        if ($address->is_default) {
            return response()->json([
                'message' => 'Cannot delete default address.',
                'errors' => ['address' => ['Cannot delete default address.']],
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $address->delete();

        return response()->json(
            status: Response::HTTP_NO_CONTENT
        );
    }

    /**
     * Set an address as default.
     *
     * @param  Address  $address
     * @return JsonResponse
     */
    public function setDefault(Address $address): JsonResponse
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Unset previous default
        auth()->user()->addresses()->update(['is_default' => false]);

        // Set this as default
        $address->update(['is_default' => true]);

        return response()->json(
            new AddressResource($address),
            Response::HTTP_OK
        );
    }
}
