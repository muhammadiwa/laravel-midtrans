# Midtrans Payment API

API untuk pemrosesan pembayaran menggunakan Midtrans, lengkap dengan dokumentasi yang dibuat menggunakan Swagger.

## Fitur

- **Buat Transaksi Pembayaran**: Membuat transaksi baru dan mendapatkan Snap Token dari Midtrans.
- **Cek Status Transaksi**: Mendapatkan status transaksi berdasarkan `order_id`.
- **Halaman Pembayaran**: View sederhana untuk pembayaran menggunakan Snap.
- **Swagger API Documentation**: Mendokumentasikan API secara otomatis menggunakan [Swagger UI](https://swagger.io/).

## Persyaratan Sistem

Pastikan Anda memiliki hal-hal berikut terinstal di lingkungan development Anda:

- PHP >= 7.4
- [Composer](https://getcomposer.org/)
- [Laravel](https://laravel.com/) >= 8.x
- [Midtrans PHP SDK](https://github.com/veritrans/veritrans-php) untuk integrasi pembayaran
- [Laravel L5 Swagger](https://github.com/DarkaOnLine/L5-Swagger) untuk dokumentasi API

## Instalasi

1. Clone repository ini:
   ```bash
   git clone https://github.com/muhammadiwa/laravel-midtrans.git
   cd repo
   ```

2. Install dependensi menggunakan Composer:
   ```bash
   composer install
   ```

3. Buat file konfigurasi `.env`:
   ```bash
   cp .env.example .env
   ```

4. Generate kunci aplikasi Laravel:
   ```bash
   php artisan key:generate
   ```

5. Sesuaikan konfigurasi Midtrans pada `.env`:
   ```bash
   MIDTRANS_SERVER_KEY=your_server_key
   MIDTRANS_CLIENT_KEY=your_client_key
   ```

6. Jalankan migrasi database:
   ```bash
   php artisan migrate
   ```

7. Jalankan server pengembangan:
   ```bash
   php artisan serve
   ```

## Dokumentasi API

Dokumentasi API tersedia melalui Swagger dan dapat diakses setelah server berjalan. Untuk mengaksesnya:

1. Jalankan perintah berikut untuk menghasilkan dokumentasi:
   ```bash
   php artisan l5-swagger:generate
   ```

2. Buka URL berikut di browser:
   ```
   http://localhost:8000/api/documentation
   ```

## Contoh Endpoint

### Membuat Transaksi Pembayaran

- **Endpoint**: `/api/create-transaction`
- **Method**: `POST`
- **Request Body**:
  ```json
  {
    "order_id": "ORDER-123456789",
    "gross_amount": 100000,
    "customer_name": "John Doe",
    "customer_email": "john.doe@example.com",
    "customer_phone": "+6281234567890"
  }
  ```
- **Response**:
  ```json
  {
    "status": 200,
    "message": "Transaction created successfully",
    "snap_token": "eJx9j..."
  }
  ```

### Cek Status Transaksi

- **Endpoint**: `/api/transaction-status/{orderId}`
- **Method**: `GET`
- **Path Parameter**:
  - `orderId` (string): ID dari pesanan yang ingin dicek statusnya
- **Response**:
  ```json
  {
    "status": 200,
    "message": "Transaction status retrieved successfully",
    "data": {
      "status_code": "200",
      "transaction_status": "settlement",
      "payment_type": "bank_transfer"
    }
  }
  ```

## Halaman Pembayaran (View)

Selain API, proyek ini menyediakan view sederhana untuk menampilkan halaman pembayaran.

### Rute untuk Halaman Pembayaran

Tambahkan rute berikut di file `routes/web.php` untuk menampilkan halaman view pembayaran:

```php
Route::get('/payment', function () {
    return view('payment');
});
```

### Contoh View `resources/views/payment.blade.php`

Buat file Blade di `resources/views/payment.blade.php` untuk menampilkan form pembayaran dengan Midtrans Snap:

```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout</title>

    <!-- Include Bootstrap for styling (optional) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
        .checkout-form {
            max-width: 500px;
            margin: auto;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="checkout-form">
        <h2 class="text-center">Checkout</h2>
        <form id="paymentForm">
            <div class="mb-3">
                <label for="name" class="form-label">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" placeholder="Enter your name" required>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" placeholder="Enter your phone number" required>
            </div>

            <div class="mb-3">
                <label for="amount" class="form-label">Amount (IDR)</label>
                <input type="number" class="form-control" id="amount" name="amount" placeholder="Enter the amount" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Pay Now</button>
        </form>

        <!-- Display status or error message -->
        <div id="statusMessage" class="alert mt-3 d-none"></div>
    </div>

    <!-- Midtrans Snap.js Library -->
    <script type="text/javascript" src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

    <!-- Include jQuery (optional) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function() {
            // Form submit event
            $('#paymentForm').on('submit', function(event) {
                event.preventDefault();

                // Ambil data form
                const name = $('#name').val();
                const email = $('#email').val();
                const phone = $('#phone').val();
                const amount = $('#amount').val();

                // Validasi input
                if (!name || !email || !phone || !amount) {
                    showMessage('All fields are required', 'danger');
                    return;
                }

                // Kirim data transaksi ke server untuk membuat Snap Token
                $.ajax({
                    url: '/api/create-transaction',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        order_id: 'ORDER-' + Math.floor(Math.random() * 1000000), // Unique order ID
                        gross_amount: amount,
                        customer_name: name,
                        customer_email: email,
                        customer_phone: phone,
                    }),
                    success: function(response) {
                        if (response.status === 200) {
                            // Panggil Snap.js dan tampilkan halaman pembayaran
                            snap.pay(response.snap_token, {
                                onSuccess: function(result) {
                                    showMessage('Payment successful!', 'success');
                                    console.log('Payment Success:', result);
                                    // Handle hasil sukses di sini
                                },
                                onPending: function(result) {
                                    showMessage('Payment is pending.', 'warning');
                                    console.log('Payment Pending:', result);
                                    // Handle hasil pending di sini
                                },
                                onError: function(result) {
                                    showMessage('Payment failed.', 'danger');
                                    console.log('Payment Failed:', result);
                                    // Handle hasil gagal di sini
                                },
                                onClose: function() {
                                    showMessage('Payment popup closed without finishing.', 'warning');
                                    console.log('Payment popup closed without finishing');
                                    // Handle jika popup ditutup tanpa penyelesaian
                                }
                            });
                        } else {
                            showMessage(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showMessage('Failed to create transaction. Please try again.', 'danger');
                        console.error('Transaction error:', error);
                    }
                });
            });

            // Fungsi untuk menampilkan pesan status
            function showMessage(message, type) {
                const statusMessage = $('#statusMessage');
                statusMessage.text(message).removeClass('d-none alert-success alert-danger alert-warning').addClass(`alert-${type}`);
            }
        });
    </script>
</body>
</html>

```

### Cara Mengakses

Setelah menjalankan server, Anda bisa mengakses halaman pembayaran dengan membuka URL berikut di browser:

```
http://localhost:8000/payment
```

## Kontribusi

Jika Anda ingin berkontribusi pada proyek ini:

1. Fork repository ini.
2. Buat branch baru untuk fitur atau perbaikan Anda:
   ```bash
   git checkout -b feature/fitur-baru
   ```
3. Commit perubahan Anda:
   ```bash
   git commit -m "Menambahkan fitur baru"
   ```
4. Push ke branch tersebut:
   ```bash
   git push origin feature/fitur-baru
   ```
5. Buat Pull Request ke branch `main` pada repository ini.


---

Dokumentasi di atas sudah mencakup penjelasan lengkap mengenai API, view halaman pembayaran, dan rute yang diperlukan. Silakan disesuaikan dengan kebutuhan proyek Anda.