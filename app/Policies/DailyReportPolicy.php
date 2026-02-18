<?php
namespace App\Policies;

use App\Models\User;
use App\Models\DailyReport;

class DailyReportPolicy
{
    /**
     * Super-Administrador puede hacer todo
     */
    public function before(User $user, $ability)
    {
        // Si el usuario tiene el rol de Super-Administrador, se le permite todo.
        if ($user->hasRole('Super-Administrador')) {
            return true;
        }
    }

    /**
     * Ver listado de reportes (por ejemplo, un administrador o conductor)
     */
    public function viewAny(User $user): bool
    {
        // El usuario puede ver los reportes si tiene el permiso 'daily_reports.view'
        return $user->can('daily_reports.view');
    }

    /**
     * Ver un reporte específico
     */
    public function view(User $user, DailyReport $dailyReport): bool
    {
        // El usuario puede ver un reporte si tiene el permiso 'daily_reports.view' o si tiene el permiso 'daily_reports.view_all'
        // También puede ver el reporte si es el creador
        return $user->can('daily_reports.view') 
            || $user->can('daily_reports.view_all') 
            || $user->id === $dailyReport->user_id;
    }

    /**
     * Crear un nuevo reporte
     */
    public function create(User $user): bool
    {
        // El usuario puede crear un reporte si tiene el permiso 'daily_reports.create'
        return $user->can('daily_reports.create');
    }

    /**
     * Actualizar un reporte
     */
    public function update(User $user, DailyReport $dailyReport): bool
    {
        // Si el usuario tiene el permiso para editar todos los reportes, puede actualizar cualquier reporte
        if ($user->can('daily_reports.edit_all')) {
            return true;
        }

        // El usuario puede editar su propio reporte si tiene el permiso 'daily_reports.edit'
        return $user->can('daily_reports.edit') && $user->id === $dailyReport->user_id;
    }

    /**
     * Eliminar un reporte
     */
    public function delete(User $user, DailyReport $dailyReport): bool
    {
        // El usuario debe tener el permiso 'daily_reports.delete' para eliminar el reporte
        if (! $user->can('daily_reports.delete')) {
            return false;
        }

        // Puede eliminar su propio reporte o, si tiene el permiso 'daily_reports.delete_all', puede eliminar cualquier reporte
        return $user->can('daily_reports.delete_all') || $user->id === $dailyReport->user_id;
    }

    /**
     * Restaurar un reporte eliminado
     */
    public function restore(User $user, DailyReport $dailyReport): bool
    {
        // Un usuario que puede eliminar el reporte también puede restaurarlo
        return $this->delete($user, $dailyReport);
    }

    /**
     * Eliminar permanentemente un reporte
     */
    public function forceDelete(User $user, DailyReport $dailyReport): bool
    {
        // Un usuario que puede eliminar el reporte también puede eliminarlo permanentemente
        return $this->delete($user, $dailyReport);
    }

    /**
     * Finalizar un reporte
     */
    public function finish(User $user, DailyReport $dailyReport): bool
    {
        // Solo el creador del reporte o un Super-Administrador puede finalizar el reporte
        return $user->can('daily_reports.finish') 
            && ($user->id === $dailyReport->user_id || $user->hasRole('Super-Administrador'));
    }
}
