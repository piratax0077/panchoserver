<?php

namespace App\Imports;

use App\oem;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;

class OemsImport implements ToModel,WithHeadingRow
{
    private $numRows = 0;
    
    public function model(array $row)
    {
            ++$this->numRows;
            return new oem([
                'codigo_oem' => $row['codigo_oem'],
                'id_repuestos' => $row['id_repuestos'],
                'usuarios_id' => Auth::user()->id,
                'activo' => 1
            ]);
    }

    public function rules(): array
    {
        return [
            'codigo_oem' => 'required|max:45',
            'id_repuestos' => 'required|max:45',
            'usuarios_id' => 'required|max:45',
            'activo' => 'required'
        ];
    }

    public function getRowCount(): int
    {
        return $this->numRows;
    }
}
