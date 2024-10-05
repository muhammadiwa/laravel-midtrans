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
