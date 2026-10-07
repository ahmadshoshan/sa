@extends('layouts.master')

@section('title', 'لوحة التحكم')

@section('content')

    <h4 class="mb-4">📊 لوحة التحكم</h4>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-white bg-success h-100">
                <div class="card-body">
                    <h6>مبيعات اليوم</h6>
                    <h4>{{ number_format($todaySales, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card text-white bg-primary h-100">
                <div class="card-body">
                    <h6>مبيعات الشهر</h6>
                    <h4>{{ number_format($monthSales, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card text-white bg-info h-100">
                <div class="card-body">
                    <h6>مشتريات الشهر</h6>
                    <h4>{{ number_format($monthPurchases, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card text-white bg-warning h-100">
                <div class="card-body">
                    <h6>أصناف منخفضة</h6>
                    <h4>{{ $lowStock }}</h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6>ذمم العملاء (لنا)</h6>
                    <h4 class="text-success">{{ number_format($receivables, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6>ذمم الموردين (علينا)</h6>
                    <h4 class="text-danger">{{ number_format($payables, 2) }}</h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6>العملاء</h6>
                    <h4>{{ $customersCount }}</h4>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card h-100">
                <div class="card-body">
                    <h6>الأصناف</h6>
                    <h4>{{ $productsCount }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">المبيعات اليومية (آخر 30 يوم)</div>
                <div class="card-body">
                    <canvas id="dailyChart" height="110"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">المبيعات حسب التصنيف</div>
                <div class="card-body">
                    <canvas id="categoryChart" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">المبيعات الشهرية (آخر 12 شهر)</div>
                <div class="card-body">
                    <canvas id="monthlyChart" height="160"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">أفضل 5 أصناف مبيعًا</div>
                <div class="card-body">
                    <canvas id="topChart" height="160"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if(file_exists(public_path('assets/js/chart.min.js')))
        <script src="{{ asset('assets/js/chart.min.js') }}"></script>
    @else
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof Chart === 'undefined') return;

            Chart.defaults.font.family = 'Tahoma, Arial, sans-serif';

            new Chart(document.getElementById('dailyChart'), {
                type: 'line',
                data: {
                    labels: @json($dailyLabels),
                    datasets: [{
                        label: 'المبيعات',
                        data: @json($dailyData),
                        borderColor: '#16a34a',
                        backgroundColor: 'rgba(22,163,74,0.15)',
                        fill: true,
                        tension: 0.35
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });

            new Chart(document.getElementById('monthlyChart'), {
                type: 'bar',
                data: {
                    labels: @json($monthlyLabels),
                    datasets: [{
                        label: 'المبيعات',
                        data: @json($monthlyData),
                        backgroundColor: '#2563eb'
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });

            new Chart(document.getElementById('categoryChart'), {
                type: 'doughnut',
                data: {
                    labels: @json($categorySales->pluck('name')),
                    datasets: [{
                        data: @json($categorySales->pluck('total')),
                        backgroundColor: ['#2563eb', '#16a34a', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4']
                    }]
                },
                options: { responsive: true }
            });

            new Chart(document.getElementById('topChart'), {
                type: 'bar',
                data: {
                    labels: @json($topProducts->pluck('name')),
                    datasets: [{
                        label: 'الإجمالي',
                        data: @json($topProducts->pluck('total')),
                        backgroundColor: '#8b5cf6'
                    }]
                },
                options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } } }
            });
        });
    </script>

@endsection