<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Importacion;
use App\Services\ImportarBienes;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ImportacionController extends Controller
{
    public function index()
    {
        $importaciones = Importacion::with('categoria')->latest('id')->paginate(15);
        $categorias = Categoria::where('activo', true)->orderBy('nombre')->get();

        return view('patrimonio.importaciones', compact('importaciones', 'categorias'));
    }

    public function store(Request $request, ImportarBienes $service)
    {
        $data = $request->validate(['nombre' => ['required', 'string', 'max:180'], 'categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')->where('activo', true)->whereNull('deleted_at')], 'archivo' => ['required', 'file', 'mimes:csv,txt,xlsx', 'max:5120']]);
        $importacion = $service->preparar($request->file('archivo'), Categoria::findOrFail($data['categoria_id']), $data['nombre']);

        return redirect()->route('patrimonio.importaciones.show', $importacion)->with($importacion->errores ? 'error' : 'success', $importacion->errores ? 'El archivo tiene errores. Revisa las filas indicadas antes de confirmar.' : 'Archivo validado. Revisa la vista previa y confirma el registro.');
    }

    public function show(Importacion $importacion)
    {
        $importacion->load('categoria');
        $bienes = $importacion->bienes()->latest('id')->paginate(15);

        return view('patrimonio.detalle-importacion', compact('importacion', 'bienes'));
    }

    public function update(Request $request, Importacion $importacion)
    {
        $importacion->update($request->validate(['nombre' => ['required', 'string', 'max:180']]));

        return back()->with('success', 'Importación actualizada.');
    }

    public function confirmar(Importacion $importacion, ImportarBienes $service)
    {
        $service->confirmar($importacion);

        return back()->with('success', 'Importación completada. Los bienes y sus QR ya están registrados.');
    }

    public function destroy(Importacion $importacion)
    {
        $importacion->delete();

        return redirect()->route('patrimonio.importaciones.index')->with('success', 'Importación archivada. Los bienes registrados se conservan.');
    }

    public function plantilla(Request $request, ImportarBienes $service)
    {
        $data = $request->validate(['categoria_id' => ['required', 'integer', Rule::exists('categorias', 'id')->where('activo', true)->whereNull('deleted_at')], 'formato' => ['required', 'in:csv,xlsx']]);
        $categoria = Categoria::findOrFail($data['categoria_id']);
        $columns = $service->columnas($categoria);
        if ($data['formato'] === 'csv') {
            return response()->streamDownload(function () use ($columns) {
                $out = fopen('php://output', 'wb'); fwrite($out, "\xEF\xBB\xBF"); fputcsv($out, $columns, ',', '"', ''); fclose($out);
            }, 'plantilla-'.$categoria->slug.'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return response()->streamDownload(function () use ($columns) {
            $book = new Spreadsheet(); $sheet = $book->getActiveSheet(); $sheet->setTitle('Bienes');
            $sheet->fromArray($columns, null, 'A1');
            $last = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columns));
            $sheet->getStyle('A1:'.$last.'1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle('A1:'.$last.'1')->getFill()->setFillType('solid')->getStartColor()->setARGB('FF601633');
            $sheet->getStyle('A1:'.$last.'1')->getAlignment()->setWrapText(true);
            $sheet->getRowDimension(1)->setRowHeight(45); $sheet->getDefaultColumnDimension()->setWidth(26);
            $sheet->getStyle('A2:'.$last.'501')->getNumberFormat()->setFormatCode('@');
            $sheet->freezePane('A2'); (new Xlsx($book))->save('php://output'); $book->disconnectWorksheets();
        }, 'plantilla-'.$categoria->slug.'.xlsx', ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }
}
