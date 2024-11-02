<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use RealRashid\SweetAlert\Facades\Alert;

class NotifController extends Controller
{
 
	public function success(){
		// Alert::success('Absen Berhasil', 'Hati Hati di Jalan')->autoClose(3000);
		Alert::success('Absen Berhasil', 'Hati Hati di Jalan')->autoClose(3000);
		return redirect('dashboard');
	}
 
	public function pulang(){
        Alert::success('Absen Pulang Berhasil', 'Terimakasih, Hati - Hati di Jalan')->autoClose(3000);
		return redirect('dashboard');
	}

	public function peringatan(){
        Alert::warning('peringatan', 'Network Error')->autoClose(3000);
		return redirect('/absensi/create');
	}
 
	public function gagal(){
        Alert::warning('Absensi Gagal', 'Silahkan Hubungi Tim IT')->autoClose(3000);
		return redirect('/absensi/create');
	}
}
