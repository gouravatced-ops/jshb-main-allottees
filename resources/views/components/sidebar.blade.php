<!-- SIDEBAR OVERLAY -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
@php
    $sidebarUser = auth()->user();
    $sidebarInitials = 'U';
    if ($sidebarUser && !empty($sidebarUser->name)) {
        $sidebarNameParts = preg_split('/\s+/', trim($sidebarUser->name));
        $sidebarInitials = strtoupper(($sidebarNameParts[0][0] ?? 'U') . ($sidebarNameParts[1][0] ?? ''));
    }
    
    $allottee = \App\Models\Allottee::where('user_id', auth()->id())->first();
    $steps = collect();
    $paymentOption = null;
    if ($allottee) {
        $steps = $allottee->processSteps()->orderBy('step_no')->get();
        $paymentOption = $allottee->payment_option;
    }
    
    $currentStepNo = request()->query('step', 1);
@endphp

<aside id="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon"><img src="{{ asset(config('panel.logo')) }}" alt="JESA Logo"></div>
        <div class="logo-text">
            <div class="logo-title"><span style="color:#f5c518;">J</span>SHB</div>
        </div>
    </div>

    <div class="sidebar-section-label">Main Menu</div>

    <!-- Static Menus -->
    <div class="nav-item-wrap">
        <a class="nav-link-custom {{ request()->routeIs('dashboard') || (request()->routeIs('dashboard.section') && request()->route('blade') === 'overview') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <div class="nav-icon"><i class="fa-solid fa-house"></i></div>
            <span class="nav-text">Dashboard</span>
        </a>
    </div>
    
    <div class="nav-item-wrap">
        <a class="nav-link-custom {{ request()->is('application') ? 'active' : '' }}" href="#">
            <div class="nav-icon"><i class="fa-solid fa-file-lines"></i></div>
            <span class="nav-text">Application</span>
        </a>
    </div>

    <div class="nav-item-wrap">
        <a class="nav-link-custom {{ request()->is('notices') ? 'active' : '' }}" href="{{ route('dashboard.section', ['blade' => 'notices']) }}">
            <div class="nav-icon"><i class="fa-solid fa-bullhorn"></i></div>
            <span class="nav-text">Notices</span>
        </a>
    </div>

    @if($steps->isNotEmpty())
        <div class="sidebar-section-label mt-3">Allottee Process</div>
        <!-- Dynamic Process Steps -->
        @foreach ($steps->groupBy('menu_key') as $menuKey => $menuSteps)
            @php
                $menu = $menuSteps->first();

                // MENU VISIBILITY CONDITIONS
                if ($menuKey === 'choose-payment-option' && !is_null($paymentOption)) continue;
                if ($menuKey === 'allotment-cancellation' && !is_null($paymentOption)) continue;
                if ($menuKey === 'property-payment' && $paymentOption !== 'one_time') continue;
                if ($menuKey === 'emi-management' && $paymentOption !== 'emi') continue;
                if ($menuKey === 'final-calculation' && $paymentOption !== 'emi') continue;

                $hasSubmenus = $menuSteps->whereNotNull('sub_menu_key')->count() > 0;
                $collapseId = 'menu-' . Str::slug($menuKey);
                
                $menuCompleted = $menuSteps->every(fn($step) => $step->status === 'completed');
                $menuPending = $menuSteps->contains(fn($step) => $step->status === 'pending');
                $menuLocked = $menuSteps->every(fn($step) => $step->status === 'locked');
                $menuTitle = str($menu->menu_key)->replace('-', ' ')->title();
            @endphp

            @if ($hasSubmenus)
                <div class="nav-item-wrap">
                    <div class="nav-link-custom" onclick="toggleSubmenu('{{ $collapseId }}', this)">
                        <div class="nav-icon">
                            <i class="{{ $menu->icons }}"></i>
                        </div>
                        <span class="nav-text">{{ $menuTitle }}</span>
                        <div class="ms-auto d-flex align-items-center gap-2">
                            @if ($menuCompleted)
                                <i class="fa-solid fa-circle-check text-success" style="font-size: 12px;"></i>
                            @elseif($menuPending)
                                <i class="fa-solid fa-clock text-warning" style="font-size: 12px;"></i>
                            @elseif($menuLocked)
                                <i class="fa-solid fa-lock text-muted" style="font-size: 12px;"></i>
                            @endif
                            <i class="fa-solid fa-chevron-right nav-chevron" id="{{ $collapseId }}-chev"></i>
                        </div>
                    </div>
                    
                    <div class="submenu" id="{{ $collapseId }}">
                        @foreach ($menuSteps as $step)
                            @php
                                $isActive = request()->routeIs('dashboard.section') && request()->route('blade') === $step->blade;
                                $isLocked = $step->status === 'locked';
                                $isCompleted = $step->status === 'completed';
                                $isPending = $step->status === 'pending';
                                $href = $isLocked ? 'javascript:void(0)' : route('dashboard.section', ['blade' => $step->blade]);
                            @endphp
                            
                            <a class="submenu-item {{ $isActive ? 'active' : '' }}" 
                               href="{{ $href }}" 
                               style="{{ $isLocked ? 'opacity: 0.5; cursor: not-allowed;' : '' }}">
                               
                                @if ($isCompleted)
                                    <i class="fa-solid fa-circle-check text-success me-2"></i>
                                @elseif($isPending)
                                    <i class="fa-solid fa-clock text-warning me-2"></i>
                                @elseif($isLocked)
                                    <i class="fa-solid fa-lock text-muted me-2"></i>
                                @else
                                    <i class="fa-solid fa-circle text-muted me-2" style="font-size: 6px;"></i>
                                @endif
                                
                                {{ $step->title }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @else
                @php
                    $step = $menuSteps->first();
                    $isActive = request()->routeIs('dashboard.section') && request()->route('blade') === $step->blade;
                    $isLocked = $step->status === 'locked';
                    $isCompleted = $step->status === 'completed';
                    $isPending = $step->status === 'pending';
                    $href = $isLocked ? 'javascript:void(0)' : route('dashboard.section', ['blade' => $step->blade]);
                @endphp
                
                <div class="nav-item-wrap">
                    <a class="nav-link-custom {{ $isActive ? 'active' : '' }}" 
                       href="{{ $href }}"
                       style="{{ $isLocked ? 'opacity: 0.5; cursor: not-allowed;' : '' }}">
                        <div class="nav-icon"><i class="{{ $menu->icons }}"></i></div>
                        <span class="nav-text">{{ $menuTitle }}</span>
                        
                        <div class="ms-auto">
                            @if ($isCompleted)
                                <i class="fa-solid fa-circle-check text-success" style="font-size: 12px;"></i>
                            @elseif($isPending)
                                <i class="fa-solid fa-clock text-warning" style="font-size: 12px;"></i>
                            @elseif($isLocked)
                                <i class="fa-solid fa-lock text-muted" style="font-size: 12px;"></i>
                            @endif
                        </div>
                    </a>
                </div>
            @endif
        @endforeach
    @endif

    <div class="sidebar-section-label mt-3">Account</div>

    <!-- Common Settings and Footer for all roles -->
    <div class="nav-item-wrap">

        <div class="nav-link-custom" onclick="toggleSubmenu('settingsub',this)">
            <div class="nav-icon">
                <i class="fa-solid fa-sliders"></i>
            </div>
            <span class="nav-text">Settings</span>
            <i class="fa-solid fa-chevron-right nav-chevron" id="settingsub-chev"></i>
        </div>

        <div class="submenu" id="settingsub">
            <!-- Change Password -->
            <a class="submenu-item" href="javascript:void(0)" onclick="openPasswordResetModal(event); return false;">
                <i class="fa-solid fa-key"></i> Change Password
            </a>

            <!-- Logout -->
            <a class="submenu-item" href="{{ route('logout') }}">
                <i class="fa-solid fa-right-from-bracket"></i> Sign Out
            </a>
        </div>
    </div>

    <div class="nav-item-wrap">
        <a class="nav-link-custom" href="#" onclick="activateLockScreen(); return false;">
            <div class="nav-icon"><i class="fa-solid fa-lock"></i></div>
            <span class="nav-text">Lock Screen</span>
        </a>
    </div>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar">
                @if ($sidebarUser && $sidebarUser->photo)
                    <img src="{{ asset('storage/photos/' . $sidebarUser->photo) }}" alt="Profile Photo"
                        style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit;">
                @else
                    {{ $sidebarInitials }}
                @endif
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name">{{ $sidebarUser->name ?? 'Guest User' }}</div>
                <span class="sidebar-user-name">{{ $sidebarUser->email ?? 'guest@domain.com' }}</span>
            </div>
        </div>
    </div>
</aside>
