<form action="{{ url('/transaksi/ajax') }}" method="POST" id="form-tambah">
    @csrf
    <div id="modal-master" class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Data Penjualan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Kasir</label>
                    <select name="user_id" id="user_id" class="form-control" required>
                        <option value="">- Pilih Kasir -</option>
                        @foreach ($user as $k)
                            <option value="{{ $k->user_id }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                    <small id="error-user_id" class="error-text form-text text-danger"></small>
                </div>
                <div class="form-group">
                    <label>Pembeli</label>
                    <input value="" type="text" name="pembeli" id="pembeli" class="form-control" required>
                    <small id="error-pembeli" class="error-text form-text text-danger"></small>
                </div>
                <div class="form-group">
                    <label>Tanggal Penjualan</label>
                    <input value="" type="datetime-local" name="penjualan_tanggal" id="penjualan_tanggal"
                        class="form-control" required>
                    <small id="error-penjualan_tanggal" class="error-text form-text text-danger"></small>
                </div>

                <hr>
                <h5>Detail Penjualan</h5>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="detail-table">
                        <thead>
                            <tr>
                                <th>Barang</th>
                                <th width="150">Harga (Rp)</th>
                                <th width="100">Jumlah</th>
                                <th width="150">Subtotal (Rp)</th>
                                <th width="50">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="detail-items">
                            <!-- Detail items will be added here -->
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3" class="text-right">Total:</th>
                                <th id="total-amount">0</th>
                                <th></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="row mb-3">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-primary btn-sm" id="btn-add-item">
                            <i class="fa fa-plus"></i> Tambah Barang
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" data-dismiss="modal" class="btn btn-warning">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</form>

<!-- Template for detail item row -->
<template id="detail-row-template">
    <tr class="detail-row">
        <td>
            <select name="barang_id[]" class="form-control barang-select" required>
                <option value="">- Pilih Barang -</option>
                @foreach ($barang as $b)
                    <option value="{{ $b->barang_id }}" data-harga="{{ $b->harga_jual }}">{{ $b->barang_nama }}</option>
                @endforeach
            </select>
            <small class="error-text form-text text-danger"></small>
        </td>
        <td>
            <input type="number" name="harga[]" class="form-control harga-input" readonly>
            <small class="error-text form-text text-danger"></small>
        </td>
        <td>
            <input type="number" name="jumlah[]" class="form-control jumlah-input" min="1" value="1" required>
            <small class="error-text form-text text-danger"></small>
        </td>
        <td>
            <input type="text" class="form-control subtotal-display" readonly>
            <input type="hidden" class="subtotal-input">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm btn-remove-item">
                <i class="fa fa-trash"></i>
            </button>
        </td>
    </tr>
</template>
<script>

    function addDetailRow() {
        var template = document.getElementById('detail-row-template');
        var clone = document.importNode(template.content, true);
        $('#detail-items').append(clone);
    }

    // Function to update a row's subtotal
    function updateRowTotal(row) {
        var harga = parseFloat(row.find('.harga-input').val()) || 0;
        var jumlah = parseInt(row.find('.jumlah-input').val()) || 0;
        var subtotal = harga * jumlah;

        row.find('.subtotal-input').val(subtotal);
        row.find('.subtotal-display').val(formatRupiah(subtotal));

        calculateTotal();
    }

    // Function to calculate the total amount
    function calculateTotal() {
        var total = 0;
        $('.subtotal-input').each(function () {
            total += parseFloat($(this).val()) || 0;
        });

        $('#total-amount').text(formatRupiah(total));
    }

    // Function to format number as Rupiah
    function formatRupiah(number) {
        return new Intl.NumberFormat('id-ID').format(number);
    }




    $(document).ready(function () {

        addDetailRow();

        $("#btn-add-item").click(function () {
            addDetailRow();
        });


        $(document).on('click', '.btn-remove-item', function () {
            if ($('.detail-row').length > 1) {
                $(this).closest('tr').remove();
                calculateTotal();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Tidak Dapat Dihapus',
                    text: 'Minimal harus ada satu barang'
                });
            }
        });

        $(document).on('change', '.barang-select', function () {
            var row = $(this).closest('tr');
            var harga = $(this).find(':selected').data('harga') || 0;
            row.find('.harga-input').val(harga);
            updateRowTotal(row);
        });

        // When quantity changes, update subtotal
        $(document).on('input', '.jumlah-input', function () {
            updateRowTotal($(this).closest('tr'));
        });

        $("#form-tambah").validate({
            rules: {
                supplier_id: { required: true, number: true },
                barang_id: { required: true, number: true },
                penjualan_tanggal: { required: true, date: true },
                penjualan_jumlah: { required: true }
            },
            submitHandler: function (form) {
                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: $(form).serialize(),
                    success: function (response) {
                        if (response.status) {
                            $('#myModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: response.message
                            });
                            datapenjualan.ajax.reload();
                        } else {
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