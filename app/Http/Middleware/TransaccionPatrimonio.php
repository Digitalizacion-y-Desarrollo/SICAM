<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransaccionPatrimonio
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethodSafe() || ! $request->is('patrimonio/*')) return $next($request);

        try {
        return DB::transaction(function () use ($request, $next) {
            $id = $request->input('_request_id');
            if ($id !== null) {
                $request->validate(['_request_id' => ['required', 'uuid']]);
                $sesionHash = hash('sha256', $request->session()->getId());
                $peticionHash = hash('sha256', $request->method().'|'.$request->path());
                $inserted = DB::table('solicitudes_procesadas')->insertOrIgnore([
                    'id' => $id, 'sesion_hash' => $sesionHash, 'peticion_hash' => $peticionHash,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                $registro = DB::table('solicitudes_procesadas')->where('id', $id)->lockForUpdate()->first();
                if (! $inserted) {
                    abort_unless(hash_equals($registro->sesion_hash, $sesionHash) && hash_equals($registro->peticion_hash, $peticionHash), 409, 'La solicitud no corresponde a esta sesión o acción.');
                    return redirect()->to($registro->destino ?: url('/patrimonio/bienes'))->with('success', 'Esta solicitud ya había sido procesada.');
                }
            }
            $response = $next($request);
            if ($response->getStatusCode() >= 400 || in_array('errors', $request->session()->get('_flash.new', []), true)) {
                throw new \Illuminate\Http\Exceptions\HttpResponseException($response);
            }
            if ($id !== null) {
                if ($response->getStatusCode() >= 400 || $request->session()->has('errors')) DB::table('solicitudes_procesadas')->where('id', $id)->delete();
                else DB::table('solicitudes_procesadas')->where('id', $id)->update(['destino' => $response->headers->get('Location'), 'updated_at' => now()]);
            }

            return $response;
        });
        } catch (\Illuminate\Http\Exceptions\HttpResponseException $e) {
            return $e->getResponse();
        }
    }
}
