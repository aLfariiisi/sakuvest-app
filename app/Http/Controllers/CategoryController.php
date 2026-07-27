<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function index()
    {
        // Mengambil semua kategori milik user yang sedang login
        $categories = Category::where('user_id', Auth::id())->orderBy('created_at', 'desc')->get();
        
        // Mengembalikan tampilan Blade yang sudah kita buat
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        // Validasi inputan
        $request->validate([
            'nama' => 'required|string|max:255',
            'tipe' => 'required|in:masuk,keluar',
        ]);

        // Simpan kategori baru
        Category::create([
            'user_id' => Auth::id(),
            'nama' => $request->nama,
            'tipe' => $request->tipe,
        ]);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil ditambahkan!');
    }

    public function update(Request $request, Category $category)
    {
        // Pastikan user tidak mengedit kategori milik orang lain
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'tipe' => 'required|in:masuk,keluar',
        ]);

        $category->update([
            'nama' => $request->nama,
            'tipe' => $request->tipe,
        ]);

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil diperbarui!');
    }

    public function destroy(Category $category)
    {
        // Pastikan user tidak menghapus kategori milik orang lain
        if ($category->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Kategori berhasil dihapus!');
    }
}