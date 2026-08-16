<header class="app-topbar">
    <div class="container-fluid topbar-menu d-flex align-items-center justify-content-between gap-2" style="min-height: var(--ins-topbar-height);">

        <div class="d-flex align-items-center gap-2">
            <button class="button-collapse-toggle btn btn-icon btn-ghost-light text-body" type="button" aria-label="{{ __('messages.nav.main_menu') }}">
                <i data-lucide="menu" style="width:22px;height:22px;"></i>
            </button>
            <span class="topbar-date d-none d-md-inline-flex align-items-center gap-1">
                <i data-lucide="calendar-days" style="width:14px;height:14px;"></i>
                {{ now()->translatedFormat('l، j F Y') }}
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            @can('manage-sales')
                <a href="{{ route('sales.create') }}" class="btn btn-primary btn-sm d-none d-sm-inline-flex align-items-center gap-1">
                    <i data-lucide="plus" style="width:15px;height:15px;"></i>
                    {{ __('messages.sales.create') }}
                </a>
            @endcan

            <div class="dropdown">
                <button class="user-chip border-0 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <span class="user-avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                    <span class="user-meta d-none d-sm-flex">
                        <span class="fw-semibold">{{ auth()->user()->name }}</span>
                        <span class="user-role">{{ auth()->user()->isAdmin() ? __('messages.roles.admin') : __('messages.roles.seller') }}</span>
                    </span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('profile.edit') }}">
                            <i data-lucide="key-round" style="width:16px;height:16px;"></i>
                            {{ __('messages.nav.change_password') }}
                        </a>
                    </li>
                    @can('manage-settings')
                        <li>
                            <a class="dropdown-item d-flex align-items-center gap-2" href="{{ route('settings.edit') }}">
                                <i data-lucide="settings" style="width:16px;height:16px;"></i>
                                {{ __('messages.nav.settings') }}
                            </a>
                        </li>
                    @endcan
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger d-flex align-items-center gap-2">
                                <i data-lucide="log-out" style="width:16px;height:16px;"></i>
                                {{ __('messages.nav.logout') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
