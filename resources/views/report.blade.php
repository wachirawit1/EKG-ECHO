@extends('layout')
@section('title', 'Report')
@section('content')
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="nav-link text-decoration-none" href="{{route('dashboard.show')}}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Report</li>
        </ol>
    </nav>
<canvas id="appointmentChart" height="100"></canvas>

@php
    $sourceCounts = $allAppointments->groupBy('source')->map->count();
@endphp

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('appointmentChart').getContext('2d');
    const appointmentChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($sourceCounts->keys()) !!},
            datasets: [{
                label: 'จำนวนการนัด',
                data: {!! json_encode($sourceCounts->values()) !!},
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)'
                ],
                borderColor: 'rgba(0, 0, 0, 0.1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'จำนวน'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'แหล่งข้อมูล (Source)'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
</script>


    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>วันที่นัด</th>
                    <th>HN</th>
                    <th>แพทย์</th>
                    <th>sorce</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($allAppointments as $i => $appt)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $appt->date }}</td>
                        <td>{{ $appt->hn }}</td>
                        <td>{{ $appt->doctor }}</td>
                        <td><span class="badge text-bg-info">{{ strtoupper($appt->source) }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
