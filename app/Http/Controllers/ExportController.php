<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FormExport;

class ExportController extends Controller
{
    // Display stages on the page
    public function showForm()
    {
        $jsonPath = resource_path('data/REPRESENT_SELLER.json');
        $jsonData = json_decode(file_get_contents($jsonPath), true);
        $stages = isset($jsonData['stages']) ? $jsonData['stages'] : [];

        return view('export', compact('stages'));
    }

    // Export Excel
    public function exportForm()
    {
        $jsonPath = resource_path('data/REPRESENT_SELLER.json');
        $jsonData = json_decode(file_get_contents($jsonPath), true);
        $stages = isset($jsonData['stages']) ? $jsonData['stages'] : [];

        return Excel::download(new FormExport($stages), 'Represent_Seller.xlsx');
    }
}
