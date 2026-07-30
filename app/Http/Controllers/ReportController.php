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

        $transactions = Transaction::with('category')
            ->where('user_id', $userId)
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Kecualikan transaksi mutasi tabungan dari rekap total pemasukan & pengeluaran laporan
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

        return view('reports.index', compact('transactions', 'totalMasuk', 'totalKeluar', 'saldoBersih', 'bulan', 'tahun'));
    }
}