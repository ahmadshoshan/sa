<!doctype html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('/') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'نظام المبيعات والحسابات')</title>

    <link href="{{ asset('assets/css/bootstrap.rtl.min.css') }}" rel="stylesheet">

    <script defer src="{{ asset('assets/js/alpine.min.js') }}"></script>

    <script>
        (function () {
            const theme = localStorage.getItem('theme') || 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
        })();
    </script>

    <style>
        body {
            font-family: Tahoma, Arial, sans-serif;
        }

        .app-wrapper {
            display: flex;
            min-height: 100vh;
        }

        .app-sidebar {
            width: 275px;
            background: linear-gradient(180deg, #0f172a, #111827);
            color: #e5e7eb;
            z-index: 1045;
        }

        [data-bs-theme="dark"] .app-sidebar {
            background: linear-gradient(180deg, #020617, #0f172a);
        }

        .sidebar-brand {
            padding: 1.1rem 1rem;
            font-weight: 800;
            font-size: 1.05rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .sidebar-section {
            padding: 0.9rem 1rem 0.35rem;
            font-size: 0.72rem;
            letter-spacing: 0.4px;
            color: #94a3b8;
            text-transform: uppercase;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            padding: 0.58rem 1rem;
            color: #cbd5e1;
            text-decoration: none;
            border-inline-start: 3px solid transparent;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
        }

        .sidebar-link.active {
            background: rgba(59, 130, 246, 0.16);
            color: #fff;
            border-inline-start-color: #3b82f6;
        }

        .app-main {
            flex: 1;
            min-width: 0;
        }

        .topbar {
            background: var(--bs-body-bg);
            border-bottom: 1px solid var(--bs-border-color);
            z-index: 1030;
        }

        .card {
            border: 0;
            border-radius: 14px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        .btn,
        .form-control,
        .form-select {
            border-radius: 10px;
        }

        .dropdown-menu {
            border-radius: 14px;
        }

        .notifications-menu {
            min-width: 320px;
            max-height: 340px;
            overflow-y: auto;
        }

        @media (min-width: 992px) {
            .app-sidebar {
                position: fixed;
                top: 0;
                bottom: 0;
                inset-inline-start: 0;
                overflow-y: auto;
                transform: none !important;
                visibility: visible !important;
            }

            .app-main {
                margin-inline-start: 275px;
            }
        }

        @media (max-width: 991.98px) {
            .app-main {
                margin-inline-start: 0;
            }
        }

        @media (max-width: 768px) {
            .table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }
        }
    </style>

    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <style>
        :root { --accent: #2563eb; --accent-rgb: 37,99,235; }
        [data-accent="green"]  { --accent: #16a34a; --accent-rgb: 22,163,74; }
        [data-accent="purple"] { --accent: #8b5cf6; --accent-rgb: 139,92,246; }
        [data-accent="red"]    { --accent: #dc2626; --accent-rgb: 220,38,38; }
        [data-accent="orange"] { --accent: #ea580c; --accent-rgb: 234,88,12; }
        [data-accent="teal"]   { --accent: #0d9488; --accent-rgb: 13,148,136; }

        .btn-primary { background: var(--accent); border-color: var(--accent); }
        .btn-outline-primary { color: var(--accent); border-color: var(--accent); }
        .btn-outline-primary:hover { background: var(--accent); color: #fff; }
        .text-primary { color: var(--accent) !important; }
        .bg-primary { background: var(--accent) !important; }
        .sidebar-link.active { border-inline-start-color: var(--accent); background: rgba(var(--accent-rgb), .16); }
        .form-control:focus, .form-select:focus { border-color: var(--accent); box-shadow: 0 0 0 .2rem rgba(var(--accent-rgb), .15); }

        .accent-picker {
            position: fixed;
            bottom: 90px;
            left: 20px;
            display: flex;
            flex-direction: column;
            gap: 6px;
            z-index: 1059;
        }

        .accent-dot {
            width: 22px; height: 22px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 2px 6px rgba(0,0,0,.3);
            cursor: pointer;
        }
    </style>
    
    <style>
        .sidebar-group { margin-bottom: 2px; }

        .sidebar-group-toggle {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 11px 16px;
            background: transparent;
            border: none;
            border-inline-start: 3px solid transparent;
            color: #e2e8f0;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            text-align: right;
            transition: background .15s;
        }

        .sidebar-group-toggle:hover { background: rgba(255,255,255,.07); color:#fff; }

        .sidebar-group-items { display: none; }
        .sidebar-group.open .sidebar-group-items { display: block; }

        .sidebar-arrow { font-size: 10px; transition: transform .2s; }
        .sidebar-group.open .sidebar-arrow { transform: rotate(180deg); }

        .sidebar-group-items .sidebar-link {
            padding-inline-start: 30px;
            font-size: 13px;
            padding-top: 7px;
            padding-bottom: 7px;
        }
    </style>
    
    <script>
        (function () {
            var accent = @json(\App\Models\Setting::where('key', 'theme_accent')->value('value') ?? '');
            if (accent) document.documentElement.setAttribute('data-accent', accent);
        })();
    </script>
    </head>
<body>

@php
    $unreadNotifications = auth()->user()->notifications()->unread()->latest()->limit(5)->get();
    $unreadCount = auth()->user()->notifications()->unread()->count();
@endphp

<div class="app-wrapper">

    <!-- Sidebar -->
    <div class="offcanvas-lg offcanvas-start app-sidebar" tabindex="-1" id="sidebarMenu">
        <div class="offcanvas-header d-lg-none">
            <h5 class="offcanvas-title text-white">نظام المبيعات</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu"></button>
        </div>

        <div class="offcanvas-body d-lg-block px-0">
            <div class="sidebar-brand d-none d-lg-block">
                📊 نظام المبيعات والحسابات
            </div>

            <nav class="pb-4">

    <div class="sidebar-group" data-group="main">
        <button type="button" class="sidebar-group-toggle" onclick="toggleSidebarGroup(this)">
            <span>🏠 الرئيسية</span>
            <span class="sidebar-arrow">▼</span>
        </button>
        <div class="sidebar-group-items">
            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">📊 لوحة التحكم</a>
            <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.index') ? 'active' : '' }}">📋 لوحة التقارير</a>
                    <a href="{{ route('fund.index') }}" class="sidebar-link {{ request()->routeIs('fund.*') ? 'active' : '' }}">
                        <span class="sidebar-icon">💰</span>
                        <span class="sidebar-text">إدارة الأموال</span>
                    </a>
                    <a href="{{ route('statements.index') }}" class="sidebar-link {{ request()->routeIs('statements.*') ? 'active' : '' }}">
                        <span class="sidebar-icon">📄</span>
                        <span class="sidebar-text">كشوف الحسابات</span>
                    </a>
                    <a href="{{ route('payables.index') }}" class="sidebar-link {{ request()->routeIs('payables.*') ? 'active' : '' }}">
                        <span class="sidebar-icon">💳</span>
                        <span class="sidebar-text">دفع المستحقات</span>
                    </a>
                    <a href="{{ route('collections.index') }}" class="sidebar-link {{ request()->routeIs('collections.*') ? 'active' : '' }}">
                        <span class="sidebar-icon">💰</span>
                        <span class="sidebar-text">تحصيل المستحقات</span>
                    </a>
            <a href="{{ route('guide.index') }}" class="sidebar-link {{ request()->routeIs('guide.*') ? 'active' : '' }}">📖 دليل المستخدم</a>
        </div>
    </div>

    <div class="sidebar-group" data-group="operations">
        <button type="button" class="sidebar-group-toggle" onclick="toggleSidebarGroup(this)">
            <span>🧾 العمليات</span>
            <span class="sidebar-arrow">▼</span>
        </button>
        <div class="sidebar-group-items">
            @if(auth()->user()->can('sales.create'))
                <a href="{{ route('pos.index') }}" class="sidebar-link {{ request()->routeIs('pos.*') ? 'active' : '' }}" style="color:#16a34a; font-weight:800;">🖥️ نقطة البيع POS</a>
            @endif
            @if(auth()->user()->can('sales.view'))
                <a href="{{ route('sales.index') }}" class="sidebar-link {{ request()->routeIs('sales.*') && !request()->routeIs('sales-returns.*') ? 'active' : '' }}">🧾 فواتير البيع</a>
            @endif
            @if(auth()->user()->can('returns.view'))
                <a href="{{ route('sales-returns.index') }}" class="sidebar-link {{ request()->routeIs('sales-returns.*') ? 'active' : '' }}">↩️ مرتجع بيع</a>
                    {{-- <a href="{{ route('returns.select-invoice') }}" class="sidebar-link {{ request()->routeIs('returns.select-invoice', 'returns.from-invoice', 'returns.process-from-invoice') ? 'active' : '' }}">
                        <span class="sidebar-icon">🔄</span>
                        <span class="sidebar-text">إرجاع من فاتورة</span>
                        <span class="badge bg-info ms-auto">جديد</span>
                    </a> --}}
            @endif
            @if(auth()->user()->can('purchases.view'))
                <a href="{{ route('purchases.index') }}" class="sidebar-link {{ request()->routeIs('purchases.*') && !request()->routeIs('purchase-returns.*') ? 'active' : '' }}">🛒 فواتير الشراء</a>
            @endif
            @if(auth()->user()->can('returns.view'))
                <a href="{{ route('purchase-returns.index') }}" class="sidebar-link {{ request()->routeIs('purchase-returns.*') ? 'active' : '' }}">↪️ مرتجع شراء</a>
            @endif
        </div>
    </div>

    @if(auth()->user()->can('payments.view'))
    <div class="sidebar-group" data-group="payments">
        <button type="button" class="sidebar-group-toggle" onclick="toggleSidebarGroup(this)">
            <span>💰 السندات</span>
            <span class="sidebar-arrow">▼</span>
        </button>
        <div class="sidebar-group-items">
            <a href="{{ route('payments.index') }}" class="sidebar-link {{ request()->routeIs('payments.*') ? 'active' : '' }}">💰 سندات قبض وصرف</a>
        </div>
    </div>
    @endif

    <div class="sidebar-group" data-group="parties">
        <button type="button" class="sidebar-group-toggle" onclick="toggleSidebarGroup(this)">
            <span>👥 الأطراف والأصناف</span>
            <span class="sidebar-arrow">▼</span>
        </button>
        <div class="sidebar-group-items">
            @if(auth()->user()->can('sales.view') || auth()->user()->can('master-data.manage'))
                <a href="{{ route('customers.index') }}" class="sidebar-link {{ request()->routeIs('customers.*') ? 'active' : '' }}">👥 العملاء</a>
            @endif
            @if(auth()->user()->can('purchases.view') || auth()->user()->can('master-data.manage'))
                <a href="{{ route('suppliers.index') }}" class="sidebar-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}">🏭 الموردون</a>
            @endif
            @if(auth()->user()->can('master-data.manage') || auth()->user()->can('sales.view') || auth()->user()->can('purchases.view'))
                <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">📦 الأصناف</a>
                    <a href="{{ route('products.price-compare') }}" class="sidebar-link">
                        <span class="sidebar-icon">💰</span>
                        <span class="sidebar-text">مقارنة الأسعار</span>
                    </a>
            @endif
        </div>
    </div>

    @if(auth()->user()->can('master-data.manage'))
    <div class="sidebar-group" data-group="masterdata">
        <button type="button" class="sidebar-group-toggle" onclick="toggleSidebarGroup(this)">
            <span>🗂️ البيانات الأساسية</span>
            <span class="sidebar-arrow">▼</span>
        </button>
        <div class="sidebar-group-items">
            <a href="{{ route('categories.index') }}" class="sidebar-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">🗂️ التصنيفات</a>
            <a href="{{ route('units.index') }}" class="sidebar-link {{ request()->routeIs('units.*') ? 'active' : '' }}">⚖️ الوحدات</a>
            <a href="{{ route('warehouses.index') }}" class="sidebar-link {{ request()->routeIs('warehouses.*') ? 'active' : '' }}">🏬 المخازن</a>
            <a href="{{ route('barcode.index') }}" class="sidebar-link {{ request()->routeIs('barcode.*') ? 'active' : '' }}">🏷️ طباعة باركود</a>
        </div>
    </div>
    @endif

    @if(auth()->user()->can('reports.view'))
    <div class="sidebar-group" data-group="reports">
        <button type="button" class="sidebar-group-toggle" onclick="toggleSidebarGroup(this)">
            <span>📈 التقارير</span>
            <span class="sidebar-arrow">▼</span>
        </button>
        <div class="sidebar-group-items">
            <a href="{{ route('reports.sales') }}" class="sidebar-link {{ request()->routeIs('reports.sales') ? 'active' : '' }}">📈 تقرير المبيعات</a>
            <a href="{{ route('reports.purchases') }}" class="sidebar-link {{ request()->routeIs('reports.purchases') ? 'active' : '' }}">📉 تقرير المشتريات</a>
            <a href="{{ route('reports.customers') }}" class="sidebar-link {{ request()->routeIs('reports.customers') ? 'active' : '' }}">💳 أرصدة العملاء</a>
            <a href="{{ route('reports.suppliers') }}" class="sidebar-link {{ request()->routeIs('reports.suppliers') ? 'active' : '' }}">🏦 أرصدة الموردين</a>
            <a href="{{ route('reports.stock') }}" class="sidebar-link {{ request()->routeIs('reports.stock') ? 'active' : '' }}">📊 تقرير المخزون</a>
            <a href="{{ route('reports.stock-movements') }}" class="sidebar-link {{ request()->routeIs('reports.stock-movements') ? 'active' : '' }}">🔄 حركة المخزون</a>
            <a href="{{ route('reports.payments') }}" class="sidebar-link {{ request()->routeIs('reports.payments') ? 'active' : '' }}">💵 تقرير المدفوعات</a>
            <a href="{{ route('reports.profit') }}" class="sidebar-link {{ request()->routeIs('reports.profit') ? 'active' : '' }}">🤑 تقرير الأرباح</a>
        </div>
    </div>

    <div class="sidebar-group" data-group="advreports">
        <button type="button" class="sidebar-group-toggle" onclick="toggleSidebarGroup(this)">
            <span>🔬 تقارير متقدمة</span>
            <span class="sidebar-arrow">▼</span>
        </button>
        <div class="sidebar-group-items">
            <a href="{{ route('reports.sales-by-product') }}" class="sidebar-link {{ request()->routeIs('reports.sales-by-product') ? 'active' : '' }}">📦 مبيعات حسب الصنف</a>
            <a href="{{ route('reports.top-customers') }}" class="sidebar-link {{ request()->routeIs('reports.top-customers') ? 'active' : '' }}">🏆 أفضل العملاء</a>
            <a href="{{ route('reports.slow-moving') }}" class="sidebar-link {{ request()->routeIs('reports.slow-moving') ? 'active' : '' }}">🐢 أصناف بطيئة</a>
            <a href="{{ route('reports.receivables-ageing') }}" class="sidebar-link {{ request()->routeIs('reports.receivables-ageing') ? 'active' : '' }}">⏳ أعمار الديون</a>
        </div>
    </div>
    @endif

    @if(auth()->user()->can('financial.view'))
    <div class="sidebar-group" data-group="accounting">
        <button type="button" class="sidebar-group-toggle" onclick="toggleSidebarGroup(this)">
            <span>📒 الحسابات</span>
            <span class="sidebar-arrow">▼</span>
        </button>
        <div class="sidebar-group-items">
            <a href="{{ route('journal.index') }}" class="sidebar-link {{ request()->routeIs('journal.*') ? 'active' : '' }}">📒 قيود اليومية</a>
            <a href="{{ route('financial.trial-balance') }}" class="sidebar-link {{ request()->routeIs('financial.trial-balance') ? 'active' : '' }}">⚖️ ميزان المراجعة</a>
            <a href="{{ route('financial.ledger') }}" class="sidebar-link {{ request()->routeIs('financial.ledger') ? 'active' : '' }}">📚 دفتر الأستاذ</a>
            <a href="{{ route('financial.income-statement') }}" class="sidebar-link {{ request()->routeIs('financial.income-statement') ? 'active' : '' }}">🧮 قائمة الدخل</a>
            <a href="{{ route('financial.revenues') }}" class="sidebar-link {{ request()->routeIs('financial.revenues') ? 'active' : '' }}">💹 الإيرادات</a>
            <a href="{{ route('financial.expenses') }}" class="sidebar-link {{ request()->routeIs('financial.expenses') ? 'active' : '' }}">💸 المصروفات</a>
            <a href="{{ route('financial.balance-sheet') }}" class="sidebar-link {{ request()->routeIs('financial.balance-sheet') ? 'active' : '' }}">🏛️ المركز المالي</a>
        </div>
    </div>
    @endif

    <div class="sidebar-group" data-group="company">
        <button type="button" class="sidebar-group-toggle" onclick="toggleSidebarGroup(this)">
            <span>🏢 الشركة</span>
            <span class="sidebar-arrow">▼</span>
        </button>
        <div class="sidebar-group-items">
            @if(auth()->user()->can('partners.manage'))
                <a href="{{ route('partners.index') }}" class="sidebar-link {{ request()->routeIs('partners.*') && !request()->routeIs('partners.statement') ? 'active' : '' }}">👥 الشركاء</a>
            @endif
            @if(auth()->user()->can('partners.manage'))
                <a href="{{ route('expense-categories.index') }}" class="sidebar-link {{ request()->routeIs('expense-categories.*') ? 'active' : '' }}">🗂️ تصنيفات المصروفات</a>
            @endif
            @if(auth()->user()->can('expenses.view'))
                <a href="{{ route('expenses.index') }}" class="sidebar-link {{ request()->routeIs('expenses.*') ? 'active' : '' }}">💸 المصروفات</a>
            @endif
            @if(auth()->user()->can('distributions.manage'))
                <a href="{{ route('distributions.index') }}" class="sidebar-link {{ request()->routeIs('distributions.*') ? 'active' : '' }}">💰 توزيع الأرباح</a>
            @endif
            @if(auth()->user()->can('partners.manage'))
                <a href="{{ route('reports.partners') }}" class="sidebar-link {{ request()->routeIs('reports.partners') ? 'active' : '' }}">📋 تقرير الشركاء</a>
            @endif
            @if(auth()->user()->can('distributions.manage'))
                <a href="{{ route('reports.distributions') }}" class="sidebar-link {{ request()->routeIs('reports.distributions') ? 'active' : '' }}">💰 تقرير التوزيعات</a>
                <a href="{{ route('reports.distributions-items') }}" class="sidebar-link {{ request()->routeIs('reports.distributions-items') ? 'active' : '' }}">🧮 أنصبة الشركاء</a>
            @endif
        </div>
    </div>

    <div class="sidebar-group" data-group="admin">
        <button type="button" class="sidebar-group-toggle" onclick="toggleSidebarGroup(this)">
            <span>⚙️ الإدارة</span>
            <span class="sidebar-arrow">▼</span>
        </button>
        <div class="sidebar-group-items">
            @if(auth()->user()->hasRole('admin'))
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">👤 المستخدمون والصلاحيات</a>
            @endif
            @if(auth()->user()->can('settings.manage'))
                <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">⚙️ الإعدادات</a>
                <a href="{{ route('backups.index') }}" class="sidebar-link {{ request()->routeIs('backups.*') ? 'active' : '' }}">💾 النسخ الاحتياطي</a>
                <a href="{{ route('audit-logs.index') }}" class="sidebar-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">🧯 سجل العمليات</a>
            @endif
            @if(auth()->user()->hasRole('admin'))
                <a href="{{ route('deployment.checklist') }}" class="sidebar-link {{ request()->routeIs('deployment.*') ? 'active' : '' }}">🚀 دليل النشر</a>
                <a href="{{ route('reset.index') }}" class="sidebar-link {{ request()->routeIs('reset.*') ? 'active' : '' }}">🗑️ إعادة تعيين النظام</a>
            @endif
        </div>
    </div>

</nav>
        </div>
    </div>

    <!-- Main -->
    <div class="app-main">

        <nav class="topbar navbar navbar-expand-lg sticky-top px-3">
            <button class="btn btn-outline-secondary d-lg-none"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#sidebarMenu">
                ☰
            </button>

            <div class="ms-auto d-flex align-items-center gap-2">

                <button class="btn btn-outline-secondary btn-sm" onclick="toggleTheme()" id="theme-toggle">
                    🌙
                </button>

                @auth
                    <div class="dropdown">
                        <a class="btn btn-outline-secondary btn-sm position-relative" href="#" role="button" data-bs-toggle="dropdown">
                            🔔
                            @if($unreadCount > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end notifications-menu">
                            <li class="dropdown-item-text fw-bold d-flex justify-content-between align-items-center">
                                <span>الإشعارات</span>

                                <form method="POST" action="{{ route('notifications.read-all') }}">
                                    @csrf
                                    <button class="btn btn-sm btn-link p-0 small">تحديد الكل كمقروء</button>
                                </form>
                            </li>

                            <li><hr class="dropdown-divider"></li>

                            @forelse($unreadNotifications as $notification)
                                <li>
                                    <a class="dropdown-item text-wrap" href="{{ route('notifications.open', $notification) }}">
                                        <div class="fw-bold small">{{ $notification->title }}</div>
                                        <div class="small text-muted">{{ Str::limit($notification->message, 80) }}</div>
                                    </a>
                                </li>

                                <li><hr class="dropdown-divider"></li>
                            @empty
                                <li>
                                    <span class="dropdown-item-text small text-muted">لا توجد إشعارات جديدة.</span>
                                </li>
                            @endforelse

                            <li>
                                <a class="dropdown-item text-center small" href="{{ route('notifications.index') }}">
                                    عرض كل الإشعارات
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="dropdown">
                        <a class="btn btn-outline-secondary btn-sm dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            {{ auth()->user()->name }}
                            <small class="text-muted">({{ auth()->user()->getRoleNames()->first() }})</small>
                        </a>

                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        تسجيل الخروج
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth

            </div>
        </nav>

        <div class="container-fluid px-3 px-lg-4 py-4">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')

        </div>
    </div>

</div>

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

<script>
    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-bs-theme');
        const next = current === 'dark' ? 'light' : 'dark';

        document.documentElement.setAttribute('data-bs-theme', next);
        localStorage.setItem('theme', next);

        updateThemeButton(next);
    }

    function updateThemeButton(theme) {
        const button = document.getElementById('theme-toggle');

        if (button) {
            button.innerHTML = theme === 'dark' ? '☀️' : '🌙';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateThemeButton(document.documentElement.getAttribute('data-bs-theme'));

        const sidebarLinks = document.querySelectorAll('.sidebar-link');

        sidebarLinks.forEach(function (link) {
            link.addEventListener('click', function () {
                if (window.innerWidth < 992) {
                    const sidebar = document.getElementById('sidebarMenu');
                    const offcanvas = bootstrap.Offcanvas.getInstance(sidebar);

                    if (offcanvas) {
                        offcanvas.hide();
                    }
                }
            });
        });
    });
</script>

@include('partials.assistant')

    

    <script>
        let masterAudioCtx;

        function playSound(type) {
            try {
                masterAudioCtx = masterAudioCtx || new (window.AudioContext || window.webkitAudioContext)();
                const o = masterAudioCtx.createOscillator();
                const g = masterAudioCtx.createGain();
                o.connect(g); g.connect(masterAudioCtx.destination);
                let freq = 880, dur = 0.15;
                if (type === "success") { freq = 1046; dur = 0.25; }
                if (type === "error") { freq = 220; dur = 0.3; }
                o.frequency.value = freq; o.type = "sine";
                g.gain.setValueAtTime(0.0001, masterAudioCtx.currentTime);
                g.gain.exponentialRampToValueAtTime(0.15, masterAudioCtx.currentTime + 0.01);
                g.gain.exponentialRampToValueAtTime(0.0001, masterAudioCtx.currentTime + dur);
                o.start(); o.stop(masterAudioCtx.currentTime + dur);
            } catch (e) {}
        }

        function setAccent(name) {
            if (name) document.documentElement.setAttribute("data-accent", name);
            else document.documentElement.removeAttribute("data-accent");
            localStorage.setItem("accent", name || "");
        }

        document.addEventListener("DOMContentLoaded", function () {
            const saved = localStorage.getItem("accent") || "";
            if (saved) document.documentElement.setAttribute("data-accent", saved);

            if (document.querySelector(".alert-success")) playSound("success");
            else if (document.querySelector(".alert-danger")) playSound("error");

            if ("serviceWorker" in navigator) {
                navigator.serviceWorker.register("/sw.js").catch(function () {});
            }
        });
    </script>
    
    <script>
        function toggleSidebarGroup(btn) {
            const group = btn.closest('.sidebar-group');
            const key = 'sidebar_' + group.dataset.group;
            group.classList.toggle('open');
            localStorage.setItem(key, group.classList.contains('open') ? '1' : '0');
        }

        function getSidebarScrollEl() {
            const sidebar = document.querySelector('.app-sidebar');
            if (!sidebar) return null;
            if (sidebar.scrollHeight > sidebar.clientHeight && sidebar.clientHeight > 0) return sidebar;
            return document.querySelector('.offcanvas-body') || sidebar;
        }

        function initSidebarGroups() {
            document.querySelectorAll('.sidebar-group').forEach(group => {
                const key = 'sidebar_' + group.dataset.group;
                const saved = localStorage.getItem(key);
                const hasActive = group.querySelector('.sidebar-link.active');

                if (hasActive) {
                    group.classList.add('open');
                } else if (saved === '1') {
                    group.classList.add('open');
                } else if (saved === null && group.dataset.group === 'main') {
                    group.classList.add('open');
                }
            });

            const scrollEl = getSidebarScrollEl();
            const pos = sessionStorage.getItem('sidebar_scroll_pos');

            if (pos && scrollEl) {
                scrollEl.scrollTop = parseInt(pos, 10);
            }

            if (scrollEl) {
                scrollEl.addEventListener('scroll', () => {
                    sessionStorage.setItem('sidebar_scroll_pos', scrollEl.scrollTop);
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initSidebarGroups);
        } else {
            initSidebarGroups();
        }
    </script>
    
<!-- Floating Command Button -->
<a hidden href="{{ route('command-center.index') }}" class="btn btn-primary rounded-circle shadow-lg" 
   style="position: fixed; bottom: 30px; left: 30px; width: 60px; height: 60px; display: flex; align-items: center; justify-content: center; font-size: 24px; z-index: 1000; background: linear-gradient(135deg, #198754 0%, #0d6efd 100%); border: none;"
   title="مركز الأوامر (Ctrl+K)">
    🎯
</a>
    <script src="{{ asset('js/form-protection.js') }}?v={{ filemtime(public_path('js/form-protection.js')) }}"></script>
</body>
</html>