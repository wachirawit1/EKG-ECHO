@extends('layout')
@section('title', 'Report')
@section('content')
    {{-- breadcrums --}}
    <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="nav-link text-decoration-none"
                    href="{{ route('dashboard.show') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Report</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col">
            <canvas id="appointmentChart" height="100"></canvas>
        </div>
        <div class="col">
            <canvas id="doctorChart" height="150"></canvas>
        </div>
    </div>


    @foreach ($appointmentsByDoctor as $doctor => $appointments)
        <h4 class="mt-4">
            หมอ : {{ $doctor }}
            <span class="text-muted">({{ $appointments->count() }} นัด)</span>
        </h4>
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead class="table-light">
                    <tr>
                        <th>HN</th>
                        <th>ชื่อผู้ป่วย</th>
                        <th>วันนัด</th>
                        <th>เวลา</th>
                        <th>แหล่งที่มา</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->hn }}</td>
                            <td>{{ $appointment->patient_name }}</td>
                            <td>{{ $appointment->date }}</td>
                            <td>{{ $appointment->time }}</td>
                            <td>{{ $appointment->source }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach


    @php
        $sourceCounts = $allAppointments->groupBy('source')->map->count();
        $chartDoctorData = $appointmentsByDoctor->map(function ($items, $key) {
            return count($items); // จำนวนครั้งที่หมอคนนั้นมีนัด
        });
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

        const doctorLabels = {!! json_encode($appointmentsByDoctor->keys()) !!};
        const appointmentCounts = {!! json_encode($appointmentsByDoctor->map->count()->values()) !!};

        const generateColors = (count) => {
            const colors = [];
            for (let i = 0; i < count; i++) {
                colors.push(`hsl(${(i * 360 / count)}, 70%, 60%)`);
            }
            return colors;
        };

        const ctxDoc = document.getElementById('doctorChart').getContext('2d');
        const doctorChart = new Chart(ctxDoc, {
            type: 'bar',
            data: {
                labels: doctorLabels,
                datasets: [{
                    label: 'จำนวนนัดหมาย',
                    data: appointmentCounts,
                    backgroundColor: generateColors(doctorLabels.length),
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        title: {
                            display: true,
                            text: 'จำนวนครั้ง'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'ชื่อแพทย์'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `จำนวน: ${context.parsed.y} นัด`;
                            }
                        }
                    }
                }
            }
        });
    </script>

@endsection
