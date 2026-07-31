<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));
        $sort = $request->input('sort', 'tanggal_asc');
        $search = $request->input('search');

        // Query Dasar
        $query = Transaction::with('category')
            ->where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun);

        // Pencarian Teks
        if ($search) {
            $query->where('deskripsi', 'like', '%' . $search . '%');
        }

        // Eksekusi Query ke Collection
        $transactions = $query->get();

        // Logika Sorting (Pengurutan)
        if ($sort === 'tanggal_asc') {
            $transactions = $transactions->sortBy(['tanggal', 'created_at']);
        } elseif ($sort === 'tanggal_desc') {
            $transactions = $transactions->sortByDesc('created_at')->sortByDesc('tanggal');
        } elseif ($sort === 'kategori') {
            $transactions = $transactions->sortBy(function($trx) {
                return $trx->category ? $trx->category->nama : 'ZZZ';
            });
        } elseif ($sort === 'terbesar_masuk') {
            $transactions = $transactions->sortByDesc(function($trx) {
                return $trx->tipe === 'masuk' ? $trx->nominal : 0;
            });
        } elseif ($sort === 'terbesar_keluar') {
            $transactions = $transactions->sortByDesc(function($trx) {
                return $trx->tipe === 'keluar' ? $trx->nominal : 0;
            });
        }

        // CEK JIKA USER KLIK TOMBOL "DOWNLOAD EXCEL"
        if ($request->has('export') && $request->export === 'excel') {
            return $this->exportToExcel($transactions, $bulan, $tahun);
        }

        // Kecualikan transaksi mutasi tabungan dari rekap TOTAL laporan agar akuntansi akurat
        $operationalTransactions = $transactions->reject(function ($trx) {
            return str_starts_with($trx->deskripsi, 'Setor Tabungan:') || 
                   str_starts_with($trx->deskripsi, 'Penarikan Tabungan:') || 
                   str_starts_with($trx->deskripsi, 'Refund Tabungan') ||
                   optional($trx->category)->nama == 'Tabungan' ||
                   optional($trx->category)->nama == 'Penarikan Tabungan' ||
                   optional($trx->category)->nama == 'Pencairan Tabungan Dihapus';
        });

        $totalMasuk = $operationalTransactions->where('tipe', 'masuk')->sum('nominal');
        $totalKeluar = $operationalTransactions->where('tipe', 'keluar')->sum('nominal');
        $saldoBersih = $totalMasuk - $totalKeluar;

        return view('reports.index', compact('transactions', 'totalMasuk', 'totalKeluar', 'saldoBersih', 'bulan', 'tahun', 'sort'));
    }

    /**
     * Fungsi untuk menghasilkan file CSV yang bisa dibuka langsung di Excel
     */
    private function exportToExcel($transactions, $bulan, $tahun)
    {
        $fileName = 'Laporan_Keuangan_' . $tahun . '_' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // Tambahkan BOM (Byte Order Mark) agar Excel otomatis mendeteksi format UTF-8
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            // Header Kolom di Excel
            fputcsv($file, ['Tanggal', 'Deskripsi', 'Kategori', 'Tipe', 'Uang Masuk (Rp)', 'Uang Keluar (Rp)']);

            // Isi Data
            foreach ($transactions as $trx) {
                $masuk = $trx->tipe == 'masuk' ? $trx->nominal : 0;
                $keluar = $trx->tipe == 'keluar' ? $trx->nominal : 0;
                
                fputcsv($file, [
                    date('d M Y', strtotime($trx->tanggal)),
                    $trx->deskripsi,
                    $trx->category ? $trx->category->nama : '-',
                    ucfirst($trx->tipe),
                    $masuk,
                    $keluar
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}