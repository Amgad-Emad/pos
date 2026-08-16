<aside class="sidenav-menu">

    <a href="{{ route('home') }}" class="sidebar-brand">
        <span class="brand-text">{{ __('messages.app_name') }}</span>
    </a>

    <button class="button-close-offcanvas btn btn-sm p-0 d-lg-none" type="button">
        <i data-lucide="x" class="align-middle"></i>
    </button>

    <div data-simplebar>
        <ul class="side-nav">

            <li class="side-nav-title">{{ __('messages.nav.main_menu') }}</li>

            @can('view-dashboard')
                <li class="side-nav-item">
                    <a href="{{ route('dashboard') }}" class="side-nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <span class="menu-icon"><i data-lucide="layout-dashboard"></i></span>
                        <span class="menu-text">{{ __('messages.nav.dashboard') }}</span>
                    </a>
                </li>
            @endcan

            @can('manage-sales')
                <li class="side-nav-item">
                    <a href="{{ route('sales.index') }}" class="side-nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i data-lucide="shopping-cart"></i></span>
                        <span class="menu-text">{{ __('messages.nav.sales') }}</span>
                    </a>
                </li>
            @endcan

            @can('manage-returns')
                <li class="side-nav-item">
                    <a href="{{ route('returns.index') }}" class="side-nav-link {{ request()->routeIs('returns.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i data-lucide="undo-2"></i></span>
                        <span class="menu-text">{{ __('messages.nav.returns') }}</span>
                    </a>
                </li>
            @endcan

            @can('view-inventory')
                <li class="side-nav-item">
                    <a href="{{ route('inventory.index') }}" class="side-nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i data-lucide="warehouse"></i></span>
                        <span class="menu-text">{{ __('messages.nav.inventory') }}</span>
                    </a>
                </li>
            @endcan

            @can('manage-products')
                <li class="side-nav-item">
                    <a href="{{ route('products.index') }}" class="side-nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i data-lucide="package"></i></span>
                        <span class="menu-text">{{ __('messages.nav.products') }}</span>
                    </a>
                </li>
            @endcan

            @can('manage-categories')
                <li class="side-nav-item">
                    <a href="{{ route('categories.index') }}" class="side-nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i data-lucide="folder-tree"></i></span>
                        <span class="menu-text">{{ __('messages.nav.categories') }}</span>
                    </a>
                </li>
            @endcan

            @can('manage-suppliers')
                <li class="side-nav-item">
                    <a href="{{ route('suppliers.index') }}" class="side-nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i data-lucide="truck"></i></span>
                        <span class="menu-text">{{ __('messages.nav.suppliers') }}</span>
                    </a>
                </li>
            @endcan

            @can('manage-purchases')
                <li class="side-nav-item">
                    <a href="{{ route('purchases.index') }}" class="side-nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i data-lucide="package-plus"></i></span>
                        <span class="menu-text">{{ __('messages.nav.purchases') }}</span>
                    </a>
                </li>
            @endcan

            @can('view-invoices')
                <li class="side-nav-item">
                    <a href="{{ route('invoices.index') }}" class="side-nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i data-lucide="receipt"></i></span>
                        <span class="menu-text">{{ __('messages.nav.invoices') }}</span>
                    </a>
                </li>
            @endcan

            @can('manage-users')
                <li class="side-nav-item">
                    <a href="{{ route('users.index') }}" class="side-nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i data-lucide="users"></i></span>
                        <span class="menu-text">{{ __('messages.nav.users') }}</span>
                    </a>
                </li>
            @endcan

            @can('manage-settings')
                <li class="side-nav-item">
                    <a href="{{ route('settings.edit') }}" class="side-nav-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i data-lucide="settings"></i></span>
                        <span class="menu-text">{{ __('messages.nav.settings') }}</span>
                    </a>
                </li>
            @endcan

            <li class="side-nav-item">
                <a href="{{ route('profile.edit') }}" class="side-nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                    <span class="menu-icon"><i data-lucide="key-round"></i></span>
                    <span class="menu-text">{{ __('messages.nav.change_password') }}</span>
                </a>
            </li>

        </ul>
    </div>
</aside>
