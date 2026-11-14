@extends('layouts.app')

@section('page-title', 'Analytics Report')

@section('content')

<style>
    .analytics-container {
        width: 100%;
    }

    .analytics-header {
        margin-bottom: 25px;
    }

    .analytics-header h2 {
        color: #203f91;
        font-size: 25px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .analytics-header p {
        color: #777;
        margin: 0;
    }

    .analytics-stats {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 25px;
    }

    .analytics-stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 22px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        border-left: 5px solid #203f91;
    }

    .analytics-stat-card h3 {
        color: #777;
        font-size: 14px;
        font-weight: 600;
        margin: 0 0 12px;
    }

    .analytics-stat-card .stat-number {
        color: #203f91;
        font-size: 30px;
        font-weight: 700;
        margin: 0;
    }

    .analytics-stat-card.archived {
        border-left-color: #dc3545;
    }

    .analytics-stat-card.archived .stat-number {
        color: #dc3545;
    }

    .analytics-stat-card.online {
        border-left-color: #198754;
    }

    .analytics-stat-card.online .stat-number {
        color: #198754;
    }

    .analytics-charts {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .analytics-chart-card {
        background: #fff;
        border-radius: 12px;
        padding: 22px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
        min-width: 0;
    }

    .analytics-chart-card.full-width {
        grid-column: 1 / -1;
    }

    .analytics-chart-card h3 {
        color: #203f91;
        font-size: 17px;
        font-weight: 700;
        margin: 0 0 5px;
    }

    .analytics-chart-card p {
        color: #888;
        font-size: 13px;
        margin-bottom: 18px;
    }

    .chart-wrapper {
        position: relative;
        width: 100%;
        height: 300px;
    }

    .full-width .chart-wrapper {
        height: 350px;
    }

    @media (max-width: 900px) {
        .analytics-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .analytics-charts {
            grid-template-columns: 1fr;
        }

        .analytics-chart-card.full-width {
            grid-column: auto;
        }
    }

    @media (max-width: 600px) {
        .analytics-stats {
            grid-template-columns: 1fr;
        }

        .analytics-stat-card {
            padding: 18px;
        }

        .analytics-chart-card {
            padding: 15px;
        }
    }
</style>

<div class="analytics-container">

    <div class="analytics-header">
        <h2>Analytics Report</h2>
        <p>Student account statistics and user activity overview.</p>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="analytics-stats">

        <div class="analytics-stat-card">
            <h3>Total Student Accounts</h3>
            <p class="stat-number">
                {{ number_format($totalStudents) }}
            </p>
        </div>

        <div class="analytics-stat-card archived">
            <h3>Archived Student Accounts</h3>
            <p class="stat-number">
                {{ number_format($archivedAccounts) }}
            </p>
        </div>

        <div class="analytics-stat-card online">
            <h3>Currently Logged-in Users</h3>
            <p class="stat-number">
                {{ number_format($currentlyLoggedIn) }}
            </p>
        </div>

    </div>

    {{-- STUDENT CHARTS --}}
    <div class="analytics-charts">

        <div class="analytics-chart-card">
            <h3>Students per Program</h3>
            <p>Distribution of student accounts by academic program.</p>

            <div class="chart-wrapper">
                <canvas id="programChart"></canvas>
            </div>
        </div>

        <div class="analytics-chart-card">
            <h3>Students per Year Level</h3>
            <p>Number of students grouped by year level.</p>

            <div class="chart-wrapper">
                <canvas id="yearChart"></canvas>
            </div>
        </div>

        {{-- LOGIN AND LOGOUT ACTIVITY --}}
        <div class="analytics-chart-card full-width">
            <h3>Daily Login/Logout Activity</h3>
            <p>Recorded login and logout activities for the last 7 days.</p>

            <div class="chart-wrapper">
                <canvas id="activityChart"></canvas>
            </div>
        </div>

    </div>

</div>

{{-- CHART.JS --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const programLabels = @json($programLabels);
    const programData = @json($programData);

    const yearLabels = @json($yearLabels);
    const yearData = @json($yearData);

    const activityDates = @json($activityDates);
    const loginCounts = @json($loginCounts);
    const logoutCounts = @json($logoutCounts);

    // STUDENTS PER PROGRAM - DOUGHNUT CHART
    new Chart(document.getElementById('programChart'), {
        type: 'doughnut',
        data: {
            labels: programLabels,
            datasets: [{
                label: 'Students',
                data: programData,
                backgroundColor: [
                    '#203f91',
                    '#4169e1',
                    '#6495ed',
                    '#87ceeb',
                    '#36a2eb',
                    '#6c8cd5',
                    '#8e7cc3',
                    '#20a4a8'
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // STUDENTS PER YEAR LEVEL - BAR CHART
    new Chart(document.getElementById('yearChart'), {
        type: 'bar',
        data: {
            labels: yearLabels,
            datasets: [{
                label: 'Number of Students',
                data: yearData,
                backgroundColor: '#4169e1',
                borderRadius: 6,
                maxBarThickness: 65
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    // DAILY LOGIN AND LOGOUT - LINE CHART
    new Chart(document.getElementById('activityChart'), {
        type: 'line',
        data: {
            labels: activityDates,
            datasets: [
                {
                    label: 'Login',
                    data: loginCounts,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25, 135, 84, 0.12)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 3
                },
                {
                    label: 'Logout',
                    data: logoutCounts,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220, 53, 69, 0.10)',
                    fill: true,
                    tension: 0.3,
                    borderWidth: 3
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

});
</script>

@endsection