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

            </nav>
        </div>
    </header>

   <!-- Main Content Section -->
   <div class="bg-white shadow-md rounded-lg w-full max-w-md sm:max-w-lg p-6 mt-4">
        <h1 class="text-2xl font-bold text-center mb-6">Login</h1>

        @if(session('error'))
            <p class="text-red-500 text-center mb-4">{{ session('error') }}</p>
        @endif

        <form action="{{ url('admin/whitelist/login') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Email:</label>
                <input type="email" name="email" id="email" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div class="mb-4">
                <label for="password" class="block text-sm font-medium text-gray-700">Password:</label>
                <input type="password" name="password" id="password" class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" required>
            </div>
            <div class="flex items-center mb-6">
                <label for="remember" class="flex items-center text-sm text-gray-600">
                    <input type="checkbox" name="remember" id="remember" class="mr-2">
                    Remember me
                </label>
            </div>
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500">Login</button>
        </form>
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
