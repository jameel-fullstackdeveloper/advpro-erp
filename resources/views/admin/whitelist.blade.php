<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IP Whitelist | QuickERP</title>
    <!-- App favicon -->
    <link rel="shortcut icon" href="http://sfpro.quickerp.net/build/images/favicon.ico">
    <!-- Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- FontAwesome via CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- SweetAlert2 via CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col items-center p-4">

    <!-- Header Section -->
    <header class="w-full bg-white shadow-md p-4">
        <div class="flex justify-between items-center">
            <!-- Logo Section (Left) -->
            <div class="flex items-center space-x-2 font-bold text-xl">
                <img src="{{ asset('images/logo-sm-1.png') }}" alt="logo" class="w-12 h-auto sm:w-10 md:w-10" />
            </div>

            <!-- Company Name (Center) -->
            <div class="flex-1 text-center font-bold text-xl">
                    QuickERP
            </div>

            <!-- Menu Section (Right) - Horizontal Bar Menu -->
            <nav class="flex space-x-6">
                <!--<a href="#" class="text-gray-700 hover:text-blue-500">Home</a>
                <a href="#" class="text-gray-700 hover:text-blue-500">Whitelist</a>
                <a href="#" class="text-gray-700 hover:text-blue-500">Settings</a>-->
                <form action="{{ route('whitelist.logout') }}" method="POST">
                    @csrf
                    <button
                        type="submit"
                        class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600"
                    >
                        Logout
                    </button>
                </form>
            </nav>
        </div>
    </header>

    <!-- Main Content Section -->
    <div class="bg-white shadow-md rounded-lg w-full max-w-md sm:max-w-lg p-6 mt-4">


        @if(session('success'))
            <p class="text-green-600 bg-green-100 p-2 rounded-md mb-4 text-center">{{ session('success') }}</p>
        @endif

        @if($errors->any())
            <div class="bg-red-100 text-red-600 p-2 rounded-md mb-4">
                <ul class="list-disc pl-4">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.whitelist.store') }}" method="POST" class="mb-6">
            @csrf
            <div class="flex flex-col sm:flex-row items-center gap-4">
                <input
                    type="text"
                    name="ip_address"
                    placeholder="Enter IP Address"
                    required
                    class="w-full px-4 py-2 border rounded-md focus:outline-none focus:ring focus:ring-blue-300"
                >
                <button
                    type="submit"
                    class="w-full sm:w-auto bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600"
                >
                    Add
                </button>
            </div>
        </form>

        <h2 class="text-lg sm:text-xl font-semibold mb-4 text-center">Whitelisted IPs</h2>
        <ul class="space-y-2">
            @foreach ($ips as $ip)
                <li class="text-gray-700 border-b pb-2 flex justify-between items-center">
                    {{ $ip->ip_address }}
                    <button
                        onclick="confirmDelete('{{ route('admin.whitelist.destroy', $ip->id) }}')"
                        class="text-red-500 hover:text-red-700"
                    >
                        <i class="fas fa-trash-alt"></i> <!-- FontAwesome trash icon -->
                    </button>
                </li>
            @endforeach
        </ul>

        <!-- JavaScript for SweetAlert2 Confirmation -->
        <script>
            function confirmDelete(actionUrl) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // If confirmed, submit the form
                        var form = document.createElement('form');
                        form.method = 'POST';
                        form.action = actionUrl;

                        // Add csrf token
                        var csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';
                        form.appendChild(csrfToken);

                        // Add method delete
                        var methodDelete = document.createElement('input');
                        methodDelete.type = 'hidden';
                        methodDelete.name = '_method';
                        methodDelete.value = 'DELETE';
                        form.appendChild(methodDelete);

                        // Append the form and submit it
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
        </script>
    </div>

    <!-- Footer Section -->
    <!-- Footer Section -->
    <footer class="bg-white shadow-md w-full p-4 mt-auto">
        <div class="flex justify-between items-center text-sm text-gray-600">
            <!-- Left Side: Copyright -->
            <p>&copy; <?php echo date('Y'); ?> QuickERP</p>

            <!-- Right Side: Developed By -->
            <p>Jameel Ahmed | 923453569417</p>
        </div>
    </footer>

</body>
</html>
