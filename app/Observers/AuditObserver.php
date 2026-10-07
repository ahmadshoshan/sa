<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    public function created(Model $model): void
    {
        $this->log($model, 'created', null, $model->attributesToArray());
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        $old = array_intersect_key($model->getOriginal(), $changes);

        $this->log($model, 'updated', $old, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->log($model, 'deleted', $model->attributesToArray(), null);
    }

    private function log(Model $model, string $action, ?array $oldValues, ?array $newValues): void
    {
        $oldValues = $this->clean($oldValues);
        $newValues = $this->clean($newValues);

        $ip = null;

        if (app()->bound('request')) {
            $ip = request()->ip();
        }

        AuditLog::create([
            'user_id' =>  Auth::id(),
            'action' => $action,
            'table_name' => $model->getTable(),
            'record_id' => $model->getKey(),
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ip,
        ]);
    }

    private function clean(?array $values): ?array
    {
        if (is_null($values)) {
            return null;
        }

        unset(
            $values['password'],
            $values['remember_token']
        );

        return $values;
    }
}