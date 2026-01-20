@extends('layout')
@section('title', 'Admin - จัดการผู้ใช้และสิทธิ์')

@section('content')
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
        @if (session('success'))
            <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive"
                aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive"
                aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                        aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>

    <div class="container-fluid py-4">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('index') }}" class="text-decoration-none">หน้าแรก</a></li>
                <li class="breadcrumb-item active">จัดการผู้ใช้และสิทธิ์</li>
            </ol>
        </nav>

        {{-- Page Header --}}
        <div class="d-flex justify-content-between align-items-center mb-4 text-theme">
            <div>
                <h2 class="fw-bold mb-1">
                    <i class="fas fa-user-shield me-2 text-teal"></i>User management
                </h2>
                <p class="text-muted mb-0">จัดการบัญชีผู้ใช้ สิทธิ์ และการเข้าถึงระบบ</p>
            </div>
            <div>
                <button class="btn btn-teal shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                    <i class="fa fa-plus-circle me-1"></i> เพิ่มสิทธิ์ใหม่
                </button>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="row g-3 mb-5">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-teal-light rounded-circle p-3 me-3 text-teal">
                                <i class="fas fa-users fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">ผู้ใช้ทั้งหมด</h6>
                                <h3 class="fw-bold mb-0">{{ count($users) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 rounded-circle p-3 me-3 text-primary">
                                <i class="fas fa-user-check fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">แอดมิน</h6>
                                <h3 class="fw-bold mb-0">{{ $users->where('role_name', 'Admin')->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-success bg-opacity-10 rounded-circle p-3 me-3 text-success">
                                <i class="fas fa-user-tag fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">เจ้าหน้าที่ทั่วไป</h6>
                                <h3 class="fw-bold mb-0">{{ $users->where('role_name', 'User')->count() }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-warning bg-opacity-10 rounded-circle p-3 me-3 text-warning">
                                <i class="fas fa-shield-alt fa-lg"></i>
                            </div>
                            <div>
                                <h6 class="text-muted mb-0">ประเภทสิทธิ์</h6>
                                <h3 class="fw-bold mb-0">{{ count($roles) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- User Table Section --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
                        <h5 class="fw-bold mb-0 text-dark">รายชื่อผู้ใช้เข้าระบบ</h5>
                        <div class="input-group w-50 shadow-sm rounded-pill overflow-hidden border">
                            <span class="input-group-text bg-white border-0 ps-3">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="search" name="search" class="form-control border-0 py-2 ps-1"
                                placeholder="ค้นหาชื่อ หรือ Username..." value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="user-result">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light text-muted small text-uppercase">
                                        <tr>
                                            <th class="ps-4 py-3">ข้อมูลผู้ใช้</th>
                                            <th class="py-3">ตำแหน่ง</th>
                                            <th class="py-3">สิทธิ์การใช้งาน</th>
                                            <th class="py-3 text-center">สถานะ</th>
                                            <th class="py-3 text-end pe-4">การจัดการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($users as $user)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm rounded-circle bg-teal-light text-teal d-flex align-items-center justify-content-center fw-bold me-3"
                                                            style="width: 40px; height: 40px;">
                                                            {{ mb_substr($user->fname, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark">
                                                                {{ $user->tname . $user->fname . ' ' . $user->lname }}</div>
                                                            <div class="small text-muted">{{ $user->username }}</div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="small fw-semibold text-secondary">{{ $user->position }}
                                                    </div>
                                                    <div class="small text-muted opacity-75">{{ $user->position2 }}</div>
                                                </td>
                                                <td>
                                                    @if ($user->role_id === null)
                                                        <span
                                                            class="badge rounded-pill bg-danger-soft text-danger px-3 py-2 border border-danger border-opacity-10">
                                                            <i class="fas fa-times-circle me-1"></i>ไม่มีสิทธิ์
                                                        </span>
                                                    @elseif($user->role_name == 'Admin')
                                                        <span
                                                            class="badge rounded-pill bg-indigo-soft text-indigo px-3 py-2 border border-indigo border-opacity-10">
                                                            <i class="fas fa-user-shield me-1"></i>{{ $user->role_name }}
                                                        </span>
                                                    @elseif($user->role_name == 'User')
                                                        <span
                                                            class="badge rounded-pill bg-teal-soft text-teal px-3 py-2 border border-teal border-opacity-10">
                                                            <i class="fas fa-user me-1"></i>{{ $user->role_name }}
                                                        </span>
                                                    @else
                                                        <span
                                                            class="badge rounded-pill bg-blue-soft text-blue px-3 py-2 border border-blue border-opacity-10">
                                                            <i class="fas fa-tag me-1"></i>{{ $user->role_name }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($user->role_name == 'User' || $user->role_name == 'Admin')
                                                        <span class="small text-success fw-bold">
                                                            <i class="fas fa-circle me-1"
                                                                style="font-size: 8px;"></i>พร้อมใช้งาน
                                                        </span>
                                                    @else
                                                        <span class="small text-muted fw-bold">
                                                            <i class="fas fa-circle me-1 text-secondary opacity-50"
                                                                style="font-size: 8px;"></i>ระงับใช้งาน
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-4">
                                                    <button class="btn btn-sm btn-outline-teal hover-teal open-role-modal"
                                                        data-username="{{ trim($user->username) }}"
                                                        data-userid="{{ trim($user->userid) }}"
                                                        data-current-role="{{ $user->role_name }}"
                                                        data-role-id="{{ $user->role_id }}"
                                                        data-initial="{{ mb_substr($user->fname, 0, 1) }}">
                                                        <i class="fa fa-cog me-1"></i> ตั้งค่า
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-5">
                                                    <div class="text-muted">
                                                        <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                                                        <p>ไม่พบรายชื่อผู้ใช้ในระบบ</p>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Roles Section --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm border-top border-teal border-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="fw-bold mb-0 text-dark">รายการสิทธิ์ในระบบ</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small text-uppercase text-center">
                                    <tr>
                                        <th class="py-3">ชื่อสิทธิ์</th>
                                        <th class="py-3">ผู้ใช้งาน</th>
                                        <th class="py-3">จัดการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($roles as $role)
                                        <tr>
                                            <td class="ps-4">
                                                @php
                                                    $roleColorClass = 'text-blue';
                                                    if ($role->name == 'Admin') {
                                                        $roleColorClass = 'text-indigo';
                                                    } elseif ($role->name == 'User') {
                                                        $roleColorClass = 'text-teal';
                                                    }
                                                @endphp
                                                <span class="fw-bold {{ $roleColorClass }}"><i
                                                        class="fas fa-tag me-2"></i>{{ $role->name }}</span>
                                            </td>
                                            <td class="text-center small">
                                                <span class="badge rounded-pill bg-light text-dark border">
                                                    {{ $users->where('role_id', $role->id)->count() }} ท่าน
                                                </span>
                                            </td>
                                            <td class="text-center pe-3">
                                                <button class="btn btn-link link-danger p-0 delete-role-btn"
                                                    data-role-id="{{ $role->id }}">
                                                    <i class="fa fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted small">
                                                ยังไม่มีรายการสิทธิ์</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal กำหนดสิทธิ์ -->
    <div class="modal fade" id="setRoleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 bg-teal text-white py-3">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-shield me-2"></i>กำหนดสิทธิ์การใช้งาน</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-4 text-center">
                        <div class="avatar-lg rounded-circle bg-teal-light text-teal d-inline-flex align-items-center justify-content-center fw-bold mb-3 mx-auto"
                            style="width: 80px; height: 80px; font-size: 24px;">
                            <span id="modalInitial"></span>
                        </div>
                        <h5 class="fw-bold text-dark mb-0" id="modalUsername"></h5>
                        <p class="text-muted small">บัญชีผู้ใช้ระบบโรงพยาบาล</p>
                    </div>

                    <form method="POST" id="setRoleForm">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">เลือกประเภทสิทธิ์ที่ต้องการ</label>
                            <select class="form-select border shadow-sm py-2" name="role" id="roleSelect" required>
                                <option value="" disabled selected>-- โปรดเลือกสิทธิ์ --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-teal py-2 shadow-sm">
                                <i class="fa fa-save me-1"></i> บันทึกข้อมูลสิทธิ์
                            </button>
                            <button type="button" class="btn btn-light py-2" data-bs-dismiss="modal">ยกเลิก</button>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light border-0 justify-content-center">
                    <form id="deleteRoleForm" method="POST" class="w-100">
                        @csrf
                        @method('DELETE')
                        <div class="d-flex justify-content-between align-items-center px-2">
                            <span class="small text-muted">ต้องการลบการเข้าถึงหรือไม่?</span>
                            <button type="submit" class="btn btn-outline-danger btn-sm border-0" id="deleteRoleBtn">
                                <i class="fa fa-trash-alt me-1"></i> ลบสิทธิ์ผู้ใช้
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal เพิ่มสิทธิ์ใหม่ -->
    <div class="modal fade" id="addRoleModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form method="POST" action="{{ route('admin.roles.store') }}">
                    @csrf
                    <div class="modal-header border-0 py-3 mt-1">
                        <h5 class="modal-title fw-bold"><i
                                class="fas fa-plus-circle text-teal me-2"></i>สร้างประเภทสิทธิ์ใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4 pt-0">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted text-uppercase">ชื่อสิทธิ์
                                (อังกฤษ/ไทย)</label>
                            <input type="text" class="form-control border shadow-sm py-2" name="name"
                                placeholder="เช่น Doctor, Admin, เจ้าหน้าที่ทั่วไป" required>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light flex-grow-1" data-bs-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-teal flex-grow-1">
                            <i class="fa fa-plus-circle me-1"></i> บันทึก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <style>
        .bg-teal {
            background-color: #14B8A6 !important;
        }

        .text-teal {
            color: #14B8A6 !important;
        }

        .bg-teal-soft {
            background-color: rgba(20, 184, 166, 0.1) !important;
        }

        .bg-danger-soft {
            background-color: rgba(239, 68, 68, 0.1) !important;
        }

        .bg-indigo-soft {
            background-color: rgba(99, 102, 241, 0.1) !important;
        }

        .text-indigo {
            color: #6366f1 !important;
        }

        .border-indigo {
            border-color: #6366f1 !important;
        }

        .bg-blue-soft {
            background-color: rgba(59, 130, 246, 0.1) !important;
        }

        .text-blue {
            color: #3b82f6 !important;
        }

        .border-blue {
            border-color: #3b82f6 !important;
        }

        .btn-outline-teal {
            color: #14B8A6;
            border-color: #14B8A6;
        }

        .btn-outline-teal:hover {
            background-color: #14B8A6;
            color: white;
        }

        .breadcrumb-item a {
            color: #14B8A6;
        }

        .link-danger:hover {
            color: #dc3545 !important;
        }
    </style>

@endsection

@push('pmScript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));
            var toastList = toastElList.map(function(toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show();
                return toast;
            });
        });

        let pmSearchTimeout;
        let pmCurrentRequest;
        let roleModal;

        // สร้าง Modal instance เพียงครั้งเดียว
        $(document).ready(function() {
            const modalEl = document.getElementById('setRoleModal');
            if (modalEl) {
                roleModal = new bootstrap.Modal(modalEl);
            }
        });

        $(document).on('input', 'input[name="search"]', function() {
            clearTimeout(pmSearchTimeout);
            const searchValue = $(this).val().trim();

            pmSearchTimeout = setTimeout(() => {
                if (pmCurrentRequest) pmCurrentRequest.abort();

                let url = (searchValue.length > 0) ? "{{ route('admin.findUser') }}" :
                    "{{ route('admin') }}";

                pmCurrentRequest = $.get(url, {
                    search: searchValue
                }, function(data) {
                    $('.user-result').html($(data).find('.user-result').html());
                });
            }, 500);
        });

        // เปิด Modal พร้อม populate ข้อมูล
        $(document).on('click', '.open-role-modal', function() {
            const username = $(this).data('username');
            const currentRole = $(this).data('current-role');
            const roleId = $(this).data('role-id');
            const initial = $(this).data('initial');
            const userFullName = $(this).closest('tr').find('.fw-bold.text-dark').text().trim();

            // อัพเดตข้อมูลใน Modal
            $('#modalUsername').text(userFullName);
            $('#modalInitial').text(initial);
            $('#setRoleForm').attr('action', "{{ url('admin/users') }}/" + username + "/set-role");
            $('#deleteRoleForm').attr('action', "{{ url('admin/users') }}/" + username + "/destroy");

            // เลือก role ปัจจุบัน
            $('#roleSelect option').prop('selected', false);
            $('#roleSelect option').each(function() {
                if ($(this).text().trim() === currentRole) {
                    $(this).prop('selected', true);
                }
            });

            // ปิด/เปิดปุ่มลบตามสถานะ
            if (!roleId || roleId === 'null') {
                $('#deleteRoleBtn').prop('disabled', true);
                $('#deleteRoleBtn').addClass('opacity-50');
            } else {
                $('#deleteRoleBtn').prop('disabled', false);
                $('#deleteRoleBtn').removeClass('opacity-50');
            }

            // เปิด Modal
            roleModal.show();
        });

        function createToast(message, type = 'success') {
            const icon = type === 'success' ? 'check-circle' : 'exclamation-triangle';
            const toastHtml = `
                <div class="toast align-items-center text-bg-${type} border-0 shadow-lg" role="alert" aria-live="assertive"
                    aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${icon} me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                aria-label="Close"></button>
                    </div>
                </div>
            `;
            $('.toast-container').append(toastHtml);
            const newToastEl = $('.toast').last()[0];
            const toast = new bootstrap.Toast(newToastEl);
            toast.show();
            newToastEl.addEventListener('hidden.bs.toast', function() {
                $(this).remove();
            });
        }

        $(document).on('submit', '#setRoleForm', function(e) {
            e.preventDefault();
            const form = $(this);

            $.post(form.attr('action'), form.serialize(), function(response) {
                createToast(response.success || 'กำหนดสิทธิ์สำเร็จ', 'success');
                roleModal.hide();
                $('input[name="search"]').trigger('input');
            }).fail(function(xhr) {
                createToast(xhr.responseJSON?.message || 'เกิดข้อผิดพลาด', 'danger');
            });
        });

        $(document).on('submit', '#deleteRoleForm', function(e) {
            e.preventDefault();
            if (!confirm('ยืนยันการลบสิทธิ์ผู้ใช้นี้?')) return;

            const form = $(this);

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    createToast('ลบสิทธิ์สำเร็จ', 'success');
                    roleModal.hide();
                    $('input[name="search"]').trigger('input');
                },
                error: function(xhr) {
                    createToast('เกิดข้อผิดพลาดในการลบ', 'danger');
                }
            });
        });

        $(document).on('click', '.delete-role-btn', function() {
            const roleId = $(this).data('role-id');
            if (confirm('ยืนยันการลบสิทธิ์นี้?')) {
                $.ajax({
                    url: "{{ url('admin/roles/destroy') }}/" + roleId,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function(response) {
                        createToast('ลบสิทธิ์สำเร็จ', 'success');
                        location.reload();
                    },
                    error: function(xhr) {
                        createToast(xhr.responseJSON?.message || 'เกิดข้อผิดพลาดในการลบสิทธิ์',
                            'danger');
                    }
                });
            }
        });
    </script>
@endpush
