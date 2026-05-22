<aside class="sidebar bg-dark text-white" id="sidebar">
    <div class="sidebar-header p-3 border-bottom border-secondary">
        <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-white d-flex align-items-center">
            <img src="{{ app_logo() }}" alt="Logo" height="32" class="me-2" onerror="this.style.display='none'">
            <span class="fw-bold fs-5">{{ app_name() }}</span>
        </a>
    </div>
    <nav class="sidebar-nav p-2">
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.dashboard') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a></li>
            <li class="nav-item mt-2"><small class="text-muted px-3 text-uppercase">Lead Management</small></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.leads.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.leads.index') }}"><i class="bi bi-people me-2"></i>Leads</a></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.pipeline.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.pipeline.index') }}"><i class="bi bi-kanban me-2"></i>Pipeline</a></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.followups.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.followups.index') }}"><i class="bi bi-telephone me-2"></i>Follow-Ups</a></li>
            <li class="nav-item mt-2"><small class="text-muted px-3 text-uppercase">Sales</small></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.clients.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.clients.index') }}"><i class="bi bi-building me-2"></i>Clients</a></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.quotations.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.quotations.index') }}"><i class="bi bi-file-earmark-text me-2"></i>Quotations</a></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.products.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.products.index') }}"><i class="bi bi-box me-2"></i>Products</a></li>
            <li class="nav-item mt-2"><small class="text-muted px-3 text-uppercase">Communication</small></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.email.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.email.inbox') }}"><i class="bi bi-envelope me-2"></i>Email</a></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.whatsapp.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.whatsapp.index') }}"><i class="bi bi-whatsapp me-2"></i>WhatsApp</a></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.campaigns.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.campaigns.index') }}"><i class="bi bi-megaphone me-2"></i>Campaigns</a></li>
            <li class="nav-item mt-2"><small class="text-muted px-3 text-uppercase">Tools</small></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.tasks.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.tasks.index') }}"><i class="bi bi-check2-square me-2"></i>Tasks</a></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.calendar.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.calendar.index') }}"><i class="bi bi-calendar3 me-2"></i>Calendar</a></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.google-sheets.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.google-sheets.index') }}"><i class="bi bi-table me-2"></i>Google Sheets</a></li>
            <li class="nav-item mt-2"><small class="text-muted px-3 text-uppercase">Analytics</small></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.reports.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.reports.index') }}"><i class="bi bi-graph-up me-2"></i>Reports</a></li>
            @role('super-admin')
            <li class="nav-item mt-2"><small class="text-muted px-3 text-uppercase">Administration</small></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.team.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.team.index') }}"><i class="bi bi-people-fill me-2"></i>Team</a></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.users.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-person-gear me-2"></i>Users</a></li>
            <li class="nav-item"><a class="nav-link text-white {{ request()->routeIs('admin.settings.*') ? 'active bg-primary rounded' : '' }}" href="{{ route('admin.settings.index') }}"><i class="bi bi-gear me-2"></i>Settings</a></li>
            @endrole
        </ul>
    </nav>
</aside>
