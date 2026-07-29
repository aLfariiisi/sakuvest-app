<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\SavingTarget;
use App\Models\Budget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $bulanIni = date('m');
        $tahunIni = date('Y');

        $allTransactions = Transaction::with('category')->where('user_id', $userId)->get();

        // 1. Hitung total keseluruhan (Tanpa mutasi tabungan)
        $totalPemasukan = $allTransactions->filter(function ($item) {
            $isTabunganMasuk = str_starts_with($item->deskripsi, 'Penarikan Tabungan:') || 
                               str_starts_with($item->deskripsi, 'Refund Tabungan') ||
                               optional($item->category)->nama == 'Penarikan Tabungan' ||
                               optional($item->category)->nama == 'Pencairan Tabungan Dihapus';
            
            return $item->tipe === 'masuk' && !$isTabunganMasuk;
        })->sum('nominal');

        $totalPengeluaran = $allTransactions->filter(function ($item) {
            $isTabunganKeluar = str_starts_with($item->deskripsi, 'Setor Tabungan:') || 
                                optional($item->category)->nama == 'Tabungan';
            
            return $item->tipe === 'keluar' && !$isTabunganKeluar;
        })->sum('nominal');

        $saldo = $totalPemasukan - $totalPengeluaran;

        // 2. Ambil 5 Transaksi Terbaru
        $transaksiTerbaru = Transaction::with('category')
                            ->where('user_id', $userId)
                            ->orderBy('tanggal', 'desc')
                            ->orderBy('id', 'desc')
                            ->take(5)
                            ->get();

        // 3. Data Grafik Per Bulan (ApexCharts) - Mapping 12 Bulan Penuh (Jan - Des) [Kompatibel PostgreSQL]
        $rawGrafik = Transaction::select(
                DB::raw('EXTRACT(MONTH FROM tanggal) as bulan'),
                DB::raw("SUM(CASE WHEN tipe = 'masuk' THEN nominal ELSE 0 END) as total_masuk"),
                DB::raw("SUM(CASE WHEN tipe = 'keluar' THEN nominal ELSE 0 END) as total_keluar")
            )
            ->where('user_id', $userId)
            ->whereYear('tanggal', $tahunIni)
            ->groupBy(DB::raw('EXTRACT(MONTH FROM tanggal)'))
            ->orderBy('bulan')
            ->get()
            ->keyBy(function ($item) {
                return (int) $item->bulan;
            });

        $grafikMasuk = [];
        $grafikKeluar = [];
        for ($i = 1; $i <= 12; $i++) {
            $grafikMasuk[] = isset($rawGrafik[$i]) ? (float) $rawGrafik[$i]->total_masuk : 0;
            $grafikKeluar[] = isset($rawGrafik[$i]) ? (float) $rawGrafik[$i]->total_keluar : 0;
        }

        // 4. Target Tabungan
        $savingTargets = SavingTarget::where('user_id', $userId)->take(3)->get();
        foreach ($savingTargets as $target) {
            $target->progress = $target->target_nominal > 0 
                ? min(round(($target->terkumpul / $target->target_nominal) * 100, 2), 100) 
                : 0;
        }

        // 5. Anggaran Bulanan
        $budgets = Budget::with('category')
                    ->where('user_id', $userId)
                    ->where('bulan', $bulanIni)
                    ->where('tahun', $tahunIni)
                    ->take(3)
                    ->get();

        foreach ($budgets as $budget) {
            $sudahDipakai = Transaction::where('user_id', $userId)
                            ->where('category_id', $budget->category_id)
                            ->where('tipe', 'keluar')
                            ->whereMonth('tanggal', $bulanIni)
                            ->whereYear('tanggal', $tahunIni)
                            ->sum('nominal');

            $budget->sudah_dipakai = $sudahDipakai;
            $budget->sisa = $budget->limit_nominal - $sudahDipakai;
            $budget->progress = $budget->limit_nominal > 0 
                ? min(round(($sudahDipakai / $budget->limit_nominal) * 100, 2), 100) 
                : 0;
        }

        // Total Tabungan untuk Card ke-4
        $totalTabungan = $savingTargets->sum('terkumpul');

        return view('dashboard', compact(
            'totalPemasukan',
            'totalPengeluaran',
            'saldo',
            'totalTabungan',
            'transaksiTerbaru',
            'grafikMasuk',
            'grafikKeluar',
            'savingTargets',
            'budgets'
        ));
    }
}