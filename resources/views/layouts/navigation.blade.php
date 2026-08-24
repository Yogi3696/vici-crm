<nav class="navbar navbar-expand-md navbar-navy navbar-dark sticky-top">
    <div class="container-fluid px-4">
        <a class="navbar-brand" href="{{ route('dashboard') }}">
            <x-application-logo />
            <span>{{ config('app.name', 'ViciCRM') }}</span>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                data-bs-target="#primaryNav" aria-controls="primaryNav"
                aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="primaryNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
                       href="{{ route('dashboard') }}">
                        <i class="bi bi-speedometer2 me-1"></i>{{ __('Dashboard') }}
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('leads.*') ? 'active' : '' }}" href="#" id="leadsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-people me-1"></i>{{ __('Leads') }}
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="leadsDropdown">
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('leads.index') ? 'active' : '' }}" href="{{ route('leads.index') }}">{{ __('All Leads') }}</a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('leads.import.*') ? 'active' : '' }}" href="{{ route('leads.import.create') }}">{{ __('Upload Leads') }}</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('campaigns.*') || request()->routeIs('inbound-groups.*') ? 'active' : '' }}" href="#" id="campaignsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-megaphone me-1"></i>{{ __('Campaigns') }}
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="campaignsDropdown">
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('campaigns.*') ? 'active' : '' }}" href="{{ route('campaigns.index') }}">{{ __('Campaigns List') }}</a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('inbound-groups.*') ? 'active' : '' }}" href="{{ route('inbound-groups.index') }}">{{ __('Inbound Groups') }}</a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle {{ request()->routeIs('call-logs.*') ? 'active' : '' }}" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-file-earmark-bar-graph me-1"></i>{{ __('Reports') }}
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('call-logs.incoming') ? 'active' : '' }}" href="{{ route('call-logs.incoming') }}">{{ __('Incoming Call Logs') }}</a>
                        </li>
                        <li>
                            <a class="dropdown-item {{ request()->routeIs('call-logs.outgoing') ? 'active' : '' }}" href="{{ route('call-logs.outgoing') }}">{{ __('Outgoing Call Logs') }}</a>
                        </li>
                    </ul>
                </li>
            </ul>

            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="userMenu" role="button"
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-1"></i>{{ Auth::user()->name }}
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userMenu">
                        <li>
                            <span class="dropdown-item-text small text-muted">{{ Auth::user()->email }}</span>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person me-1"></i>{{ __('Profile') }}
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="bi bi-box-arrow-right me-1"></i>{{ __('Log Out') }}
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
