@extends('layouts.app', ['title' => 'لوحة التحكم'])

@push('styles')
<style>
    .archon-chart-card .card-body {
        padding: 20px;
    }
</style>
@endpush

@section('content')
<div class="archon-stats-grid">
    <div class="archon-stat-card stat-primary">
        <div class="archon-stat-label">عدد الأصناف</div>
        <div class="archon-stat-value">{{ number_format($productsCount) }}</div>
        <div class="archon-stat-meta">إجمالي الأصناف المسجلة بالنظام</div>
    </div>

    <div class="archon-stat-card stat-info">
        <div class="archon-stat-label">إجمالي المخزون</div>
        <div class="archon-stat-value">{{ number_format($stockQty, 2) }} م</div>
        <div class="archon-stat-meta">إجمالي الكمية المتاحة بالمتر</div>
    </div>

    <div class="archon-stat-card stat-success">
        <div class="archon-stat-label">إجمالي المبيعات</div>
        <div class="archon-stat-value">{{ number_format($totalSales, 2) }} ج.م</div>
        <div class="archon-stat-meta">قيمة فواتير البيع المسجلة</div>
    </div>

    <div class="archon-stat-card {{ $todayProfit > 0 ? 'stat-success' : 'stat-danger' }}">
        <div class="archon-stat-label">ربح اليوم</div>
        <div class="archon-stat-value">{{ number_format($todayProfit, 2) }} ج.م</div>
        <div class="archon-stat-meta">صافي أرباح بنود البيع اليوم</div>
    </div>

    <div class="archon-stat-card {{ $salesDue > 0 ? 'stat-warning' : 'stat-primary' }}">
        <div class="archon-stat-label">الذمم المستحقة لنا</div>
        <div class="archon-stat-value">{{ number_format($salesDue, 2) }} ج.م</div>
        <div class="archon-stat-meta">مديونيات العملاء المفتوحة</div>
    </div>

    <div class="archon-stat-card {{ $purchaseDue > 0 ? 'stat-danger' : 'stat-dark' }}">
        <div class="archon-stat-label">الالتزامات المستحقة علينا</div>
        <div class="archon-stat-value">{{ number_format($purchaseDue, 2) }} ج.م</div>
        <div class="archon-stat-meta">مديونيات الموردين المفتوحة</div>
    </div>
</div>

<div class="archon-cashbox-grid">
    @forelse($cashboxes as $cashbox)
        @php
            $balanceClass = $cashbox->balance <= 0 ? 'zero' : ($cashbox->balance <= 3000 ? 'low' : 'good');
        @endphp
        <div class="archon-cashbox-card">
            <div class="archon-cashbox-name">{{ $cashbox->name }}</div>
            <div class="archon-cashbox-balance {{ $balanceClass }}">{{ number_format($cashbox->balance, 2) }} ج.م</div>
            <div class="text-muted small mt-2">الرصيد الحالي للخزنة</div>
        </div>
    @empty
        <div class="archon-card p-4 text-center text-muted" style="grid-column: 1 / -1;">
            لا توجد خزن مسجلة حاليًا
        </div>
    @endforelse
</div>

<div class="archon-grid-wide mb-4">
    <div class="archon-card archon-chart-card">
        <div class="card-body">
            <h3 class="archon-section-title">اتجاه المبيعات خلال آخر 7 أيام</h3>
            <div class="archon-chart-wrap">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
    </div>

    <div class="archon-card archon-chart-card">
        <div class="card-body">
            <h3 class="archon-section-title">اتجاه الأرباح خلال آخر 7 أيام</h3>
            <div class="archon-chart-wrap">
                <canvas id="profitChart"></canvas>
            </div>
        </div>
    </div>
</div>

<div class="archon-grid-2">
    <div class="archon-card">
        <div class="card-body">
            <h3 class="archon-section-title">أكثر 5 أصناف مبيعًا</h3>
            <div class="table-responsive">
                <table class="table align-middle text-center">
                    <thead>
                        <tr>
                            <th>الصنف</th>
                            <th>إجمالي الكمية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $product)
                            <tr>
                                <td>{{ $product->product_name }}</td>
                                <td>{{ number_format($product->total_qty, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="archon-empty">لا توجد بيانات</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="archon-card">
        <div class="card-body">
            <h3 class="archon-section-title">أقرب 5 أصناف على النفاد</h3>
            <div class="table-responsive">
                <table class="table align-middle text-center">
                    <thead>
                        <tr>
                            <th>الصنف</th>
                            <th>الكمية الحالية</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($lowStockProducts as $product)
                            <tr>
                                <td>{{ $product->full_name }}</td>
                                <td>
                                    @if($product->quantity_meter <= 0)
                                        <span class="archon-badge-soft archon-badge-danger">{{ number_format($product->quantity_meter, 2) }} م</span>
                                    @else
                                        <span class="archon-badge-soft archon-badge-warning">{{ number_format($product->quantity_meter, 2) }} م</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="archon-empty">لا توجد أصناف منخفضة حاليًا</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartLabels = @json($chartLabels);
    const salesChartData = @json($salesChartData);
    const profitChartData = @json($profitChartData);

    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'المبيعات',
                data: salesChartData,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.12)',
                fill: true,
                tension: 0.35,
                borderWidth: 3,
                pointRadius: 4
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        font: { family: 'Arial' }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    new Chart(document.getElementById('profitChart'), {
        type: 'bar',
        data: {
            labels: chartLabels,
            datasets: [{
                label: 'الأرباح',
                data: profitChartData,
                backgroundColor: '#198754',
                borderRadius: 8
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        font: { family: 'Arial' }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
@endpush