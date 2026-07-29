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

        // Hitung total masuk dan keluar secara menyeluruh (termasuk mutasi tabungan)
        $totalMasuk = $transactions->where('tipe', 'masuk')->sum('nominal');
        $totalKeluar = $transactions->where('tipe', 'keluar')->sum('nominal');
        $saldoBersih = $totalMasuk - $totalKeluar;

        return view('reports.index', compact('transactions', 'totalMasuk', 'totalKeluar', 'saldoBersih', 'bulan', 'tahun'));
    }
}