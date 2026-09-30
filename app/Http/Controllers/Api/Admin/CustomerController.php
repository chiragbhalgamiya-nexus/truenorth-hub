<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\AddressResource;
use App\Http\Resources\UserResource;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    /**
     * Get paginated list of customers.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->query('per_page', 20), 100);
        $sortBy = $request->query('sort_by', 'created_at');
        $order = $request->query('order', 'desc');
        $search = $request->query('search');

        $query = User::where('role', 'customer')
            ->whereNull('deleted_at');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ilike', "%$search%")
                    ->orWhere('email', 'ilike', "%$search%");
            });
        }

        $query->orderBy($sortBy, $order);
        $customers = $query->paginate($perPage);

        return response()->json([
            'data' => UserResource::collection($customers->items()),
            'pagination' => [
                'total' => $customers->total(),
                'per_page' => $customers->perPage(),
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Get a specific customer's full profile with addresses.
     *
     * @param  User  $customer
     * @return JsonResponse
     */
    public function show(User $customer): JsonResponse
    {
        if ($customer->role !== 'customer') {
            return response()->json([
                'message' => 'Customer not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'phone' => $customer->phone,
            'status' => $customer->status,
            'created_at' => $customer->created_at,
            'addresses' => AddressResource::collection(
                $customer->addresses()->whereNull('deleted_at')->get()
            ),
        ], Response::HTTP_OK);
    }

    /**
     * Activate a customer account.
     *
     * @param  User  $customer
     * @return JsonResponse
     */
    public function activate(User $customer): JsonResponse
    {
        if ($customer->role !== 'customer') {
            return response()->json([
                'message' => 'Customer not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $oldStatus = $customer->status;
        $customer->update(['status' => 'active']);

        // Log audit
        AuditLog::create([
            'admin_id' => auth()->id(),
            'action' => 'activate',
            'subject_type' => 'User',
            'subject_id' => $customer->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'active'],
        ]);

        return response()->json([
            'id' => $customer->id,
            'status' => $customer->status,
            'updated_at' => $customer->updated_at,
        ], Response::HTTP_OK);
    }

    /**
     * Deactivate a customer account.
     *
     * @param  User  $customer
     * @return JsonResponse
     */
    public function deactivate(User $customer): JsonResponse
    {
        if ($customer->role !== 'customer') {
            return response()->json([
                'message' => 'Customer not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        $oldStatus = $customer->status;
        $customer->update(['status' => 'inactive']);

        // Log audit
        AuditLog::create([
            'admin_id' => auth()->id(),
            'action' => 'deactivate',
            'subject_type' => 'User',
            'subject_id' => $customer->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => 'inactive'],
        ]);

        return response()->json([
            'id' => $customer->id,
            'status' => $customer->status,
            'updated_at' => $customer->updated_at,
        ], Response::HTTP_OK);
    }
}
