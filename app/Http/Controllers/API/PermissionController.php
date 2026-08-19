<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\AppBaseController;
use App\Http\Resources\PermissionCollection;
use App\Http\Resources\PermissionResource;
use App\Repositories\PermissionRepository;
use Illuminate\Http\Request;

class PermissionController extends AppBaseController
{
    /** @var PermissionRepository */
    private $permissionRepository;

    public function __construct(PermissionRepository $permissionRepo)
    {
        $this->permissionRepository = $permissionRepo;
    }

    public function getPermissions(Request $request)
    {
        $perPage = getPageSize($request);
        $user = $request->user();
        $company = ($user && $user->company_id)
            ? \App\Models\Company::withoutGlobalScopes()->find($user->company_id)
            : null;

        if ($company && is_array($company->allowed_permissions)) {
            $permissions = \App\Models\Permission::whereIn('name', $company->allowed_permissions)->paginate($perPage);
        } else {
            $permissions = $this->permissionRepository->paginate($perPage);
        }

        PermissionResource::usingWithCollection();

        return new PermissionCollection($permissions);
    }
}
