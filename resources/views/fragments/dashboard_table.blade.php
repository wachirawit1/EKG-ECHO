@php $counter = 1; @endphp
@foreach ($appointmentsByDoctor as $doctor => $appointments)
    <tr class="bg-gray-100">
        <td colspan="6" class="ps-4 py-2 border-bottom fw-bold text-primary small bg-light bg-opacity-50">
            <i class="fas fa-user-md me-2"></i>{{ $doctor }}
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2">{{ $appointments->count() }}
                เคส</span>
        </td>
    </tr>
    @foreach ($appointments as $item)
        @if (empty(trim($item->hn)))
            @continue
        @endif
        <tr>
            <td class="ps-4 text-muted small text-center">{{ $counter++ }}</td>
            <td>
                @if (!empty($item->hospital_name))
                    <span class="badge bg-danger bg-opacity-10 text-danger fw-bold"
                        style="font-size: 11px; white-space: normal; text-align: left; width: fit-content;">
                        <i class="fas fa-hospital-alt me-1"></i>รพ.{{ $item->hospital_name }}
                    </span>
                @else
                    <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold">{{ trim($item->hn) }}</span>
                @endif
            </td>
            <td class="fw-bold">{{ $item->patient_name }}</td>
            <td class="text-primary fw-bold">
                {{ $item->time }}
                <div class="small text-muted fw-normal">{{ $item->date_display }}
                </div>
            </td>
            <td>
                <span class="small text-muted">{{ $item->ward ?? '-' }}</span>
                @if ($item->source == 'สมุดบันทึก')
                    <span class="badge bg-info bg-opacity-10 text-info smaller ms-1"
                        style="font-size: 10px;">{{ $item->source }}</span>
                @else
                    <span class="badge bg-success bg-opacity-10 text-success smaller ms-1"
                        style="font-size: 10px;">{{ $item->source }}</span>
                @endif
            </td>
            <td class="text-center">
                @if ($item->is_treated)
                    <span class="badge bg-success shadow-sm fw-bold" style="font-size: 10px;">
                        <i class="fas fa-check-circle me-1"></i>ตรวจเสร็จแล้ว
                    </span>
                @elseif (!empty($item->reg_no))
                    <span class="badge bg-info shadow-sm fw-bold" style="font-size: 10px;">
                        <i class="fas fa-user-check me-1"></i>มาแล้ว/รอตรวจ
                    </span>
                @elseif (\Carbon\Carbon::parse($item->date)->isPast() && !\Carbon\Carbon::parse($item->date)->isToday())
                    <span class="badge shadow-sm fw-bold"
                        style="font-size: 10px; background-color: #6f42c1; color: white;">
                        <i class="fas fa-user-times me-1"></i>ไม่มาตามนัด
                    </span>
                @elseif (
                    \Carbon\Carbon::parse($item->date)->isToday() &&
                        !empty($item->time_to) &&
                        $item->time_to < \Carbon\Carbon::now()->format('H:i'))
                    {{-- เช็คกรณีระบบหลัก ถ้าไม่มี regNo และเลยเวลา --}}
                    <span class="badge shadow-sm fw-bold"
                        style="font-size: 10px; background-color: #6f42c1; color: white;">
                        <i class="fas fa-user-times me-1"></i>ไม่มาตามนัด
                    </span>
                @else
                    <span class="badge bg-warning text-dark shadow-sm fw-bold" style="font-size: 10px;">
                        <i class="fas fa-clock me-1"></i>รอรับบริการ
                    </span>
                @endif
            </td>
        </tr>
    @endforeach
@endforeach
@if ($appointmentsByDoctor->isEmpty())
    <tr>
        <td colspan="6" class="text-center py-5">
            <img src="https://img.icons8.com/clouds/100/000000/todo-list.png" class="mb-3">
            <p class="text-muted">ไม่พบนัดหมายในวันที่เลือก</p>
        </td>
    </tr>
@endif
