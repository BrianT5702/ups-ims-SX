<?php

namespace App\Services;

use App\Models\DeliveryOrder;
use App\Support\TenantUser;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DeliveryOrderListQuery
{
    public static function isPrivilegedUser($user): bool
    {
        return $user && (
            $user->hasRole('Admin')
            || $user->hasRole('Super Admin')
            || $user->hasRole('Department1')
            || $user->hasRole('Department 1')
            || $user->hasRole('Department2')
            || $user->hasRole('Department 2')
            || $user->hasRole('Department2 Admin')
            || $user->hasRole('Department 2 Admin')
        );
    }

    public static function build(
        $user,
        ?string $searchTerm = null,
        ?int $filterCustomerId = null,
        ?string $startDate = null,
        ?string $endDate = null,
    ): Builder {
        $isPrivileged = self::isPrivilegedUser($user);

        return DeliveryOrder::with(['customer', 'customerSnapshot', 'salesman', 'user', 'updatedBy'])
            ->withCount('items')
            ->when(!$isPrivileged, function ($q) use ($user) {
                return $q->where('user_id', TenantUser::resolveId($user));
            })
            ->when($filterCustomerId, function ($q) use ($filterCustomerId) {
                return $q->where('cust_id', $filterCustomerId);
            })
            ->when($searchTerm, function ($q) use ($searchTerm) {
                return $q->where(function ($query) use ($searchTerm) {
                    $query->where('do_num', 'like', '%' . $searchTerm . '%')
                        ->orWhereHas('customer', function ($subQuery) use ($searchTerm) {
                            $subQuery->where('cust_name', 'like', '%' . $searchTerm . '%')
                                ->orWhere('account', 'like', '%' . $searchTerm . '%');
                        });
                });
            })
            ->when($startDate && $endDate, function ($q) use ($startDate, $endDate) {
                return $q->whereBetween('date', [
                    Carbon::parse($startDate)->toDateString(),
                    Carbon::parse($endDate)->toDateString(),
                ]);
            })
            ->orderByDesc('date')
            ->orderByDesc('created_at');
    }
}
