<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Spatie\Multitenancy\Models\Tenant;

class SetTenantFromUser
{
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->company_id) {
            $company = \App\Models\Company::find(Auth::user()->company_id);
            if ($company) {
                tenant()->initialize($company);
            }
        }
        return $next($request);
    }
}
