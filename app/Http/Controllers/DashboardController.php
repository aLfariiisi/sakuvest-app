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

        // Helper untuk mendeteksi transaksi mutasi tabungan (saldo yang muter)
        $isSavingsTrx = function($trx) {
            return str_starts_with($trx->deskripsi, 'Setor Tabungan:') || 
                   str_starts_with($trx->deskripsi, 'Penarikan Tabungan:') || 
                   str_starts_with($trx->deskripsi, 'Refund Tabungan') ||
                   optional($trx->category)->nama == 'Tabungan' ||
                   optional($trx->category)->nama == 'Penarikan Tabungan' ||
                   optional($trx->category)->nama == 'Pencairan Tabungan Dihapus';
        };

        // 1. Saldo Kas Dompet Utama (Tetap memperhitungkan kas keluar/masuk nyata karena uang benar-benar berpindah)
        $totalSemuaMasuk = $allTransactions->where('tipe', 'masuk')->sum('nominal');
        $totalSemuaKeluar = $allTransactions->where('tipe', 'keluar')->sum('nominal');
        $saldo = $totalSemuaMasuk - $totalSemuaKeluar;

        // 2. Total Pemasukan & Pengeluaran Operasional (Mutasi tabungan dikecualikan sesuai akuntansi formal)
        $operationalTransactions = $allTransactions->reject($isSavingsTrx);
        $totalPemasukan = $operationalTransactions->where('tipe', 'masuk')->sum('nominal');
        $totalPengeluaran = $operationalTransactions->where('tipe', 'keluar')->sum('nominal');

        // 3. Ambil 5 Transaksi (Diurutkan dari lama ke baru / asc)
        $transaksiTerbaru = Transaction::with('category')
                            ->where('user_id', $userId)
                            ->orderBy('tanggal', 'asc')
                            ->orderBy('created_at', 'asc')
                            ->take(5)
                            ->get();

        // 4. Data Grafik Per Bulan (ApexCharts) [Kompatibel PostgreSQL - Bersih dari mutasi tabungan]
        $rawGrafik = Transaction::select(
                DB::raw('EXTRACT(MONTH FROM tanggal) as bulan'),
                DB::raw("SUM(CASE WHEN tipe = 'masuk' AND deskripsi NOT LIKE 'Penarikan Tabungan:%' AND deskripsi NOT LIKE 'Refund Tabungan%' THEN nominal ELSE 0 END) as total_masuk"),
                DB::raw("SUM(CASE WHEN tipe = 'keluar' AND deskripsi NOT LIKE 'Setor Tabungan:%' THEN nominal ELSE 0 END) as total_keluar")
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

        // 5. Target Tabungan
        $savingTargets = SavingTarget::where('user_id', $userId)->take(3)->get();
        foreach ($savingTargets as $target) {
            $target->progress = $target->target_nominal > 0 
                ? min(round(($target->terkumpul / $target->target_nominal) * 100, 2), 100) 
                : 0;
        }

        // 6. Anggaran Bulanan
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