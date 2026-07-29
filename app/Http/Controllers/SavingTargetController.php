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
        $userId = Auth::id();

        $savingTargets = SavingTarget::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($target) {
                $target->progress = $target->target_nominal > 0 
                    ? min(round(($target->terkumpul / $target->target_nominal) * 100, 2), 100) 
                    : 0;
                return $target;
            });

        // Ambil daftar kategori user (kecuali kategori sistem)
        $categories = Category::where('user_id', $userId)
            ->whereNotIn('nama', ['Tabungan', 'Penarikan Tabungan', 'Pencairan Tabungan Dihapus'])
            ->get();

        return view('saving-targets.index', compact('savingTargets', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_target' => 'required|string|max:255',
            'target_nominal' => 'required|numeric|min:1',
            'terkumpul' => 'nullable|numeric|min:0',
            'category_id' => 'required',
            'new_category_nama' => 'nullable|string|max:255',
            'tipe' => 'nullable|in:masuk,keluar',
        ]);

        $userId = Auth::id();
        $terkumpulAwal = $request->terkumpul ?? 0;
        $categoryId = $request->category_id;

        // Tangani jika user memilih tambah kategori baru inline
        if ($categoryId === 'new') {
            if (empty($request->new_category_nama)) {
                return back()->withErrors(['new_category_nama' => 'Nama kategori baru wajib diisi.'])->withInput();
            }

            $existingCategory = Category::where('user_id', $userId)
                ->where('nama', $request->new_category_nama)
                ->first();

            if ($existingCategory) {
                $categoryId = $existingCategory->id;
            } else {
                $newCat = Category::create([
                    'user_id' => $userId,
                    'nama' => $request->new_category_nama,
                    'tipe' => $request->tipe ?? 'keluar',
                ]);
                $categoryId = $newCat->id;
            }
        }

        // 1. Buat target tabungan baru
        $savingTarget = SavingTarget::create([
            'user_id' => $userId,
            'nama_target' => $request->nama_target,
            'target_nominal' => $request->target_nominal,
            'terkumpul' => $terkumpulAwal,
        ]);

        // 2. Jika ada setoran awal, catat ke transaksi menggunakan kategori yang dipilih user
        if ($terkumpulAwal > 0) {
            Transaction::create([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'deskripsi' => 'Setor Tabungan: ' . $savingTarget->nama_target,
                'tipe' => 'keluar',
                'nominal' => $terkumpulAwal,
                'tanggal' => date('Y-m-d'),
            ]);
        }

        return redirect()->route('saving-targets.index')->with('success', 'Target tabungan berhasil ditambahkan!');
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
            'category_id' => 'required',
            'new_category_nama' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();
        $nominal = $request->tambah_nominal;
        $categoryId = $request->category_id;

        if ($categoryId === 'new') {
            if (empty($request->new_category_nama)) {
                return back()->withErrors(['new_category_nama' => 'Nama kategori baru wajib diisi.'])->withInput();
            }

            $existingCategory = Category::where('user_id', $userId)
                ->where('nama', $request->new_category_nama)
                ->first();

            if ($existingCategory) {
                $categoryId = $existingCategory->id;
            } else {
                $newCat = Category::create([
                    'user_id' => $userId,
                    'nama' => $request->new_category_nama,
                    'tipe' => 'keluar',
                ]);
                $categoryId = $newCat->id;
            }
        }

        $savingTarget->terkumpul += $nominal;
        $savingTarget->save();

        Transaction::create([
            'user_id' => $userId,
            'category_id' => $categoryId,
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
            'category_id' => 'required',
            'new_category_nama' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();
        $nominal = $request->tarik_nominal;
        $categoryId = $request->category_id;

        if ($nominal > $savingTarget->terkumpul) {
            return back()->withErrors(['tarik_nominal' => 'Nominal penarikan melebihi saldo terkumpul saat ini!']);
        }

        if ($categoryId === 'new') {
            if (empty($request->new_category_nama)) {
                return back()->withErrors(['new_category_nama' => 'Nama kategori baru wajib diisi.'])->withInput();
            }

            $existingCategory = Category::where('user_id', $userId)
                ->where('nama', $request->new_category_nama)
                ->first();

            if ($existingCategory) {
                $categoryId = $existingCategory->id;
            } else {
                $newCat = Category::create([
                    'user_id' => $userId,
                    'nama' => $request->new_category_nama,
                    'tipe' => 'masuk',
                ]);
                $categoryId = $newCat->id;
            }
        }

        $savingTarget->terkumpul -= $nominal;
        $savingTarget->save();

        Transaction::create([
            'user_id' => $userId,
            'category_id' => $categoryId,
            'deskripsi' => 'Penarikan Tabungan: ' . $savingTarget->nama_target,
            'tipe' => 'masuk',
            'nominal' => $nominal,
            'tanggal' => date('Y-m-d'),
        ]);

        return redirect()->route('saving-targets.index')->with('success', 'Berhasil melakukan pengambilan saldo! Dana kembali masuk ke saldo utama.');
    }
}