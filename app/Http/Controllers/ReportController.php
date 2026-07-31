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
}