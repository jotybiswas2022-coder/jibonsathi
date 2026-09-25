<div class="member-layout container">
    {{-- The sidebar is a drawer below 992px, so every section is one tap away. --}}
    <div class="member-bar">
        <button type="button" class="member-bar-toggle" data-sidebar-toggle
                aria-label="Open section menu" aria-expanded="false" aria-controls="memberSidebar">
            <i class="fas fa-ellipsis-vertical"></i>
        </button>
    </div>

    <aside class="sidebar" id="memberSidebar">
        <div class="sidebar-head">
            <span class="sidebar-head-title">Menu</span>
            <button type="button" class="sidebar-head-close" data-sidebar-close aria-label="Close section menu">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        @include('frontend.components.sidebar')
    </aside>
    <div class="member-content">
        @yield('member-content')
    </div>
</div>
