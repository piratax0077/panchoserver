<?php

namespace App\Exports;


use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use PHPExcel;
use PHPExcel_IOFactory;

class UsersExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $usuarios = collect([
            ['Nombre', 'Email'],
            ['Juan', 'juan@example.com'],
            ['Pedro', 'pedro@example.com'],
            ['María', 'maria@example.com']
        ]);
        
        $excel = new PHPExcel();
        $sheet = $excel->getActiveSheet();
        $sheet->fromArray($usuarios, null, 'A1');
        
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $writer->save(storage_path('app/misClases/usuarios.xls'));

        return $usuarios;
    }
}
