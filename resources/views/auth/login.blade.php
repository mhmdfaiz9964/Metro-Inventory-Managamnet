<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="POS - Bootstrap Admin Template">
    <meta name="author" content="APEX WEB INNOVATIONS">
    <meta name="robots" content="noindex, nofollow">
    <title>Login - APEX Inventory Management System</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/img/favicon.png') }}">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">

    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/fontawesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome/css/all.min.css') }}">

    <!-- Main CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>

  <body style="background-color: #ffffff; margin: 0; padding: 0; font-family: Arial, sans-serif;">

    <div id="global-loader">
        <div class="whirly-loader"></div>
    </div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <div class="account-content">
            <div class="login-wrapper login-new">
                <div class="container">
                                {{-- Animation Image Above Card --}}
            <div class="d-flex justify-content-center mb-4">
                <div style="width:200px; height:200px;">
                    <img src="https://assets-v2.lottiefiles.com/a/59ae3046-117b-11ee-88a7-ef3838e9662f/r8HuxylbzH.gif" alt="Profile Animation"
                        class="w-100 h-100 object-fit-cover">
                </div>
            </div>

                    <div class="login-content user-login">
                        <div class="login-logo">
                            <img src="{{ asset('assets/img/logo-new.png') }}" alt="img">
                            <a href="{{ url('/') }}" class="login-logo logo-white">
                                <img src="{{ asset('assets/img/logo-new.png') }}" alt="">
                            </a>
                        </div>

                        {{--  Laravel Login Form --}}
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="login-userset">
                                <div class="login-userheading">
                                    <h3>Sign In</h3>
                                    <h4>Access the APEX Inventory Management System using your email and password.</h4>
                                </div>

                                {{-- Email --}}
                                <div class="form-login">
                                    <label class="form-label">Email Address</label>
                                    <div class="form-addons">
                                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                                        <img src="{{ asset('assets/img/icons/mail.svg') }}" alt="img">
                                    </div>
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Password --}}
                                <div class="form-login">
                                    <label>Password</label>
                                    <div class="pass-group">
                                        <input type="password" name="password" class="pass-input" required>
                                        <span class="fas toggle-password fa-eye-slash"></span>
                                    </div>
                                    @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Remember Me + Forgot --}}
                                <div class="form-login authentication-check">
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="checkboxs ps-4 mb-0 pb-0 line-height-1">
                                                <input type="checkbox" name="remember">
                                                <span class="checkmarks"></span> Remember me
                                            </label>
                                        </div>
                                        <div class="col-6 text-end">
                                            <a class="forgot-link" href="{{ route('password.request') }}">Forgot Password?</a>
                                        </div>
                                    </div>
                                </div>

                                {{-- Submit --}}
                                <div class="form-login">
                                    <button class="btn btn-login" type="submit">Sign In</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="my-4 d-flex justify-content-center align-items-center copyright-text">
                        <p>Copyright &copy; 2025 APEX WEB INNOVATIONS. All rights reserved</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Main Wrapper -->

    <!-- Scripts -->
    <script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/feather.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme-script.js') }}"></script>
    <script src="{{ asset('assets/js/script.js') }}"></script>
</body>
</html>
