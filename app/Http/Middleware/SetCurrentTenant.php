<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SetCurrentTenant
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && empty($user->is_platform_admin) && $user->company_id) {
            app()->instance('currentCompanyId', (int) $user->company_id);
        } else {
            app()->instance('currentCompanyId', null);
        }

        User::addGlobalScope('company', function (Builder $builder) {
            $companyId = app()->bound('currentCompanyId') ? app('currentCompanyId') : null;
            if ($companyId) {
                $builder->where($builder->getModel()->getTable().'.company_id', $companyId);

                return;
            }

            if (! app()->runningInConsole()) {
                $builder->whereRaw('1 = 0');
            }
        });

        return $next($request);
    }
}
