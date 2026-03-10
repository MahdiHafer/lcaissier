<?php

namespace App\Http\Controllers;

use App\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = AppSetting::allAsMap();
        $exports = $this->availableExports();
        return view('settings.index', compact('settings', 'exports'));
    }

    public function updateCompany(Request $request)
    {
        if (!auth()->user()->hasPermission('settings.entreprise')) {
            abort(403, 'Acces refuse');
        }

        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'company_address' => 'nullable|string|max:255',
            'company_phone' => 'nullable|string|max:80',
            'company_email' => 'nullable|email|max:255',
            'company_ice' => 'nullable|string|max:100',
            'company_rc' => 'nullable|string|max:100',
            'company_if' => 'nullable|string|max:100',
            'company_cnss' => 'nullable|string|max:100',
            'label_printer_name' => 'nullable|string|max:255',
            'company_logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        if ($request->hasFile('company_logo')) {
            $file = $request->file('company_logo');
            $dir = public_path('uploads/company');
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $name = 'company_logo_' . now()->format('Ymd_His') . '_' . bin2hex(random_bytes(3)) . '.' . $file->getClientOriginalExtension();
            $file->move($dir, $name);
            $newPath = 'uploads/company/' . $name;

            $oldPath = AppSetting::get('company_logo');
            if (!empty($oldPath) && str_starts_with($oldPath, 'uploads/company/')) {
                $oldFull = public_path($oldPath);
                if (is_file($oldFull)) {
                    @unlink($oldFull);
                }
            }

            $data['company_logo'] = $newPath;
        }

        AppSetting::setMany($data);

        return back()->with('success', 'Informations entreprise mises a jour.');
    }

    public function exportExcel(Request $request)
    {
        if (!auth()->user()->hasPermission('settings.exports')) {
            abort(403, 'Acces refuse');
        }

        $request->validate([
            'dataset' => 'required|string',
        ]);

        $exports = $this->availableExports();
        $dataset = $request->input('dataset');

        if (!array_key_exists($dataset, $exports)) {
            return back()->with('error', 'Dataset export invalide.');
        }

        $table = $exports[$dataset]['table'];
        $columns = Schema::getColumnListing($table);
        $filename = $exports[$dataset]['filename_prefix'] . '-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($table, $columns) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, $columns, ';');

            DB::table($table)->orderBy('id')->chunk(500, function ($rows) use ($out, $columns) {
                foreach ($rows as $row) {
                    $line = [];
                    foreach ($columns as $col) {
                        $line[] = $row->{$col};
                    }
                    fputcsv($out, $line, ';');
                }
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function availableExports(): array
    {
        return [
            'products' => ['label' => 'Produits', 'table' => 'products', 'filename_prefix' => 'export-produits'],
            'clients' => ['label' => 'Clients', 'table' => 'clients', 'filename_prefix' => 'export-clients'],
            'fournisseurs' => ['label' => 'Fournisseurs', 'table' => 'fournisseurs', 'filename_prefix' => 'export-fournisseurs'],
            'ventes' => ['label' => 'Ventes', 'table' => 'ventes', 'filename_prefix' => 'export-ventes'],
            'ventes_details' => ['label' => 'Details ventes', 'table' => 'ventes_details', 'filename_prefix' => 'export-details-ventes'],
            'bons_livraison' => ['label' => 'Bons de livraison', 'table' => 'bons_livraison', 'filename_prefix' => 'export-bl'],
            'devis' => ['label' => 'Devis', 'table' => 'devis', 'filename_prefix' => 'export-devis'],
            'factures' => ['label' => 'Factures', 'table' => 'factures', 'filename_prefix' => 'export-factures'],
            'avoirs' => ['label' => 'Avoirs', 'table' => 'avoirs', 'filename_prefix' => 'export-avoirs'],
            'stock_movements' => ['label' => 'Mouvements stock', 'table' => 'stock_movements', 'filename_prefix' => 'export-mouvements-stock'],
        ];
    }
}
