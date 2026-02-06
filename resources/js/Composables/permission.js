import { usePage } from "@inertiajs/vue3";

export function usePermission() {
    const hasPermission = (permission) => usePage().props.auth.permissions.includes(permission);
    const hasRole = (role) => usePage().props.auth.roles.includes(role);
    const hasPermissions = (permissions) => usePage().props.auth.permissions.some(p => permissions.includes(p));
    const user_id = usePage().props.auth.user.id;
    return {hasPermission, hasRole, hasPermissions, user_id};
}
