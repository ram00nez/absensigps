<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AbsensiController extends Controller
{
    public function create()
    {
        $hariini = date("Y-m-d");
        $nik = Auth::guard('karyawan')->user()->nik;
        $cek = DB::table('absensi')->where('tgl_absen',$hariini)->where('nik',$nik)->count();
        return view('absensi.create', compact('cek'));
        
    }
    
    public function store(Request $request){
        $nik = Auth::guard('karyawan')->user()->nik;
        $tgl_absen = date("Y-m-d" );
        $jam = date("H:i:s");


        // $lalitudekantor = -6.218868413903974;
        // $longitudekantor =  106.70393884696168;
        $lokasi = $request->lokasi;
        // $lokasiuser = explode(",",$lokasi);
        // $latitudeuser = $lokasiuser[0];
        // $longitudeuser = $lokasiuser[1];
        // $jarak = $this->distance( $lalitudekantor, $longitudekantor, $latitudeuser, $longitudeuser);
        // $radius = round($jarak["meters"]);

        
        $cek = DB::table('absensi')->where('tgl_absen',$tgl_absen)->where('nik',$nik)->count();
        if($cek > 0){
            $ket = "out";
        }else{
            $ket = "in";
        }


        $image = $request->image;
        $folderPath = "public/uploads/absensi/";
        $formatName = $nik."-".$tgl_absen."-".$ket;
        $image_parts = explode(";base64", $image);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $formatName.".png";
        $file = $folderPath.$fileName;
        
        
        // if($radius > 10){
        //     echo " error|Maaf Anda Berada di Luar Radius|";
        // }else{

        
        if($cek > 0){
            $data_pulang =[
                'jam_out'=> $jam,
                'foto_out'=> $fileName,
                'lokasi_out' => $lokasi
            ];
            $update = DB::table('absensi')->where('tgl_absen',$tgl_absen)->where('nik',$nik)->update($data_pulang);
            if($update){
                echo "success|Terimakasih, Hati - Hati di Jalan|out";
                Storage::put($file,$image_base64);  
                }else{
                    echo "error|Maaf Gagal Absen, Silahkan Hubungi IT|out";
                }
        }else{
            $data = [
                'nik' => $nik
                , 'tgl_absen' => $tgl_absen
                , 'jam_in' => $jam
                , 'foto_in' => $fileName
                , 'lokasi_in' => $lokasi
            ];
    
            $simpan = DB::table('absensi')->insert($data);
            if($simpan){
                echo "success|Terimakasih, Selamat Bekerja|in";
            Storage::put($file,$image_base64);  
            }else{
                echo "error|Maaf Gagal Absen, Silahkan Hubungi IT|in";
            }
        }

    //  }
    
    }

    //Menghitung Jarak
    function distance($lat1, $lon1, $lat2, $lon2)
    {
        $theta = $lon1 - $lon2;
        $miles = (sin(deg2rad($lat1)) * sin(deg2rad($lat2))) + (cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta)));
        $miles = acos($miles);
        $miles = rad2deg($miles);
        $miles = $miles * 60 * 1.1515;
        $feet = $miles * 5280;
        $yards = $feet / 3;
        $kilometers = $miles * 1.609344;
        $meters = $kilometers * 1000;
        return compact('meters');
    }

    public function editprofile()
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $karyawan = DB::table('karyawan')->where('nik', $nik)->first();
        return view('absensi.editprofile', compact('karyawan'));
    }

    public function updateprofile(Request $request)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $nama_lengkap = $request->nama_lengkap;
        $email = $request->email;
        $no_hp = $request->no_hp;
        $password = Hash::make($request->password);
        $karyawan = DB::table('karyawan')->where('nik', $nik)->first();

        if($request->hasFile('foto')) {
            $foto = $nik.".".$request->file('foto')->getClientOriginalExtension();
        }else{
            $foto = $karyawan->foto;
        }
        if(empty($request->password)){
            $data = [
                'nama_lengkap' => $nama_lengkap,
                'email'=> $email,
                'no_hp'=> $no_hp,
                'foto'=> $foto
            ];
        }else{
            $data = [
                'nama_lengkap' => $nama_lengkap,
                'email'=> $email,
                'no_hp'=> $no_hp,
                'password'=> $password,
                'foto'=> $foto
            ];
        }

        $update = DB::table('karyawan')->where('nik', $nik)->update($data);
        if($update){
            if ($request->hasFile('foto')){
                $folderPath = "uploads/karyawan/";
                $request->file('foto')->storeAs($folderPath, $foto);
            }
            return Redirect::back()->with(['success' => 'Data Behasil Di simpan']);
        }else{
            return Redirect::back()->with(['error' => 'Data Gagal Di Update']);
        }
        
    }

    public function history()
    {
        $namabulan = ["","Januari","Febuari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
        return view('absensi.history',compact('namabulan'));
    }

    public function gethistory(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        $nik = Auth::guard('karyawan')->user()->nik;

        $history = DB::table('absensi')
        ->whereRaw('MONTH(tgl_absen)="'.$bulan.'"')
        ->whereRaw('YEAR(tgl_absen)="'.$tahun.'"')
        ->where('nik', $nik)
        ->orderBy('tgl_absen')
        ->get();
        
        return view('absensi.gethistory', compact('history'));
    }
    public function izin()
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $dataizin = DB::table('pengajuan_izin')->where('nik', $nik)->get();
        return view('absensi.izin',compact('dataizin'));
    }

    public function buatizin()
    {
        
        return view('absensi.buatizin');
    }

    public function storeizin(Request $request)
    {
        $nik = Auth::guard('karyawan')->user()->nik;
        $tgl_izin = $request->tgl_izin;
        $status = $request->status;
        $keterangan = $request->keterangan;

        $data = [
            'nik' => $nik,
            'tgl_izin' => $tgl_izin,
            'status' => $status,
            'keterangan' => $keterangan
        ];

        $simpan = DB::table('pengajuan_izin')->insert($data);
        if($simpan){
            return redirect('/absensi/izin')->with(['success'=>'Data Berhasil Disimpan']);
        }else{
            return redirect('/absensi/izin')->with(['error'=>'Data Gagal Disimpan']);
        }

    }

    public function monitoring()
    {
        return view('absensi.monitoring');
    }

    public function getabsensi(Request $request)
    {
        $tanggal = $request->tanggal;
        $absensi = DB::table('absensi')
        ->select('absensi.*','nama_lengkap','nama_dept','jobdesk')
        ->join('karyawan','absensi.nik','=','karyawan.nik')
        ->join('departemen','karyawan.kode_dept','=','departemen.kode_dept')
        ->where('tgl_absen',$tanggal)
        ->get();

        return view('absensi.getabsensi',compact('absensi'));
    }

    public function tampilkanpeta(Request $request)
    {
        $id = $request->id;
        $absensi = DB::table('absensi')->where('id',$id)
        ->join('karyawan','absensi.nik','=','karyawan.nik')
        ->first();
        return view('absensi.showmap',compact('absensi'));
    }
}
