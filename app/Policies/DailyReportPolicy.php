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
        if ($user->hasRole('Super-Administrador')) {
            return true;
        }
    }

    /**
     * Ver listado
     */
    public function viewAny(User $user): bool
    {
        return $user->can('daily_reports.view');
    }

    /**
     * Ver uno específico
     */
    public function view(User $user, DailyReport $dailyReport): bool
    {
        if (!$user->can('daily_reports.view')) {
            return false;
        }

        // Administrador puede ver todos
        if ($user->hasRole('Administrador')) {
            return true;
        }

        // Otros solo los suyos
        return $user->id === $dailyReport->user_id;
    }

    /**
     * Crear
     */
    public function create(User $user): bool
    {
        return $user->can('daily_reports.create');
    }

    /**
     * Actualizar
     */
    public function update(User $user, DailyReport $dailyReport): bool
    {
        if (!$user->can('daily_reports.edit')) {
            return false;
        }

        if ($user->hasRole('Administrador')) {
            return true;
        }

        return $user->id === $dailyReport->user_id;
    }

    /**
     * Eliminar
     */
    public function delete(User $user, DailyReport $dailyReport): bool
    {
        if (!$user->can('daily_reports.delete')) {
            return false;
        }

        if ($user->hasRole('Administrador')) {
            return true;
        }

        return $user->id === $dailyReport->user_id;
    }

    /**
     * Restaurar
     */
    public function restore(User $user, DailyReport $dailyReport): bool
    {
        return $this->delete($user, $dailyReport);
    }

    /**
     * Eliminación permanente
     */
    public function forceDelete(User $user, DailyReport $dailyReport): bool
    {
        return $this->delete($user, $dailyReport);
    }
}
