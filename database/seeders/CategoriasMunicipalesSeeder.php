<?php

namespace Database\Seeders;

use App\Models\Categoria;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriasMunicipalesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach ($this->catalogo() as $definicion) {
                // Reutilizar categorías creadas desde la interfaz, cuyo slug lleva un sufijo.
                $categoria = Categoria::withTrashed()->whereNull('categoria_padre_id')
                    ->where(function ($query) use ($definicion) {
                        $query->where('slug', $definicion['slug'])->orWhere('nombre', $definicion['nombre']);
                    })->first();

                if (! $categoria && $definicion['slug'] === 'mobiliario') {
                    $categoria = Categoria::withTrashed()->where('slug', 'inmobiliaria')->first();
                    if ($categoria) {
                        $categoria->update([
                            'slug' => 'mobiliario', 'nombre' => $definicion['nombre'],
                            'descripcion' => $definicion['descripcion'],
                        ]);
                        // Conservar posibles valores históricos, retirando estos campos del registro.
                        $categoria->campos()->whereIn('clave', [
                            'tipo_inmueble', 'uso_actual', 'calle', 'numero_exterior',
                            'numero_interior', 'colonia', 'codigo_postal', 'localidad_municipio',
                            'clave_catastral', 'superficie_terreno_m2', 'superficie_construccion_m2',
                            'niveles', 'referencia_documental', 'expediente_patrimonial',
                            'situacion_documental', 'servicios_disponibles', 'fecha_inspeccion',
                        ])->update(['activo' => false, 'requerido' => false]);
                        $categoria->campos()->where('clave', 'condicion_fisica')->update(['orden' => 2]);
                    }
                }

                $categoria ??= Categoria::withTrashed()->firstOrCreate(
                    ['slug' => $definicion['slug']],
                    ['nombre' => $definicion['nombre'], 'descripcion' => $definicion['descripcion'], 'activo' => true],
                );

                // Una ejecución posterior no debe reactivar categorías eliminadas o inactivas.
                if ($categoria->trashed() || ! $categoria->activo) {
                    continue;
                }

                foreach ($definicion['campos'] as $orden => [$clave, $nombre, $tipo, $requerido, $opciones]) {
                    // Conservar las opciones, obligatoriedad y ajustes que haga el municipio.
                    $categoria->campos()->firstOrCreate(
                        ['clave' => $clave],
                        compact('nombre', 'tipo', 'requerido', 'opciones') + ['orden' => $orden + 1, 'activo' => true],
                    );
                }
            }
        });
    }

    private function catalogo(): array
    {
        // Los datos comunes (marca, modelo, serie, ubicación, costo y responsable)
        // ya pertenecen al formulario general y no se duplican como características.
        return [
            [
                'slug' => 'computo',
                'nombre' => 'Cómputo',
                'descripcion' => 'Equipo de cómputo y periféricos utilizados por las dependencias y áreas del Ayuntamiento de Nezahualcóyotl.',
                'campos' => [
                    ['tipo_equipo', 'Tipo de equipo', 'SELECCION', true, ['Computadora de escritorio', 'Laptop', 'Servidor', 'Monitor', 'Impresora', 'Escáner', 'Multifuncional', 'Proyector', 'UPS / No break', 'Equipo de red', 'Otro']],
                    ['condicion_fisica', 'Condición física', 'SELECCION', true, ['Bueno', 'Regular', 'Malo', 'Por dictaminar']],
                    ['procesador', 'Procesador', 'TEXTO', false, null],
                    ['memoria_ram_gb', 'Memoria RAM (GB)', 'NUMERO', false, null],
                    ['capacidad_almacenamiento_gb', 'Capacidad de almacenamiento (GB)', 'NUMERO', false, null],
                    ['tipo_almacenamiento', 'Tipo de almacenamiento', 'SELECCION', false, ['SSD', 'HDD', 'Mixto', 'Sin almacenamiento', 'No aplica']],
                    ['sistema_operativo', 'Sistema operativo y versión', 'TEXTO', false, null],
                    ['nombre_equipo', 'Nombre del equipo en la red', 'TEXTO', false, null],
                    ['direccion_mac', 'Dirección MAC', 'TEXTO', false, null],
                    ['direccion_ip', 'Dirección IP asignada', 'TEXTO', false, null],
                    ['licenciamiento', 'Situación del licenciamiento', 'SELECCION', false, ['OEM', 'Licencia institucional', 'Licencia individual', 'Software libre', 'Por verificar', 'No aplica']],
                    ['accesorios_incluidos', 'Accesorios incluidos', 'TEXTO', false, null],
                    ['fin_garantia', 'Fecha de término de garantía', 'FECHA', false, null],
                    ['ultimo_mantenimiento', 'Fecha del último mantenimiento', 'FECHA', false, null],
                ],
            ],
            [
                'slug' => 'mobiliario',
                'nombre' => 'Mobiliario',
                'descripcion' => 'Mobiliario de oficina y de atención ciudadana del Ayuntamiento de Nezahualcóyotl: escritorios, sillas, mesas, archiveros y muebles de almacenamiento.',
                'campos' => [
                    ['tipo_mueble', 'Tipo de mueble', 'SELECCION', true, ['Escritorio', 'Silla operativa', 'Silla de visita', 'Silla plegable', 'Banca', 'Mesa de trabajo', 'Mesa de juntas', 'Archivero', 'Librero', 'Estante', 'Gabinete', 'Mostrador', 'Credenza', 'Otro']],
                    ['condicion_fisica', 'Condición física', 'SELECCION', true, ['Bueno', 'Regular', 'Malo', 'Por dictaminar']],
                    ['material_principal', 'Material principal', 'SELECCION', true, ['Madera', 'Metal', 'Melamina / MDF', 'Plástico', 'Vidrio', 'Mixto', 'Otro']],
                    ['color', 'Color o acabado', 'TEXTO', false, null],
                    ['ancho_cm', 'Ancho (cm)', 'NUMERO', false, null],
                    ['alto_cm', 'Alto (cm)', 'NUMERO', false, null],
                    ['fondo_cm', 'Fondo (cm)', 'NUMERO', false, null],
                    ['numero_cajones', 'Número de cajones', 'NUMERO', false, null],
                    ['numero_puertas', 'Número de puertas', 'NUMERO', false, null],
                    ['numero_entrepanos', 'Número de entrepaños', 'NUMERO', false, null],
                    ['tapizado', 'Tipo de tapizado', 'SELECCION', false, ['Tela', 'Vinil', 'Piel sintética', 'Piel', 'Malla', 'Sin tapizado', 'Otro']],
                    ['ruedas', 'Cuenta con ruedas', 'SELECCION', false, ['Sí', 'No']],
                    ['accesorios_incluidos', 'Accesorios incluidos (llaves, soportes u otros)', 'TEXTO', false, null],
                    ['ultimo_mantenimiento', 'Fecha del último mantenimiento', 'FECHA', false, null],
                ],
            ],
            [
                'slug' => 'telefonia',
                'nombre' => 'Telefonía',
                'descripcion' => 'Equipos telefónicos y de comunicación asignados a oficinas, atención ciudadana y servicios operativos del Ayuntamiento de Nezahualcóyotl.',
                'campos' => [
                    ['tipo_equipo', 'Tipo de equipo', 'SELECCION', true, ['Teléfono fijo', 'Teléfono IP', 'Teléfono celular', 'Conmutador', 'Radiocomunicador', 'Equipo de videoconferencia', 'Otro']],
                    ['condicion_fisica', 'Condición física', 'SELECCION', true, ['Bueno', 'Regular', 'Malo', 'Por dictaminar']],
                    ['numero_telefonico', 'Número telefónico institucional', 'TEXTO', false, null],
                    ['extension', 'Extensión', 'TEXTO', false, null],
                    ['imei_1', 'IMEI 1', 'TEXTO', false, null],
                    ['imei_2', 'IMEI 2', 'TEXTO', false, null],
                    ['iccid', 'ICCID de la tarjeta SIM', 'TEXTO', false, null],
                    ['operador', 'Operador o prestador del servicio', 'TEXTO', false, null],
                    ['modalidad_servicio', 'Modalidad del servicio', 'SELECCION', false, ['Plan institucional', 'Prepago', 'Extensión interna', 'Sin servicio', 'No aplica']],
                    ['referencia_contrato', 'Referencia del contrato de servicio', 'TEXTO', false, null],
                    ['identificador_radio', 'Identificador o indicativo del radio', 'TEXTO', false, null],
                    ['accesorios_incluidos', 'Accesorios incluidos', 'TEXTO', false, null],
                    ['fin_garantia', 'Fecha de término de garantía', 'FECHA', false, null],
                ],
            ],
        ];
    }
}
