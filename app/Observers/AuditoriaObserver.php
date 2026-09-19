<?php

namespace App\Observers;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AuditoriaObserver
{
    public function created(Model $model): void
    {
        $this->record($model, 'CREAR', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = Arr::except($model->getChanges(), ['updated_at']);
        if ($changes) {
            $this->record($model, 'ACTUALIZAR', Arr::only($model->getRawOriginal(), array_keys($changes)), $changes);
        }
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'ELIMINAR', $model->getAttributes(), null);
    }

    private function record(Model $model, string $action, ?array $before, ?array $after): void
    {
        $request = request();
        $user = $request->user();
        $requestId = $request->attributes->get('audit_request_id');
        if (! $requestId) {
            $requestId = (string) Str::uuid();
            $request->attributes->set('audit_request_id', $requestId);
        }
        $excluded = ['password', 'remember_token', 'access_token', 'refresh_token', 'token'];
        Auditoria::create([
            'usuario_id_accesos' => $user?->getAuthIdentifier(), 'usuario_nombre' => $user?->name,
            'origen' => app()->runningInConsole() && ! app()->runningUnitTests() ? 'CONSOLA' : ($user ? 'USUARIO' : 'SIN_SESION'),
            'accion' => $action, 'entidad' => $model->getTable(), 'entidad_id' => $model->getKey(),
            'valores_anteriores' => $before === null ? null : Arr::except($before, $excluded),
            'valores_nuevos' => $after === null ? null : Arr::except($after, $excluded),
            'ip' => $request->ip(), 'solicitud_id' => $requestId, 'created_at' => now(),
        ]);
    }
}
