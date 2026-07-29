<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $bulan = $request->input('bulan', date('m'));
        $tahun = $request->input('tahun', date('Y'));

        // Ambil data anggaran sesuai bulan & tahun yang dipilih
        $budgets = Budget::with('category')
            ->where('user_id', $userId)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->get()
            ->map(function ($budget) use ($userId, $bulan, $tahun) {
                // Hitung total pengeluaran aktual untuk kategori ini pada bulan & tahun tersebut
                $terpakai = Transaction::where('user_id', $userId)
                    ->where('category_id', $budget->category_id)
                    ->whereMonth('tanggal', $bulan)
                    ->whereYear('tanggal', $tahun)
                    ->where('tipe', 'keluar')
                    ->sum('nominal');

                $budget->terpakai = $terpakai;
                $budget->persentase = $budget->limit_nominal > 0 
                    ? min(round(($terpakai / $budget->limit_nominal) * 100), 100) 
                    : 0;
                return $budget;
            });

        // Ambil kategori berjenis pengeluaran ('keluar') untuk pilihan form
        $categories = Category::where('user_id', $userId)
            ->where('tipe', 'keluar')
            ->get();

        return view('budgets.index', compact('budgets', 'categories', 'bulan', 'tahun'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required',
            'limit_nominal' => 'required|numeric|min:1',
            'bulan' => 'required|numeric|between:1,12',
            'tahun' => 'required|numeric|digits:4',
            'new_category_nama' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();
        $categoryId = $request->category_id;

        // Jika user memilih opsi tambah kategori baru
        if ($categoryId === 'new') {
            if (empty($request->new_category_nama)) {
                return back()->withErrors(['new_category_nama' => 'Nama kategori baru wajib diisi.'])->withInput();
            }

            // Cek apakah kategori dengan nama tersebut sudah ada
            $existingCategory = Category::where('user_id', $userId)
                ->where('nama', $request->new_category_nama)
                ->first();

            if ($existingCategory) {
                $categoryId = $existingCategory->id;
            } else {
                $newCat = Category::create([
                    'user_id' => $userId,
                    'nama' => $request->new_category_nama,
                    'tipe' => 'keluar', // Anggaran dikhususkan untuk pengeluaran
                ]);
                $categoryId = $newCat->id;
            }
        }

        // Cek apakah anggaran untuk kategori dan bulan tersebut sudah pernah dibuat
        $exists = Budget::where('user_id', $userId)
            ->where('category_id', $categoryId)
            ->where('bulan', $request->bulan)
            ->where('tahun', $request->tahun)
            ->exists();

        if ($exists) {
            return back()->withErrors(['category_id' => 'Anggaran untuk kategori ini pada bulan tersebut sudah ada.'])->withInput();
        }

        Budget::create([
            'user_id' => $userId,
            'category_id' => $categoryId,
            'limit_nominal' => $request->limit_nominal,
            'bulan' => $request->bulan,
            'tahun' => $request->tahun,
        ]);

        return redirect()->route('budgets.index')->with('success', 'Anggaran bulanan berhasil ditambahkan!');
    }

    public function destroy(Budget $budget)
    {
        if ($budget->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $budget->delete();

        return redirect()->route('budgets.index')->with('success', 'Anggaran berhasil dihapus!');
    }
}