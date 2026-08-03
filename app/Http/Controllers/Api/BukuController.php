<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BukuController extends Controller
{
    public function index(Request $request)
    {
        $query = Buku::query();

        if ($request->search) {
            $query->where('judul', 'like', '%' . $request->search . '%');
        }

        $data = $query->orderBy('judul','asc')->paginate(10);

        return response()->json([
            'status' => true,
            'message' => 'Data ditemukan',
            'data' => $data
        ]);
    }
    public function store(Request $request)
    {
        $dataBuku = new Buku;

        $rules = [
            'judul' => 'required',
            'pengarang' => 'required',
            'tanggal_publikasi' => 'required|date',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Gagal memasukkan data',
                'data' => $validator->errors()
            ]);
        }

        $dataBuku->judul = $request->judul;
        $dataBuku->pengarang = $request->pengarang;
        $dataBuku->tanggal_publikasi = $request->tanggal_publikasi;

        // Proses Upload Gambar
        if ($request->hasFile('gambar')) {
            $gambarPath = $request->file('gambar')->store('gambar-buku', 'public');
            $dataBuku->gambar = $gambarPath;
        }

        $dataBuku->save();

        return response()->json([
            'status'=>true,
            'message'=> 'Sukses memasukkan data'
        ]);
    }

    public function show($id)
    {
        $data = Buku::find($id);

        if (!$data) {
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ],404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data ditemukan',
            'data' => $data
        ],200);
    }

    public function update(Request $request, string $id)
    {
        $dataBuku = Buku::find($id);
        if(empty($dataBuku)){
            return response()->json([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $rules = [
            'judul' => 'required',
            'pengarang' => 'required',
            'tanggal_publikasi' => 'required|date',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' // <-- Validasi gambar
        ];
        $validator = Validator::make($request->all(), $rules);
        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Gagal melakukan update data',
                'data' => $validator->errors()
            ]);
        }

        $dataBuku->judul = $request->judul;
        $dataBuku->pengarang = $request->pengarang;
        $dataBuku->tanggal_publikasi = $request->tanggal_publikasi;

        // Proses Update Gambar
        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($dataBuku->gambar && Storage::disk('public')->exists($dataBuku->gambar)) {
                Storage::disk('public')->delete($dataBuku->gambar);
            }
            // Simpan gambar baru
            $gambarPath = $request->file('gambar')->store('gambar-buku', 'public');
            $dataBuku->gambar = $gambarPath;
        }

        $dataBuku->save();

        return response()->json([
            'status'=>true,
            'message'=> 'Sukses melakukan update data'
        ]);
    }

    public function destroy(string $id)
    {
        $data = Buku::find($id);

        if (!$data) {
            return response()->json([
            'status' => false,
            'message' => 'Data tidak ditemukan'
            ],404);
        }

        if ($data->gambar && Storage::disk('public')->exists($data->gambar)) {
        Storage::disk('public')->delete($data->gambar);

        }

        $data->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil dihapus'
            ],200);
    }
}
