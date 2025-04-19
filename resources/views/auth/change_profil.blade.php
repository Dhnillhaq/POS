<form action="{{ route('profile.upload') }}" method="POST" id="form-photo" enctype="multipart/form-data">
    @csrf
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ubah Foto Profil</h5>
                <button type="button" class="close" data-dismiss="modal" arialabel="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <img class="rounded-circle img-thumbnail" id="photo_preview"
                        src="{{ Auth::user()->profile_image ? asset('storage/' . Auth::user()->profile_image) : asset('storage/profile_images/default.jpg') }}"
                        style="width: 200px; height: 200px; object-fit: cover;" alt="User profile picture">
                </div>
                <div class="form-group">
                    <label>Pilih File</label>
                    <input type="file" name="profile_image" id="profile_image" class="form-control"
                        accept="image/jpeg, image/jpg, image/png " required>
                    <small id="error-file_user" class="error-text form-text text-danger"></small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-warning">Batal</button>
                <button type="submit" class="btn btn-primary">Ganti</button>
            </div>
        </div>
    </div>
</form>
<script>
    document.getElementById('profile_image').addEventListener('change', function (event) {
        const [file] = event.target.files;
        if (file) {
            document.getElementById('photo_preview').src = URL.createObjectURL(file);
        }

    });
    $(document).ready(function () {
        $("#form-photo").validate({
            rules: {
                file_user: { required: true, extension: "jpg|jpeg|png" },
            },
            submitHandler: function (form) {
                var formData = new FormData(form); // Jadikan form ke FormData untuk menghandle file
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: formData, // Data yang dikirim berupa FormData
                    processData: false, // setting processData dan contentType ke false,untuk menghandle file
                    contentType: false,
                    success: function (response) {
                        if (response.status) { // jika sukses
                            $('#myModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            });
                            location.reload(); // reload datatable
                        } else { // jika error
                            $('.error-text').text('');
                            $.each(response.msgField, function (prefix, val) {
                                $('#error-' + prefix).text(val[0]);
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: response.message
                            });
                        }
                    }
                });
                return false;
            },
            errorElement: 'span',
            errorPlacement: function (error, element) {
                error.addClass('invalid-feedback');
                element.closest('.form-group').append(error);
            },
            highlight: function (element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function (element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            }
        });
    });
</script>