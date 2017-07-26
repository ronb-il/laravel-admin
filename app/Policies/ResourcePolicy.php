<?php

namespace App\Policies;

use Session;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResourcePolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    private function getUserPermissions($user)
    {
        $permissions = [];
        $allRoles = config('app.roles');

        foreach ($user['permissions']['roles'] as $role) {
            $permissions = array_unique(array_merge($permissions, $allRoles[$role]));
        }

        // if has edit, than can view also
        foreach ($permissions as $permission) {
            $splitter = strrpos($permission, "-");
            $resource = substr($permission, 0, $splitter);
            $action = substr($permission, $splitter + 1);
            // allow view on resource if can edit
            if ($action == "edit") {
                $permissions[] = $resource . '-view';
            }
        }

        // let's hardcode affiliate id's as resources
        if (isset($user['permissions']['affiliates'])) {
            foreach ($user['permissions']['affiliates'] as $affiliate) {
                $permissions[] = "affiliate-id-${affiliate}";
            }

            // ability to view all of the user's assigned affiliates
            if (count($user['permissions']['affiliates']) > 1) {
                $permissions[] = "affiliate-id-*";
            }
        }

        return $permissions;
    }

    public function __call($name, $arguments)
    {
        $user = $arguments[0];
        $resource = $arguments[1];

        if ($user->isAdmin()) return true;

        $permissions = $this->getUserPermissions($user);
        $resourceName = $resource->getName();

        // if requested to check affiliate-id
        if (strpos($resourceName, "affiliate-id-") === 0) {
            return isset($user['permissions']['affiliates']) ? in_array($resourceName, $permissions) : true;
        }

        $action_permission = $resourceName . "-$name";
        $affiliate_permission = 'affiliate-id-' . Session::get('affiliate_id');

        return (in_array($action_permission, $permissions) && (isset($user['permissions']['affiliates']) ? in_array($affiliate_permission, $permissions) : true));
    }
}
