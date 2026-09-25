<div class="member-layout container">
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
