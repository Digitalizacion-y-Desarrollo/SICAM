<?php

namespace App\Services;

use App\Models\Bien;
use App\Models\Categoria;
use App\Models\Licencia;
use App\Models\Proveedor;
use App\Models\Responsable;
use App\Models\Sistema;
use Illuminate\Support\Facades\Gate;

class BuscarEnSicam
{
    public function ejecutar(string $termino): array
    {
        $termino = trim($termino);

        if (mb_strlen($termino) < 2) {
            return [];
        }

        $resultados = [];

        if (Gate::allows('bienes.ver')) {
            $bienes = Bien::query()
                ->where(function ($query) use ($termino) {
                    $query->where('folio_sicam', 'like', "%{$termino}%")
                        ->orWhere('numero_patrimonial', 'like', "%{$termino}%")
                        ->orWhere('numero_serie', 'like', "%{$termino}%")
                        ->orWhere('nombre', 'like', "%{$termino}%")
                        ->orWhere('marca', 'like', "%{$termino}%")
                        ->orWhere('modelo', 'like', "%{$termino}%");
                })
                ->latest('updated_at')
                ->limit(5)
                ->get();

            foreach ($bienes as $bien) {
                $resultados[] = [
                    'tipo' => 'Bien',
                    'titulo' => $bien->nombre,
                    'detalle' => collect([$bien->folio_sicam, $bien->numero_patrimonial, $bien->marca])->filter()->implode(' · '),
                    'url' => route('patrimonio.bienes.show', $bien),
                ];
            }
        }

        if (Gate::allows('sistemas.ver')) {
            $sistemas = Sistema::query()
                ->where(function ($query) use ($termino) {
                    $query->where('clave', 'like', "%{$termino}%")
                        ->orWhere('nombre', 'like', "%{$termino}%")
                        ->orWhere('descripcion', 'like', "%{$termino}%");
                })
                ->latest('updated_at')
                ->limit(5)
                ->get();

            foreach ($sistemas as $sistema) {
                $resultados[] = [
                    'tipo' => 'Sistema',
                    'titulo' => $sistema->nombre,
                    'detalle' => collect([$sistema->clave, Sistema::ESTADOS[$sistema->estado] ?? $sistema->estado])->filter()->implode(' · '),
                    'url' => route('software.sistemas.show', $sistema),
                ];
            }
        }

        if (Gate::allows('licencias.ver')) {
            $licencias = Licencia::query()
                ->where(function ($query) use ($termino) {
                    $query->where('clave', 'like', "%{$termino}%")
                        ->orWhere('nombre', 'like', "%{$termino}%")
                        ->orWhere('producto', 'like', "%{$termino}%")
                        ->orWhere('fabricante', 'like', "%{$termino}%");
                })
                ->latest('updated_at')
                ->limit(5)
                ->get();

            foreach ($licencias as $licencia) {
                $resultados[] = [
                    'tipo' => 'Licencia',
                    'titulo' => $licencia->nombre,
                    'detalle' => collect([$licencia->clave, $licencia->producto, Licencia::ESTADOS[$licencia->estado] ?? $licencia->estado])->filter()->implode(' · '),
                    'url' => route('licencia.index', ['buscar' => $licencia->clave]),
                ];
            }
        }

        if (Gate::allows('responsables.ver')) {
            $responsables = Responsable::query()
                ->where(function ($query) use ($termino) {
                    $query->where('numero_empleado', 'like', "%{$termino}%")
                        ->orWhere('nombre', 'like', "%{$termino}%")
                        ->orWhere('apellido_paterno', 'like', "%{$termino}%")
                        ->orWhere('apellido_materno', 'like', "%{$termino}%")
                        ->orWhere('correo', 'like', "%{$termino}%");
                })
                ->latest('updated_at')
                ->limit(5)
                ->get();

            foreach ($responsables as $responsable) {
                $resultados[] = [
                    'tipo' => 'Responsable',
                    'titulo' => $responsable->nombre_completo,
                    'detalle' => collect([$responsable->numero_empleado, $responsable->cargo, $responsable->dependencia_id_accesos])->filter()->implode(' · '),
                    'url' => route('patrimonio.responsables.show', $responsable),
                ];
            }
        }

        if (Gate::allows('proveedores.ver')) {
            $proveedores = Proveedor::query()
                ->where(function ($query) use ($termino) {
                    $query->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('razon_social', 'like', "%{$termino}%")
                        ->orWhere('rfc', 'like', "%{$termino}%")
                        ->orWhere('contacto_nombre', 'like', "%{$termino}%");
                })
                ->latest('updated_at')
                ->limit(5)
                ->get();

            foreach ($proveedores as $proveedor) {
                $resultados[] = [
                    'tipo' => 'Proveedor',
                    'titulo' => $proveedor->nombre,
                    'detalle' => collect([$proveedor->razon_social, $proveedor->rfc])->filter()->implode(' · '),
                    'url' => route('licencia.proveedores', ['buscar' => $proveedor->nombre]),
                ];
            }
        }

        if (Gate::allows('categorias.ver')) {
            $categorias = Categoria::query()
                ->where(function ($query) use ($termino) {
                    $query->where('nombre', 'like', "%{$termino}%")
                        ->orWhere('descripcion', 'like', "%{$termino}%");
                })
                ->limit(5)
                ->get();

            foreach ($categorias as $categoria) {
                $resultados[] = [
                    'tipo' => 'Categoría',
                    'titulo' => $categoria->nombre,
                    'detalle' => $categoria->descripcion,
                    'url' => route('patrimonio.categorias', ['categoria' => $categoria]),
                ];
            }
        }

        return array_slice($resultados, 0, 20);
    }
}
