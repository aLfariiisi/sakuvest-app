<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\SavingTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Transaction::with('category')->where('user_id', $userId);
    
        if ($request->has('search') && $request->search != '') {
            $query->where('deskripsi', 'like', '%' . $request->search . '%');
        }
        if ($request->has('bulan') && $request->bulan != '') {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->has('tahun') && $request->tahun != '') {
            $query->whereYear('tanggal', $request->tahun);
        }
    
        // Urutkan dari yang lama ke baru (Akuntansi formal: asc)
        $transactions = $query->orderBy('tanggal', 'asc')
                            ->orderBy('created_at', 'asc')
                            ->get();

        $categories = Category::where('user_id', $userId)
            ->whereNotIn('nama', ['Tabungan', 'Penarikan Tabungan', 'Pencairan Tabungan Dihapus'])
            ->get();

        $savingTargets = SavingTarget::where('user_id', $userId)->get();
        
        return view('transactions.index', compact('transactions', 'categories', 'savingTargets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable',
            'tanggal' => 'required|date',
            'deskripsi' => 'required|string|max:255',
            'tipe' => 'required|in:masuk,keluar',
            'nominal' => 'required|numeric|min:0',
            'new_category_nama' => 'nullable|string|max:255',
        ]);

        $categoryId = $request->category_id;

        // Jika user memilih opsi tambah kategori baru
        if ($categoryId === 'new' && !empty($request->new_category_nama)) {
            // Cek apakah kategori dengan nama tersebut sudah ada untuk user ini agar tidak duplikat
            $existingCategory = Category::where('user_id', Auth::id())
                ->where('nama', $request->new_category_nama)
                ->first();

            if ($existingCategory) {
                $categoryId = $existingCategory->id;
            } else {
                $newCat = Category::create([
                    'user_id' => Auth::id(),
                    'nama' => $request->new_category_nama,
                    'tipe' => $request->tipe, // Mengikuti tipe transaksi yang dipilih
                ]);
                $categoryId = $newCat->id;
            }
        } elseif ($categoryId === 'new' && empty($request->new_category_nama)) {
            return back()->withErrors(['new_category_nama' => 'Nama kategori baru wajib diisi.'])->withInput();
        } elseif (!empty($categoryId)) {
            // Validasi kategori sistem yang diblokir
            $category = Category::find($categoryId);
            $restrictedCategories = ['Tabungan', 'Penarikan Tabungan', 'Pencairan Tabungan Dihapus'];
            if ($category && in_array($category->nama, $restrictedCategories)) {
                return back()->withErrors(['category_id' => 'Kategori sistem ini tidak dapat dipilih secara manual.']);
            }
        } else {
            $categoryId = null; // Tanpa Kategori
        }

        Transaction::create([
            'user_id' => Auth::id(),
            'category_id' => $categoryId,
            'tanggal' => $request->tanggal,
            'deskripsi' => $request->deskripsi,
            'tipe' => $request->tipe,
            'nominal' => $request->nominal,
        ]);

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil ditambahkan!');
    }

    public function update(Request $request, Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $isSavings = str_starts_with($transaction->deskripsi, 'Setor Tabungan:') || 
                     str_starts_with($transaction->deskripsi, 'Penarikan Tabungan:') || 
                     str_starts_with($transaction->deskripsi, 'Refund Tabungan') ||
                     optional($transaction->category)->nama == 'Tabungan';

        if ($isSavings) {
            return redirect()->route('transactions.index')->with('error', 'Transaksi tabungan tidak dapat diedit di sini. Kelola langsung melalui halaman Target Tabungan.');
        }

        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'tanggal' => 'required|date',
            'deskripsi' => 'required|string|max:255',
            'tipe' => 'required|in:masuk,keluar',
            'nominal' => 'required|numeric|min:0',
        ]);

        $userId = Auth::id();
        $transaction->delete();

        Transaction::create([
            'user_id' => $userId,
            'category_id' => $request->category_id,
            'tanggal' => $request->tanggal,
            'deskripsi' => $request->deskripsi,
            'tipe' => $request->tipe,
            'nominal' => $request->nominal,
        ]);

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil diperbarui dan dicatat sebagai riwayat baru!');
    }

    public function destroy(Transaction $transaction)
    {
        if ($transaction->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $isSavings = str_starts_with($transaction->deskripsi, 'Setor Tabungan:') || 
                     str_starts_with($transaction->deskripsi, 'Penarikan Tabungan:') || 
                     str_starts_with($transaction->deskripsi, 'Refund Tabungan') ||
                     optional($transaction->category)->nama == 'Tabungan';

        if ($isSavings) {
            return redirect()->route('transactions.index')->with('error', 'Transaksi tabungan tidak dapat dihapus di sini.');
        }

        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus!');
    }
}