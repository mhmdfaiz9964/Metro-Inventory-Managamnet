<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="POS - Bootstrap Admin Template">
    <meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern, html5, responsive">
    <meta name="author" content="Dreamguys - Bootstrap Admin Template">
    <meta name="robots" content="noindex, nofollow">
    <title>APEX - Inventory Management System</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

    <!-- Animation CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}">

    <!-- Datatable CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <!-- Additional CSS -->
    @stack('styles')
</head>
<body>
    <div id="global-loader">
        <div class="whirly-loader"></div>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Header -->
        @include('layouts.header')
        <!-- /Header -->

        <!-- Sidebar -->
        @include('layouts.sidebar')
        <!-- /Sidebar -->

        <div class="page-wrapper pagehead">
            <div class="content">
                @yield('content')
            </div>
        </div>
        
        
    </div>
    <!-- /Main Wrapper -->
<!-- Floating Box Button -->
   <div class="floating-button-container">
       <button id="myFloatingButton" title="Go Back" onclick="window.history.back()">
    <i data-feather="arrow-left" class="icon"></i>
    Back
  </button>
   </div>

<style>
   .floating-button-container {
     position: fixed; /* Makes the button float */
     left: 300px; /* Adjust as needed for desired left margin */
     top: 90%; /* Centers vertically, adjust if needed */
     transform: translateY(-50%); /* Fine-tunes vertical centering */
     z-index: 1000; /* Ensures it stays above other content */
   }

   #myFloatingButton {
     /* Add your desired button styles here (background, color, padding, border-radius, etc.) */
    background-color: #00008B;
     color: white;
     padding: 15px 20px;
     border: none;
     border-radius: 5px;
     cursor: pointer;
     box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
   }
</style>
    {{-- <div class="customizer-links" id="setdata">
        <ul class="sticky-sidebar">
            <li class="sidebar-icons">
                <a href="#" class="navigation-add" data-bs-toggle="tooltip" data-bs-placement="left"
                    data-bs-original-title="Theme">
                    <i data-feather="settings" class="feather-five"></i>
                </a>
            </li>
        </ul>
    </div> --}}

    <!-- jQuery -->
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>

    <!-- Feather Icon JS -->
    <script src="{{ asset('assets/js/feather.min.js') }}"></script>

    <!-- Slimscroll JS -->
    <script src="{{ asset('assets/js/jquery.slimscroll.min.js') }}"></script>

    <!-- Bootstrap Core JS -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Custom JS -->
    <script src="{{ asset('assets/js/theme-script.js') }}"></script>
    <script src="{{ asset('assets/js/script.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if(session('success'))
        Swal.fire({
            html: `
                <div class="d-flex">
                    <div class="bg-success text-white d-flex align-items-center justify-content-center p-3">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                    <div class="p-3 flex-grow-1">
                        <strong>Success</strong>
                        <div>{{ session('success') }}</div>
                    </div>
                </div>
            `,
            toast: true,
            position: 'top-end',
            background: '#ffffff',
            showConfirmButton: false,
            showCloseButton: true,
            timer: 3000,
            timerProgressBar: true
        });
        @endif

        @if(session('error'))
        Swal.fire({
            html: `
                <div class="d-flex">
                    <div class="bg-danger text-white d-flex align-items-center justify-content-center p-3">
                        <i class="bi bi-x-circle-fill fs-4"></i>
                    </div>
                    <div class="p-3 flex-grow-1">
                        <strong>Error</strong>
                        <div>{{ session('error') }}</div>
                    </div>
                </div>
            `,
            toast: true,
            position: 'top-end',
            background: '#ffffff',
            showConfirmButton: false,
            showCloseButton: true,
            timer: 3000,
            timerProgressBar: true
        });
        @endif
    </script>
    <!-- Additional Scripts -->
    @yield('scripts')
    @stack('scripts')
    @stack('styles')
</body>
</html>