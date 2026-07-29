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

        // Ambil transaksi berdasarkan bulan dan tahun yang dipilih
        $transactions = Transaction::with('category')
            ->where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Hitung ringkasan statistik dengan mengecualikan mutasi tabungan
        $totalMasuk = $transactions->filter(function ($item) {
            $isTabunganMasuk = str_starts_with($item->deskripsi, 'Penarikan Tabungan:') || 
                               str_starts_with($item->deskripsi, 'Refund Tabungan') ||
                               optional($item->category)->nama == 'Penarikan Tabungan' ||
                               optional($item->category)->nama == 'Pencairan Tabungan Dihapus';
            
            return $item->tipe === 'masuk' && !$isTabunganMasuk;
        })->sum('nominal');

        $totalKeluar = $transactions->filter(function ($item) {
            $isTabunganKeluar = str_starts_with($item->deskripsi, 'Setor Tabungan:') || 
                                optional($item->category)->nama == 'Tabungan';
            
            return $item->tipe === 'keluar' && !$isTabunganKeluar;
        })->sum('nominal');

        $saldoBersih = $totalMasuk - $totalKeluar;

        return view('reports.index', compact('transactions', 'totalMasuk', 'totalKeluar', 'saldoBersih', 'bulan', 'tahun'));
    }
}