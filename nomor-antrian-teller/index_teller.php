<?php
include "../header.php";
?>

<body class="d-flex flex-column h-100">
  <main class="flex-shrink-0">
    <div class="container pt-5">
      <div class="row justify-content-lg-center">
        <div class="col-lg-5 mb-4">
          <div class="px-4 py-3 mb-4 bg-white rounded-2 shadow-sm">
            <!-- judul halaman -->
            <div class="d-flex align-items-center me-md-auto">
              <i class="bi-people-fill text-success me-3 fs-3"></i>
              <h1 class="h5 pt-2">Nomor Antrian Loket Teller</h1>
            </div>
          </div>

          <div class="card border-0 shadow-sm">
            <div class="card-body text-center d-grid p-5">
              <div class="border border-success rounded-2 py-2 mb-5">
                <h3 class="pt-4">ANTRIAN</h3>
                <!-- menampilkan informasi jumlah antrian -->
                <h1 id="antrian" class="display-1 fw-bold text-success text-center lh-1 pb-2"></h1>
              </div>
              <!-- button pengambilan nomor antrian -->
              <a id="insert" href="javascript:void(0)" class="btn btn-success btn-block rounded-pill fs-5 px-5 py-4 mb-2">
                <i class="bi-person-plus fs-4 me-2"></i> Ambil Nomor
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer -->
  <?php
  include "../footer.php";
  ?>

  <!-- jQuery Core -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
  <!-- Popper and Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.min.js" integrity="sha384-Atwg2Pkwv9vp0ygtn1JAojH0nYbwNJLPhwyoVbhoPwBhjQPR5VtM2+xf0Uwh9KtT" crossorigin="anonymous"></script>
  <script src="print_blue.js"></script>

  <script type="text/javascript">
    $(document).ready(function() {
      // Tampilkan jumlah antrian
      $('#antrian').load('get_antrian_teller.php');

      // Event klik untuk mengambil nomor antrian
      $('#insert').on('click', function() {
        $.ajax({
          type: 'POST',
          url: 'insert_teller.php',
          success: function(result) {
            if (result === 'Sukses') {
              // Update nomor antrian setelah berhasil
              $('#antrian').load('get_antrian_teller.php', function() {
                const nomorAntrian = $('#antrian').text().trim(); // Ambil teks nomor antrian

                // ESC/POS Commands
                const content = `
\x1B\x40 
\x1B\x61\x01
\x1B\x45\x01PERUMDA BPR SUKABUMI\x1B\x45\x00
Cabang Cikembar\n
\x1B\x61\x01ANTRIAN Teller\n
\x1D\x21\x11NO ${nomorAntrian}\x1D\x21\x00\n
${new Date().toLocaleString('id-ID', {
              weekday: 'long',
              day: '2-digit',
              month: '2-digit',
              year: 'numeric',
              hour: '2-digit',
              minute: '2-digit',
              second: '2-digit',
            })}
\n-------------------\n
`;

                // Kirim ke printer Bluetooth
                connectToBluetoothPrinter(content);
              });
            }
          },
          error: function(xhr) {
            console.error('Error:', xhr.responseText);
            alert('Terjadi kesalahan. Silakan coba lagi.');
          },
        });
      });
    });


    function printSection(id) {
      var content = document.getElementById(id).innerHTML;
      var printWindow = window.open('', '', 'height=600,width=800');
      printWindow.document.write('<html><head><title>Cetak Nomor Antrian</title></head><body>');
      printWindow.document.write(content);
      printWindow.document.write('</body></html>');
      printWindow.document.close();
      printWindow.print();
    }
  </script>
</body>

</html>