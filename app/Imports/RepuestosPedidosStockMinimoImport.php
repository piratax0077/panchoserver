<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Validators\Failure;

class RepuestosPedidosStockMinimoImport implements 
    ToCollection, 
    WithHeadingRow, 
    WithValidation, 
    SkipsOnFailure
{
    use SkipsFailures;

    public $rows = [];

    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {

            $this->rows[] = [
                'proveedor' => $row['proveedor'] ?? null,
                'codigo_interno' => $row['codigo_interno'] ?? null,
                'estado' => $row['respuesta_proveedor'] ?? null, // 👈 AQUÍ
            ];
        }
    }

    public function rules(): array
{
    return [
        '*.proveedor' => 'required|max:45',
        '*.codigo_interno' => 'required|max:45',
        '*.respuesta_proveedor' => 'nullable|max:45',
    ];
}

public function customValidationMessages()
{
    return [
        '*.proveedor.required' => 'El proveedor es obligatorio.',
        '*.codigo_interno.required' => 'El código interno es obligatorio.',
        '*.respuesta_proveedor.required' => 'La respuesta del proveedor es obligatoria.',
    ];
}

    public function getRows()
    {
        return $this->rows;
        
    }
}