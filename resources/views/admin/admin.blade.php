@extends('layout')
@section('title', 'Admin - จัดการผู้ใช้และสิทธิ์')

@section('content')
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
        <!-- Toast สำหรับข้อความสำเร็จ -->
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

        <!-- Toast สำหรับข้อผิดพลาด -->
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

    <div class="my-4">

        <!-- ====================== 👤 จัดการผู้ใช้ ====================== -->
        <h2 class="text-center mb-4">จัดการผู้ใช้</h2>

        <!-- 🔍 ค้นหาผู้ใช้ -->
        <div class="row mb-4">
            <div class="col-md-6 offset-md-3">
                <div class="input-group">
                    <input type="search" name="search" class="form-control rounded-start"
                        placeholder="พิมพ์ชื่อ หรือ Username" value="{{ request('search') }}">
                </div>
            </div>
        </div>

        <!-- ตารางผู้ใช้ -->
        <div class="card shadow border-0 mb-5">
            <div class="card-body">
                <h5 class="card-title">รายชื่อผู้ใช้</h5>
                <div class="user-result">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light text-center">
                                <tr>
                                    <th>ชื่อ-สกุล</th>
                                    <th>ชื่อผู้ใช้</th>
                                    <th>ตำแหน่ง</th>
                                    <th>สิทธิ์ปัจจุบัน</th>
                                    <th>สถานะ</th>
                                    <th>การจัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td>{{ $user->tname . ' ' . $user->fname . ' ' . $user->lname }}</td>
                                        <td>{{ $user->username }}</td>
                                        <td>{{ $user->position . $user->position2}}</td>
                                        <td>
                                            @if ($user->role_id === null)
                                                <span class="badge bg-danger">ไม่มีสิทธิ์</span>
                                            @else
                                                <span class="badge bg-success">{{ $user->role_name }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($user->role_name == 'User' || $user->role_name == 'Admin')
                                                <span class="badge bg-success">ใช้งาน</span>
                                            @else
                                                <span class="badge bg-secondary">ปิดใช้งาน</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <!-- กำหนดสิทธิ์ -->
                                            <button class="btn btn-sm btn-primary mb-1" data-bs-toggle="modal"
                                                data-bs-target="#setRoleModal{{ $user->userid }}">
                                                <i class="fa fa-user-shield"></i> กำหนดสิทธิ์
                                            </button>

                                            <!-- Modal กำหนดสิทธิ์ -->
                                            <div class="modal fade" id="setRoleModal{{ $user->userid }}" tabindex="-1">
                                                <div class="modal-dialog">
                                                    <div class="modal-content shadow-lg border-0 rounded-3">
                                                        <div class="modal-header bg-primary text-white">
                                                            <h5 class="modal-title">กำหนดสิทธิ์ให้ {{ $user->username }}
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>

                                                        <div class="modal-body">
                                                            <!-- ฟอร์มกำหนดสิทธิ์ -->
                                                            <form method="POST"
                                                                action="{{ route('admin.users.setRole', $user->username) }}">
                                                                @csrf
                                                                <div class="mb-3">
                                                                    <label for="role"
                                                                        class="form-label">เลือกสิทธิ์</label>
                                                                    <select class="form-select" name="role" required>
                                                                        <option value="" disabled selected>--
                                                                            เลือกสิทธิ์ --</option>
                                                                        @foreach ($roles as $role)
                                                                            <option value="{{ $role->id }}"
                                                                                {{ $user->role_name == $role->name ? 'selected' : '' }}>
                                                                                {{ $role->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <div class="text-end">
                                                                    <button type="submit" class="btn btn-primary">
                                                                        <i class="fa fa-save"></i> บันทึก
                                                                    </button>
                                                                    <button type="button" class="btn btn-secondary"
                                                                        data-bs-dismiss="modal">ยกเลิก</button>
                                                                </div>
                                                            </form>
                                                        </div>

                                                        <!-- เส้นคั่น -->
                                                        <div class="border-top"></div>

                                                        <!-- ฟอร์มลบสิทธิ์ -->
                                                        <div class="modal-body">
                                                            <form
                                                                action="{{ route('admin.users.destroy', $user->username) }}"
                                                                method="POST" class="d-inline delete-form">
                                                                @csrf
                                                                @method('DELETE')
                                                                <div
                                                                    class="d-flex justify-content-between align-items-center">
                                                                    <button type="submit" class="btn btn-danger btn-sm"
                                                                        {{ $user->role_name == null ? 'disabled' : '' }}>
                                                                        <i class="fa fa-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">ไม่มีผู้ใช้ในระบบ</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>



        <!-- ====================== 🔑 จัดการสิทธิ์ ====================== -->
        <h2 class="text-center mb-4">จัดการสิทธิ์</h2>

        <div class="card shadow border-0">
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <h5 class="card-title">รายการสิทธิ์</h5>
                    <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                        <i class="fa fa-plus"></i> เพิ่มสิทธิ์ใหม่
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle text-center">
                        <thead class="table-light">
                            <tr>
                                <th>ชื่อสิทธิ์</th>
                                <th>การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                                <tr>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                        <!-- ลบ -->
                                        <button class="btn btn-sm btn-danger delete-role-btn"
                                            data-role-id="{{ $role->id }}">
                                            <i class="fa fa-trash"></i> ลบ
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">ไม่มีสิทธิ์ในระบบ</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: เพิ่มสิทธิ์ใหม่ -->
    <div class="modal fade" id="addRoleModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">เพิ่มสิทธิ์ใหม่</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">ชื่อสิทธิ์</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                        <button type="submit" class="btn btn-success">บันทึก</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@endsection


@push('pmScript')
    <script>
        // แสดง Toast notifications อัตโนมัติเมื่อหน้าโหลด
        document.addEventListener('DOMContentLoaded', function() {
            // เลือก toast ทั้งหมด
            var toastElList = [].slice.call(document.querySelectorAll('.toast'));

            // แสดง toast แต่ละอัน
            var toastList = toastElList.map(function(toastEl) {
                var toast = new bootstrap.Toast(toastEl);
                toast.show(); // แสดง toast
                return toast;
            });
        });

        let pmSearchTimeout;

        $('input[name="search"]').on('input', function() {
            clearTimeout(pmSearchTimeout);
            const searchValue = $(this).val().trim();

            pmSearchTimeout = setTimeout(() => {
                let url;

                if (searchValue.length > 0) {
                    // ถ้ามีข้อความ -> ค้นหา
                    url = "{{ route('admin.findUser') }}";
                } else {
                    // ถ้าว่าง -> โหลด route หลัก
                    url = "{{ route('admin') }}";
                }

                $.get(url, {
                    search: searchValue
                }, function(data) {
                    $('.user-result').html($(data).find('.user-result').html());

                    // รีเฟรช modal
                    $('.modal').modal('dispose');
                    $('.modal').modal();
                });
            }, 500);
        });

        // ฟังก์ชันสำหรับสร้าง Toast
        function createToast(message, type = 'success') {
            const toastHtml = `
                <div class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive"
                    aria-atomic="true" data-bs-autohide="true" data-bs-delay="5000">
                    <div class="d-flex">
                        <div class="toast-body">
                            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                                aria-label="Close"></button>
                    </div>
                </div>
            `;

            // เพิ่ม toast ใหม่ลงใน container
            $('.toast-container').append(toastHtml);

            // แสดง toast ที่เพิ่งสร้าง
            const newToast = $('.toast').last();
            const toast = new bootstrap.Toast(newToast[0]);
            toast.show();

            // ลบ toast หลังจากซ่อน
            newToast.on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }

        // ใช้ event delegation สำหรับ form submit
        $(document).on('submit', 'form[action*="set-role"]', function(e) {
            e.preventDefault();

            const form = $(this);
            const formData = form.serialize();
            const url = form.attr('action');

            $.post(url, formData, function(response) {
                // ใช้ Toast แทน alert
                createToast(response.success || 'กำหนดสิทธิ์สำเร็จ', 'success');

                // ปิด modal
                form.closest('.modal').modal('hide');

                // รีเฟรชผลการค้นหา
                const searchValue = $('input[name="search"]').val();
                if (searchValue || searchValue === '') {
                    $('input[name="search"]').trigger('input');
                }
            }).fail(function(xhr) {
                let errorMessage = 'เกิดข้อผิดพลาด';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }

                // ใช้ Toast แทน alert
                createToast(errorMessage, 'danger');
            });
        });

        // ใช้ event delegation สำหรับ form submit ลบผู้ใช้
        $(document).on('submit', 'form[action*="destroy"]', function(e) {
            e.preventDefault();

            if (!confirm('ยืนยันการลบผู้ใช้นี้?')) {
                return;
            }

            const form = $(this);
            const formData = form.serialize();
            const url = form.attr('action');

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                success: function(response) {
                    // ใช้ Toast แทน alert
                    createToast(response.success || 'ลบผู้ใช้สำเร็จ', 'success');

                    // ปิด modal
                    form.closest('.modal').modal('hide');

                    // รีเฟรชผลการค้นหา
                    const searchValue = $('input[name="search"]').val();
                    if (searchValue || searchValue === '') {
                        $('input[name="search"]').trigger('input');
                    }
                },
                error: function(xhr) {
                    let errorMessage = 'เกิดข้อผิดพลาดในการลบ';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    // ใช้ Toast แทน alert
                    createToast(errorMessage, 'danger');
                }
            });
        });

        // ลบสิทธิ์
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

                        // รีเฟรชหน้า
                        location.reload();
                    },
                    error: function(xhr) {
                        let errorMessage = 'เกิดข้อผิดพลาดในการลบสิทธิ์';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        createToast(errorMessage, 'danger');
                    }
                });
            }
        });
    </script>
@endpush
