@extends('layouts.absensi')
@section('header')
<!-- App Header -->
<div class="appHeader bg-primary text-light">
        <div class="left">
            <a href="javascript:;" class="headerButton goBack">
                <ion-icon name="chevron-back-outline"></ion-icon>
            </a>
        </div>
        <div class="pageTitle">E-Absensi</div>
        <div class="right"></div>
    </div>
    <!-- * App Header -->
<style>
    .webcam-capture,
    .webcam-capture video{
        display: inline-block;
        width: 100% !important;
        margin: auto;
        height: auto !important;
        border-radius: 15px;
    }

    #map { 
        height: 200px; 
        }
</style>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@endsection
@section('content')
<!-- App Capsule -->
<div id="appCapsule">


<div class="row" style="margin-top: 70px">
    <div class="col">
        <input type="hidden" id="lokasi"> 
        <div class="webcam-capture"></div>
    </div>
</div>
<div class="row">
        <div class="col">

        @if ($cek > 0)
            <a href="/absensi/create/pulang" id="takeabsen" class="btn btn-danger btn-block"><ion-icon name="camera-outline"></ion-icon>Absen Pulang</a>
            @else
            <a href="/absensi/create/success" id="takeabsen" class="btn btn-success btn-block"><ion-icon name="camera-outline"></ion-icon>Absen Masuk</a>
        @endif

         <!-- <button id="takeabsen" class="btn btn-success btn-block">
         <ion-icon name="camera-outline"></ion-icon>   
         Absen Masuk</button> -->
        </div>
    </div>

<div class="row mt-2">
    <div class="col">
        <div id="map"></div>
</div>

<audio id="notifikasi_in">
    <source src="{{ asset('assets/sound/notifikasi_in.mp3') }}" type="audio/mpeg">
</audio>

<!-- * App Capsule -->
@endsection
@push('myscript')
<script>

    var notifikasi_in = document.getElementById('notifikasi_in');
    Webcam.set({
        height: 480
        , width: 640
        , image_format: 'jpeg'
        , jpeg_quality: 80
    });

    Webcam.attach('.webcam-capture')
    
    var lokasi = document.getElementById('lokasi');
        if(navigator.geolocation){
            navigator.geolocation.getCurrentPosition(succsessCallback, errorCallback);
        }

    function succsessCallback(Position){
        lokasi.value = Position.coords.latitude + "," + Position.coords.longitude;
        var map = L.map('map').setView([Position.coords.latitude, Position.coords.longitude], 18);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 20,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        var marker = L.marker([Position.coords.latitude, Position.coords.longitude]).addTo(map);
        //nanti ganti L.circle([latitude, longitude] ke titik koordinat yang diinginkan
        var circle = L.circle([Position.coords.latitude, Position.coords.longitude], {
            color: 'red',
            fillColor: '#f03',
            fillOpacity: 0.5,
            radius: 20
        }).addTo(map);

    }

    function errorCallback(){

    }

    //hapus ini nanti///////////////////////////////////////////////////////

//     $("#takeabsen").click(function(e){
//         Webcam.snap(function(uri){
//             image = uri;
//         });

//         var lokasi = $("#lokasi").val();
//         alert('success')
// });

///////////////////////////////////////////////////////

    $("#takeabsen").click(function(e){
        Webcam.snap(function(uri){
            image = uri;
        });

        var lokasi = $("#lokasi").val();
            //alert disini bisa 
        $.ajax({
            //tapi jika sudah di masukan di ajax jadi gak muncul notifnya
            type:'POST'
            , url:'/absensi/store'
            , data: {
                _token:"{{ csrf_token() }}",
                  image: image,
                  lokasi: lokasi
            }
            , cache: false
            , succeess: function(respond) {
                var status = respond.split("|");
                if (status["success"] == "success") {
                    if(status[2]=="in"){
                        notifikasi_in.play();
                    }
                    Swal.fire({
                    title: 'Berhsil Simpan!!'
                    , text: status[0]
                    , icon: 'sucssess'
                    , confirmButtonText: 'ok'
                    })
                setTimeOut("location.href='/dashboard'",3000);
                }else{
                    Swal.fire({
                    title: 'Gagal Simpan!!',
                    text: 'Silahkan Hubungi Team IT',
                    icon: 'error',
                    confirmButtonText: 'ok'
                    })
                }
            }
        });
});

</script>
@endpush
