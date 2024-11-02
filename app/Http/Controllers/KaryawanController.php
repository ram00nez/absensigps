<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Karyawan;
use Illuminate\Container\Attributes\Storage;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\ViewException;

class KaryawanController extends Controller
{
    public function index(Request $request)
    {
        $query = karyawan::query();
        $query->select('karyawan.*','nama_dept');
        $query->join('departemen', 'karyawan.kode_dept','=','departemen.kode_dept');
        $query->orderBy('nama_lengkap');
        if(!empty($request->nama_karyawan)){
            $query->where('nama_lengkap','like','%'.$request->nama_karyawan.'%');
        }

        if(!empty($request->kode_dept)){
            $query->where('karyawan.kode_dept',$request->kode_dept);
        }

        $karyawan = $query->paginate(10);

        $departemen = DB::table('departemen')->get();
        return view('karyawan.index',compact('karyawan','departemen'));
    }

    public function store(Request $request)
    {
        $nik = $request->nik;
        $nama_lengkap = $request->nama_lengkap;
        $email = $request->email;
        $jobdesk = $request->jobdesk;
        $no_hp = $request->no_hp;
        $kode_dept = $request->kode_dept;
        $password = Hash::make($request->password);
        if($request->hasFile('foto')) {
            $foto = $nik.".".$request->file('foto')->getClientOriginalExtension();
        }else{
            $foto = null;
        }

        try {
            $data = [
                'nik'=> $nik,
                'nama_lengkap'=> $nama_lengkap,
                'email'=> $email,
                'jobdesk'=> $jobdesk,
                'no_hp'=> $no_hp,
                'kode_dept'=> $kode_dept,
                'foto'=> $foto,
                'password'=> $password

            ];
            $simpan = DB::table('karyawan')->insert($data);
            if($simpan){
                if ($request->hasFile('foto')){
                    $folderPath = "public/upload/karyawan/";
                    $request->file('foto')->storeAs($folderPath, $foto);
                }
                return Redirect::back()->with(['success'=>'Data Berhasil Di Simpan']);
            }

        } catch (\Exception $e) {
            // dd($e);
            return Redirect::back()->with(['Warning'=>'Data Gagal Di Simpan']);
        }

    }

    public function edit(Request $request)
    {
        $nik = $request->nik;
        $departemen = DB::table('departemen')->get();
        $karyawan = DB::table('karyawan')->where('nik',$nik)->first();
        // dd($karyawan);
        return view('karyawan.edit',compact('departemen','karyawan'));
    }

    public function update($nik, Request $request)
    {
        $nik = $request->nik;
        $nama_lengkap = $request->nama_lengkap;
        $email = $request->email;
        $jobdesk = $request->jobdesk;
        $no_hp = $request->no_hp;
        $kode_dept = $request->kode_dept;
        $password = Hash::make($request->password);
        $oldfoto = $request->oldfoto;
        if($request->hasFile('foto')) {
            $foto = $nik.".".$request->file('foto')->getClientOriginalExtension();
        }else{
            $foto = $oldfoto;
        }

        try {
            $data = [
                'nama_lengkap'=> $nama_lengkap,
                'email'=> $email,
                'jobdesk'=> $jobdesk,
                'no_hp'=> $no_hp,
                'kode_dept'=> $kode_dept,
                'foto'=> $foto,
                'password'=> $password

            ];
            $update = DB::table('karyawan')->where('nik', $nik)->update( $data );
            if($update){
                if ($request->hasFile('foto')){
                    $folderPath = "public/upload/karyawan/";
                    // $folderPathOld = "public/upload/karyawan/".$old_foto;
                    // Storage::delete($folderPathOld);
                    $request->file('foto')->storeAs($folderPath, $foto);
                }
                return Redirect::back()->with(['success'=>'Data Berhasil Di Update']);
            }

        } catch (\Exception $e) {
            // dd($e);
            return Redirect::back()->with(['Warning'=>'Data Gagal Di Update']);
        }
    }

    public function delete($nik)
    {
            $delete = DB::table('karyawan')->where('nik', $nik)->delete();
            if($delete){
                // return Redirect::back()->with(['success'=>'Data Berhasil Di Hapus']);
                return Redirect('/karyawan'); 
            }else{
                return Redirect::back()->with(['warning'=>'Data Gagal Di Hapus']);
            }
                
    }
}