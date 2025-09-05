<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Importable;

class UsersImport implements
    WithMultipleSheets
{
    use Importable;

    public function sheets(): array
    {
        return [
            0 => new UsersFirstSheetImport(),
        ];
    }
}
