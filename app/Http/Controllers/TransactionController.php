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
    
        $transactions = $query->orderBy('tanggal', 'desc')->get();
        $categories = Category::where('user_id', $userId)->get();
        $savingTargets = SavingTarget::where('user_id', $userId)->get();
        
        return view('transactions.index', compact('transactions', 'categories', 'savingTargets'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'tanggal' => 'required|date',
            'deskripsi' => 'required|string|max:255',
            'tipe' => 'required|in:masuk,keluar',
            'nominal' => 'required|numeric|min:0',
        ]);

        Transaction::create([
            'user_id' => Auth::id(),
            'category_id' => $request->category_id,
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

        // Cek apakah ini transaksi tabungan (dikunci total di riwayat)
        $isSavings = str_starts_with($transaction->deskripsi, 'Setor Tabungan:') || 
                     str_starts_with($transaction->deskripsi, 'Penarikan Tabungan:') || 
                     str_starts_with($transaction->deskripsi, 'Refund Tabungan') ||
                     optional($transaction->category)->nama == 'Tabungan';

        if ($isSavings) {
            return redirect()->route('transactions.index')->with('error', 'Transaksi tabungan tidak dapat diedit di sini. Kelola langsung melalui halaman Target Tabungan.');
        }

        $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'tanggal' => 'required|date',
            'deskripsi' => 'required|string|max:255',
            'tipe' => 'required|in:masuk,keluar',
            'nominal' => 'required|numeric|min:0',
        ]);

        $userId = Auth::id();

        // Hapus transaksi lama agar tidak mengubah data in-place
        $transaction->delete();

        // Buat sebagai riwayat transaksi baru
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

        // Cek apakah ini transaksi tabungan
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