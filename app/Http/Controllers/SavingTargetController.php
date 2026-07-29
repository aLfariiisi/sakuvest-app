<?php

namespace App\Http\Controllers;

use App\Models\SavingTarget;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavingTargetController extends Controller
{
    public function index()
    {
        $savingTargets = SavingTarget::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($target) {
                $target->progress = $target->target_nominal > 0 
                    ? min(round(($target->terkumpul / $target->target_nominal) * 100, 2), 100) 
                    : 0;
                return $target;
            });

        return view('saving-targets.index', compact('savingTargets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_target' => 'required|string|max:255',
            'target_nominal' => 'required|numeric|min:1',
            'terkumpul' => 'nullable|numeric|min:0',
        ]);

        $userId = Auth::id();
        $terkumpulAwal = $request->terkumpul ?? 0;

        // 1. Buat target tabungan baru
        $savingTarget = SavingTarget::create([
            'user_id' => $userId,
            'nama_target' => $request->nama_target,
            'target_nominal' => $request->target_nominal,
            'terkumpul' => $terkumpulAwal,
        ]);

        // 2. Jika saat membuat target langsung diisi dana awal (terkumpul > 0), catat sebagai pengeluaran setor tabungan
        if ($terkumpulAwal > 0) {
            $category = Category::firstOrCreate(
                ['user_id' => $userId, 'nama' => 'Tabungan'],
                ['tipe' => 'keluar']
            );

            Transaction::create([
                'user_id' => $userId,
                'category_id' => $category->id,
                'deskripsi' => 'Setor Tabungan: ' . $savingTarget->nama_target,
                'tipe' => 'keluar',
                'nominal' => $terkumpulAwal,
                'tanggal' => date('Y-m-d'),
            ]);
        }

        return redirect()->route('saving-targets.index')->with('success', 'Target tabungan berhasil ditambahkan dan dicatat ke riwayat!');
    }

    public function update(Request $request, SavingTarget $savingTarget)
    {
        if ($savingTarget->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'nama_target' => 'required|string|max:255',
            'target_nominal' => 'required|numeric|min:1',
            'terkumpul' => 'required|numeric|min:0',
        ]);

        $savingTarget->update([
            'nama_target' => $request->nama_target,
            'target_nominal' => $request->target_nominal,
            'terkumpul' => $request->terkumpul,
        ]);

        return redirect()->route('saving-targets.index')->with('success', 'Target tabungan berhasil diperbarui!');
    }

    public function destroy(SavingTarget $savingTarget)
    {
        if ($savingTarget->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $userId = Auth::id();

        if ($savingTarget->terkumpul > 0) {
            $categoryMasuk = Category::firstOrCreate(
                ['user_id' => $userId, 'nama' => 'Pencairan Tabungan Dihapus'],
                ['tipe' => 'masuk']
            );

            Transaction::create([
                'user_id' => $userId,
                'category_id' => $categoryMasuk->id,
                'deskripsi' => 'Refund Tabungan Dihapus: ' . $savingTarget->nama_target,
                'tipe' => 'masuk',
                'nominal' => $savingTarget->terkumpul,
                'tanggal' => date('Y-m-d'),
            ]);
        }

        $savingTarget->delete();

        return redirect()->route('saving-targets.index')->with('success', 'Target tabungan dihapus dan sisa dana terkumpul dikembalikan ke saldo utama!');
    }

    public function deposit(Request $request, SavingTarget $savingTarget)
    {
        if ($savingTarget->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'tambah_nominal' => 'required|numeric|min:1',
        ]);

        $userId = Auth::id();
        $nominal = $request->tambah_nominal;

        $savingTarget->terkumpul += $nominal;
        $savingTarget->save();

        $category = Category::firstOrCreate(
            ['user_id' => $userId, 'nama' => 'Tabungan'],
            ['tipe' => 'keluar']
        );

        Transaction::create([
            'user_id' => $userId,
            'category_id' => $category->id,
            'deskripsi' => 'Setor Tabungan: ' . $savingTarget->nama_target,
            'tipe' => 'keluar',
            'nominal' => $nominal,
            'tanggal' => date('Y-m-d'),
        ]);

        return redirect()->route('saving-targets.index')->with('success', 'Berhasil menyetor ke tabungan!');
    }

    public function withdraw(Request $request, SavingTarget $savingTarget)
    {
        if ($savingTarget->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'tarik_nominal' => 'required|numeric|min:1',
        ]);

        $userId = Auth::id();
        $nominal = $request->tarik_nominal;

        if ($nominal > $savingTarget->terkumpul) {
            return back()->withErrors(['tarik_nominal' => 'Nominal penarikan melebihi saldo terkumpul saat ini!']);
        }

        $savingTarget->terkumpul -= $nominal;
        $savingTarget->save();

        $category = Category::firstOrCreate(
            ['user_id' => $userId, 'nama' => 'Penarikan Tabungan'],
            ['tipe' => 'masuk']
        );

        Transaction::create([
            'user_id' => $userId,
            'category_id' => $category->id,
            'deskripsi' => 'Penarikan Tabungan: ' . $savingTarget->nama_target,
            'tipe' => 'masuk',
            'nominal' => $nominal,
            'tanggal' => date('Y-m-d'),
        ]);

        return redirect()->route('saving-targets.index')->with('success', 'Berhasil melakukan pengambilan saldo! Dana kembali masuk ke saldo utama.');
    }
}