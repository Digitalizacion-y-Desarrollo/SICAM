<?php

namespace App\Services;

use App\Http\Requests\StoreBienRequest;
use App\Models\Categoria;
use App\Models\Importacion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Throwable;
use ZipArchive;

class ImportarBienes
{
    public const COLUMNAS = ['nombre', 'numero_serie', 'marca', 'modelo', 'dependencia_id_accesos', 'area_id_accesos', 'ubicacion_fisica', 'fecha_adquisicion', 'costo', 'proveedor', 'numero_factura', 'observaciones', 'responsabilidad', 'responsable_id', 'estado'];

    public function columnas(Categoria $categoria): array
    {
        return [...self::COLUMNAS, ...$categoria->campos()->where('activo', true)->pluck('clave')->map(fn ($clave) => 'campo:'.$clave)->all()];
    }

    public function preparar(UploadedFile $archivo, Categoria $categoria, string $nombre): Importacion
    {
        $tabla = $this->leer($archivo);
        $headers = array_map(fn ($v) => trim((string) $v), array_shift($tabla) ?? []);
        if (! $headers || count(array_unique($headers)) !== count($headers) || array_diff($headers, $this->columnas($categoria))) {
            throw ValidationException::withMessages(['archivo' => 'Los encabezados no corresponden a la plantilla de esta categoría o están repetidos. Descarga la plantilla actualizada.']);
        }
        if (! in_array('nombre', $headers, true)) throw ValidationException::withMessages(['archivo' => 'La plantilla debe incluir la columna nombre.']);
        $campos = $categoria->campos()->where('activo', true)->pluck('id', 'clave');
        $filas = []; $errores = []; $series = [];
        foreach ($tabla as $index => $row) {
            if (! array_filter($row, fn ($value) => $value !== null && trim((string) $value) !== '')) continue;
            $data = ['categoria_id' => $categoria->id, 'responsabilidad' => 'ninguna', 'estado' => 'DISPONIBLE', 'campos' => []];
            if (count($row) > count($headers)) $errores[$index + 2][] = 'La fila tiene más columnas que los encabezados.';
            foreach ($headers as $column => $key) {
                $value = trim((string) ($row[$column] ?? ''));
                if (str_starts_with($value, '=')) $errores[$index + 2][] = 'No se admiten fórmulas; pega únicamente valores.';
                if (str_starts_with($key, 'campo:')) $data['campos'][$campos[substr($key, 6)]] = $value === '' ? null : $value;
                elseif ($value !== '' || ! in_array($key, ['responsabilidad', 'estado'], true)) $data[$key] = $value === '' ? null : $value;
            }
            $validation = $this->validar($data);
            if ($validation->fails()) $errores[$index + 2] = [...($errores[$index + 2] ?? []), ...$validation->errors()->all()];
            $serie = mb_strtolower($data['numero_serie'] ?? '');
            if ($serie !== '' && isset($series[$serie])) $errores[$index + 2][] = 'El número de serie está repetido en el archivo.';
            if ($serie !== '') $series[$serie] = true;
            $filas[] = ['numero' => $index + 2, 'datos' => $data];
        }
        if (! $filas) throw ValidationException::withMessages(['archivo' => 'El archivo no contiene bienes para importar.']);
        $path = $archivo->store('importaciones', 'local');
        if (! $path) throw ValidationException::withMessages(['archivo' => 'No se pudo guardar el archivo.']);
        try {
            return Importacion::create(['categoria_id' => $categoria->id, 'nombre' => $nombre,
                'archivo_original' => mb_substr(basename($archivo->getClientOriginalName()), 0, 191), 'archivo_path' => $path,
                'filas' => $filas, 'errores' => $errores, 'total' => count($filas), 'estado' => $errores ? 'CON_ERRORES' : 'PREVIA']);
        } catch (Throwable $e) { Storage::disk('local')->delete($path); throw $e; }
    }

    public function confirmar(Importacion $importacion): void
    {
        $qrPaths = [];
        try {
            DB::transaction(function () use ($importacion, &$qrPaths) {
                $importacion = Importacion::whereKey($importacion->id)->lockForUpdate()->firstOrFail();
                if ($importacion->estado !== 'PREVIA') throw ValidationException::withMessages(['importacion' => 'Solo se puede confirmar una importación validada y pendiente.']);
                foreach ($importacion->filas as $fila) {
                    $validation = $this->validar($fila['datos']);
                    if ($validation->fails()) throw ValidationException::withMessages(['importacion' => 'Fila '.$fila['numero'].': '.implode(' ', $validation->errors()->all())]);
                    $bien = app(RegistrarBien::class)->registrar($validation->validated(), null, request()->ip());
                    $qrPaths[] = 'qr/'.$bien->public_id.'.svg';
                    $bien->update(['importacion_id' => $importacion->id]);
                }
                $importacion->update(['estado' => 'COMPLETADA', 'registrados' => $importacion->total]);
            });
        } catch (Throwable $e) {
            foreach ($qrPaths as $path) Storage::disk('local')->delete($path);
            throw $e;
        }
    }

    private function validar(array $data)
    {
        $request = new StoreBienRequest($data);
        $request->setContainer(app());

        return Validator::make($data, $request->rules(), $request->messages(), $request->attributes());
    }

    private function leer(UploadedFile $archivo): array
    {
        $ext = strtolower($archivo->getClientOriginalExtension());
        if ($ext === 'csv') {
            $handle = fopen($archivo->getRealPath(), 'rb');
            $first = fgets($handle);
            rewind($handle);
            $delimiter = substr_count($first ?: '', ';') > substr_count($first ?: '', ',') ? ';' : ',';
            $rows = [];
            try {
                while (($row = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
                    if (count($rows) >= 501 || count($row) > 100) throw ValidationException::withMessages(['archivo' => 'El límite es de 500 bienes y 100 columnas por archivo.']);
                    foreach ($row as $cell) if ($cell !== null && ! mb_check_encoding($cell, 'UTF-8')) throw ValidationException::withMessages(['archivo' => 'Guarda el archivo como CSV UTF-8.']);
                    $rows[] = $row;
                }
            } finally { fclose($handle); }
            if (isset($rows[0][0])) $rows[0][0] = preg_replace('/^\xEF\xBB\xBF/', '', $rows[0][0]);

            return $rows;
        }
        if ($ext !== 'xlsx') throw ValidationException::withMessages(['archivo' => 'Solo se admiten archivos XLSX o CSV.']);
        $zip = new ZipArchive();
        if ($zip->open($archivo->getRealPath()) !== true) throw ValidationException::withMessages(['archivo' => 'El archivo XLSX no es válido.']);
        $bytes = 0;
        for ($i = 0; $i < $zip->numFiles; $i++) $bytes += $zip->statIndex($i)['size'];
        $entries = $zip->numFiles; $zip->close();
        if ($bytes > 20 * 1024 * 1024 || $entries > 2000) throw ValidationException::withMessages(['archivo' => 'El libro es demasiado grande. Divide la importación.']);
        try {
            $reader = IOFactory::createReader('Xlsx');
            $info = $reader->listWorksheetInfo($archivo->getRealPath());
            if (count($info) !== 1 || $info[0]['totalRows'] > 501 || $info[0]['totalColumns'] > 100) throw ValidationException::withMessages(['archivo' => 'Usa una sola hoja, con máximo 500 bienes y 100 columnas.']);
            $reader->setReadDataOnly(true)->setReadEmptyCells(false);
            $book = $reader->load($archivo->getRealPath());
            $sheet = $book->getActiveSheet();
            foreach ($sheet->getCellCollection()->getCoordinates() as $coordinate) {
                if ($sheet->getCell($coordinate)->getDataType() === DataType::TYPE_FORMULA) throw ValidationException::withMessages(['archivo' => 'No se admiten fórmulas; pega únicamente valores.']);
            }
            $rows = $sheet->toArray(null, false, false, false);
            $book->disconnectWorksheets();

            return $rows;
        } catch (ValidationException $e) { throw $e; }
        catch (Throwable $e) { throw ValidationException::withMessages(['archivo' => 'No se pudo leer el libro. Usa una plantilla XLSX válida.']); }
    }
}
