        <div class="header">
            <!-- Logo -->
            <div class="header-left active">
                {{-- <a href="{{ url('/') }}" class="logo logo-normal">
                    <img src="{{ asset('assets/img/logo-new.png') }}" alt="">
                </a>
                <a href="{{ url('/') }}" class="logo logo-white">
                    <img src="{{ asset('assets/img/logo-new.png') }}" alt="">
                </a>
                <a href="{{ url('/') }}" class="logo-small">
                    <img src="{{ asset('assets/img/logo-new.png') }}" alt="">
                </a> --}}
                <a id="toggle_btn" href="javascript:void(0);">
                    <i data-feather="chevrons-left" class="feather-16"></i>
                </a>
            </div>
            <!-- /Logo -->

            <a id="mobile_btn" class="mobile_btn" href="#sidebar">
                <span class="bar-icon">
                    <span></span>
                    <span></span>
                    <span Scientifica></span>
            </a>

            <!-- Header Menu -->
            <ul class="nav user-menu">
                <!-- Search -->
                <li class="nav-item nav-searchinputs">

                </li>
                <!-- /Search -->

                <li class="nav-item nav-item-box">
                    <a href="javascript:void(0);" id="btnFullscreen">
                        <i data-feather="maximize"></i>
                    </a>
                </li>
                <!-- Notifications -->
                <li class="nav-item dropdown nav-item-box">
                    <a href="javascript:void(0);" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
                        <i data-feather="bell"></i><span class="badge rounded-pill">2</span>
                    </a>
                    <div class="dropdown-menu notifications">
                        <div class="topnav-dropdown-header">
                            <span class="notification-title">Notifications</span>
                            <a href="javascript:void(0)" class="clear-noti"> Clear All </a>
                        </div>
                        <div class="noti-content">
                            <ul class="notification-list">
                                <li class="notification-message">
                                    <a href="{{ url('activities') }}">
                                        <div class="media d-flex">
                                            <span class="avatar flex-shrink-0">
                                                <img alt=""
                                                    src="{{ asset('assets/img/profiles/avatar-02.jpg') }}">
                                            </span>
                                            <div class="media-body flex-grow-1">
                                                <p class="noti-details"><span class="noti-title">John Doe</span> added
                                                    new task <span class="noti-title">Patient appointment booking</span>
                                                </p>
                                                <p class="noti-time"><span class="notification-time">4 mins ago</span>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li class="notification-message">
                                    <a href="{{ url('activities') }}">
                                        <div class="media d-flex">
                                            <span class="avatar flex-shrink-0">
                                                <img alt=""
                                                    src="{{ asset('assets/img/profiles/avatar-03.jpg') }}">
                                            </span>
                                            <div class="media-body flex-grow-1">
                                                <p class="noti-details"><span class="noti-title">Tarah Shropshire</span>
                                                    changed the task name <span class="noti-title">Appointment booking
                                                        with payment gateway</span></p>
                                                <p class="noti-time"><span class="notification-time">6 mins ago</span>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li class="notification-message">
                                    <a href="{{ url('activities') }}">
                                        <div class="media d-flex">
                                            <span class="avatar flex-shrink-0">
                                                <img alt=""
                                                    src="{{ asset('assets/img/profiles/avatar-06.jpg') }}">
                                            </span>
                                            <div class="media-body flex-grow-1">
                                                <p class="noti-details"><span class="noti-title">Misty Tison</span>
                                                    added <span class="noti-title">Domenic Houston</span> and <span
                                                        class="noti-title">Claire Mapes</span> to project <span
                                                        class="noti-title">Doctor available module</span></p>
                                                <p class="noti-time"><span class="notification-time">8 mins ago</span>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li class="notification-message">
                                    <a href="{{ url('activities') }}">
                                        <div class="media d-flex">
                                            <span class="avatar flex-shrink-0">
                                                <img alt=""
                                                    src="{{ asset('assets/img/profiles/avatar-17.jpg') }}">
                                            </span>
                                            <div class="media-body flex-grow-1">
                                                <p class="noti-details"><span class="noti-title">Rolland Webber</span>
                                                    completed task <span class="noti-title">Patient and Doctor video
                                                        conferencing</span></p>
                                                <p class="noti-time"><span class="notification-time">12 mins ago</span>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li class="notification-message">
                                    <a href="{{ url('activities') }}">
                                        <div class="media d-flex">
                                            <span class="avatar flex-shrink-0">
                                                <img alt=""
                                                    src="{{ asset('assets/img/profiles/avatar-13.jpg') }}">
                                            </span>
                                            <div class="media-body flex-grow-1">
                                                <p class="noti-details"><span class="noti-title">Bernardo Galaviz</span>
                                                    added new task <span class="noti-title">Private chat module</span>
                                                </p>
                                                <p class="noti-time"><span class="notification-time">2 days ago</span>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="topnav-dropdown-footer">
                            <a href="{{ url('activities') }}">View all Notifications</a>
                        </div>
                    </div>
                </li>
                <!-- /Notifications -->

                <li class="nav-item dropdown has-arrow main-drop">
                    <a href="javascript:void(0);" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
                        <span class="user-info">
                            <span class="user-letter">
                                <img src="{{ asset('assets/img/user.webp') }}" alt="" class="img-fluid">
                            </span>
                            <span class="user-detail">
                                @auth
                                    <span class="user-name">{{ auth()->user()->name }}</span>
                                    <span class="user-role">{{ auth()->user()->getRoleNames()->first() }}</span>
                                @endauth
                            </span>
                        </span>
                    </a>
                    <div class="dropdown-menu menu-drop-user">
                        <div class="profilename">
                            <div class="profileset">
                                <span class="user-img"><img src="{{ asset('assets/img/user.webp') }}"
                                        alt="">
                                    <span class="status online"></span></span>
                                <div class="profilesets">
                                    @auth
                                        <span class="user-name">{{ auth()->user()->name }}</span>
                                        <span class="user-role">{{ auth()->user()->getRoleNames()->first() }}</span>
                                    @endauth
                                </div>
                            </div>
                            <hr class="m-0">
                            <a class="dropdown-item" href="{{ route('profile.show') }}"> <i class="me-2"
                                    data-feather="user"></i> My
                                Profile</a>
                            <a class="dropdown-item" href="{{ url('general-settings') }}"><i class="me-2"
                                    data-feather="settings"></i>Settings</a>
                            <hr class="m-0">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="dropdown-item logout pb-0">
                                    <img src="{{ asset('assets/img/icons/log-out.svg') }}" class="me-2"
                                        alt="img"> Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </li>
            </ul>
            <!-- /Header Menu -->

            <!-- Mobile Menu -->
            <div class="dropdown mobile-user-menu">
                <a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown"
                    aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item" href="{{ url('profile') }}">My Profile</a>
                    <a class="dropdown-item" href="{{ url('general-settings') }}">Settings</a>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="dropdown-item logout pb-0">
                            <img src="{{ asset('assets/img/icons/log-out.svg') }}" class="me-2" alt="img">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
            <!-- /Mobile Menu -->
        </div>
