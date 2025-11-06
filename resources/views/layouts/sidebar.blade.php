<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul class="p-0 m-0">

                <!-- Dashboard -->
                <li class="submenu-open">
                    <a href="{{ route('admin.dashboard') }}"
                        class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i data-feather="grid" class="sidebar-icon"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Users -->
                <li class="submenu-open">
                    <a href="{{ route('admin.users.menu') }}"
                        class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i data-feather="users" class="sidebar-icon"></i>
                        <span>Users</span>
                    </a>
                </li>
                <!-- Suppliers -->
                <li class="submenu-open">
                    <a href="{{ route('admin.suppliers.menu') }}"
                        class="sidebar-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <i data-feather="users" class="sidebar-icon"></i>
                        <span>Suppliers</span>
                    </a>
                </li>
                <!--Brands-->
                <li class="submenu-open">
                    <a href="{{ route('admin.brands.menu') }}"
                        class="sidebar-link {{ request()->routeIs('brands.*') ? 'active' : '' }}">
                        <i data-feather="tag" class="sidebar-icon"></i>
                        <span>Brands</span>
                    </a>
                </li>
                <!-- Products -->
                <li class="submenu-open">
                    <a href="{{ route('admin.products.menu') }}"
                        class="sidebar-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}">
                        <i data-feather="cpu" class="sidebar-icon"></i>
                        <span>Products</span>
                    </a>
                </li>
                <!-- Purchase -->
                <li class="submenu-open">
                    <a href="{{ route('admin.purchase.menu') }}"
                        class="sidebar-link {{ request()->routeIs('admin.purchase.*') ? 'active' : '' }}">
                        <i data-feather="shopping-bag" class="sidebar-icon"></i>
                        <span>Purchase</span>
                    </a>
                </li>

                <!-- TOG -->
                <li class="submenu-open">
                    <a href="{{ route('admin.tog.menu') }}"
                        class="sidebar-link {{ request()->routeIs('tog.menu') ? 'active' : '' }}">
                        <i data-feather="repeat" class="sidebar-icon"></i>
                        <span>TOG</span>
                    </a>
                </li>

                <!-- Inventory -->
                <li class="submenu-open">
                    <a href="{{ route('admin.inventory.menu') }}"
                        class="sidebar-link {{ request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
                        <i data-feather="archive" class="sidebar-icon"></i>
                        <span>Inventory</span>
                    </a>
                </li>

                <!-- BOM -->
                <li class="submenu-open">
                    <a href="{{ route('admin.bom.menu') }}"
                        class="sidebar-link {{ request()->routeIs('admin.bom.*') ? 'active' : '' }}">
                        <i data-feather="package" class="sidebar-icon"></i>
                        <span>BOM</span>
                    </a>
                </li>

                <!-- Customers -->
                <li class="submenu-open">
                    <a href="{{ route('admin.customers.menu') }}"
                        class="sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">
                        <i data-feather="users" class="sidebar-icon"></i>
                        <span>Customers</span>
                    </a>
                </li>

                <!-- Sales -->
                <li class="submenu-open">
                    <a href="{{ route('admin.sales.menu') }}"
                        class="sidebar-link {{ request()->routeIs('admin.sales.*') ? 'active' : '' }}">
                        <i data-feather="shopping-cart" class="sidebar-icon"></i>
                        <span>Sales</span>
                    </a>
                </li>

                <!-- Expenses -->
                <li class="submenu-open">
                    <a href="{{ route('admin.expenses.index') }}"
                        class="sidebar-link {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}">
                        <i data-feather="credit-card" class="sidebar-icon"></i>
                        <span>Expenses</span>
                    </a>
                </li>

                <!-- Accounts -->
                <li class="submenu-open">
                    <a href="{{ route('admin.accounts.menu') }}"
                        class="sidebar-link {{ request()->routeIs('admin.accounts.*') ? 'active' : '' }}">
                        <i data-feather="briefcase" class="sidebar-icon"></i>
                        <span>Accounts</span>
                    </a>
                </li>

                <!-- Reports -->
                <li class="submenu-open">
                    <a href="{{ route('admin.reports.menu') }}"
                        class="sidebar-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                        <i data-feather="bar-chart-2" class="sidebar-icon"></i>
                        <span>Reports</span>
                    </a>
                </li>

            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->


@push('styles')
    <style>
        /* Sidebar background */
        .sidebar-inner.slimscroll {
            background-color: #1e0041;
            min-height: 100vh;
            padding-top: 10px;
        }

        .sidebar .sidebar-menu>ul>li>a.active span,
        .sidebars .sidebar-menu>ul>li>a.active span {
            color: #340965;
        }

        .sidebar .sidebar-menu>ul>li>a:hover span,
        .sidebars .sidebar-menu>ul>li>a:hover span {
            color: #340965;
        }

        /* Menu items */
        .sidebar .sidebar-menu>ul>li {
            margin: 4px 0;
        }

        .sidebar .sidebar-menu>ul>li>a {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            border-radius: 6px;
            color: #ffffff;
            text-decoration: none;
            font-size: 15px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .sidebar .sidebar-menu>ul>li>a span {
            margin-left: 12px;
            font-size: 15px;
            font-weight: 500;
            color: #ffffff;
        }

        /* Icons */
        .sidebar .sidebar-menu>ul>li>a svg {
            width: 20px;
            height: 20px;
            color: #ffffff;
            flex-shrink: 0;
        }

        /* Hover & Active */
        .sidebar .sidebar-menu>ul>li>a:hover,
        .sidebar .sidebar-menu>ul>li>a.active {
            background-color: #ffffff;
            color: #340965;
        }

        .sidebar .sidebar-menu>ul>li>a:hover svg,
        .sidebar .sidebar-menu>ul>li>a.active svg {
            color: #340965;
        }

        .sidebar .sidebar-menu .submenu-hdr {
            color: #ffffff;
            font-size: 13px;
            text-transform: uppercase;
            padding: 10px 16px;
            margin-top: 10px;
            display: block;
            letter-spacing: 0.5px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        // Activate feather icons
        feather.replace();
    </script>
@endpush
