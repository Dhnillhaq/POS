@extends('layouts.template')
@section('content')

    <!-- Profile Image -->
    <div class="d-flex justify-content-center align-items-center">
        <div class="card card-primary card-outline"
             style="width: 30vw; height: 62vh;">
            <div class="card-body box-profile d-flex flex-column align-items-center justify-content-center">
                <div class="text-center mb-3">
                    <img class="rounded-circle img-thumbnail"
                         src="{{ Auth::user()->profile_image ? asset('storage/' . Auth::user()->profile_image) : asset('storage/profile_images/default.jpg') }}"    
                         style="width: 200px; height: 200px; object-fit: cover;" alt="User profile picture">
                </div>
    
                <h3 class="profile-username">{{ Auth::user()->nama }}</h3>
    
                <p class="text-muted">{{ Auth::user()->level->level_nama }}</p>
    
                <button onclick="modalAction('{{ url('/profil/change_photo') }}')" class="btn btn-primary btn mt-3">Ubah Foto Profil</button>
            </div>
        </div>
    </div>
    <div id="myModal" class="modal fade animate shake" tabindex="-1" data-backdrop="static" data-keyboard="false"
        data-width="75%"></div>
@endsection
@push('js')

    {{-- Jika ada javascript taro sini --}}
    <script>
        function modalAction(url = '') {
            $('#myModal').load(url, function () {
                $('#myModal').modal('show');
            });
        }
    </script>
    
@endpush