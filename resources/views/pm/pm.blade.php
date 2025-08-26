@extends('layout')
@section('title', 'PM')
@section('content')
    <div class="container py-4" id="pm-content" style="display:none;">
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <div class="card shadow border-0">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-3">ค้นหา PM</h2>
                        <div class="input-group input-group">
                            <input type="search" name="search" class="form-control rounded-start"
                                placeholder="พิมพ์ชื่อ, รหัส หรือ Username เพื่อค้นหา..." value="{{ request('search') }}">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="pm-result col-md-10">
                @if ($allPm->isEmpty())
                    <div class="alert alert-warning text-center shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> ไม่พบ PM ที่ตรงกับคำค้นหา
                    </div>
                @else
                    <div class="table-responsive shadow-sm rounded">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th scope="col">บัตรประชาชน</th>
                                    <th scope="col">ชื่อ-สกุล</th>
                                    <th scope="col">ชื่อผู้ใช้</th>
                                    <th scope="col">ตำแหน่ง</th>
                                    <th scope="col">กลุ่มงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allPm as $pm)
                                    <tr>
                                        <td class="fw-bold">{{ $pm->cid }}</td>
                                        <td>{{ $pm->tname . ' ' . $pm->fname . ' ' . $pm->lname }}</td>
                                        <td><span class="badge bg-info text-dark">{{ $pm->username }}</span></td>
                                        <td>{{ $pm->position }}</td>
                                        <td>{{ $pm->department }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal PIN -->
    <div class="modal fade" id="pinModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="pinModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow">
                <div class="modal-header">
                    <h5 class="modal-title w-100 text-center">กรอกรหัส PIN</h5>
                </div>
                <div class="modal-body">
                    <input type="password" id="pinInput" class="form-control text-center" placeholder="ใส่รหัส PIN">
                    <div id="pinError" class="text-danger text-center mt-2" style="display:none;">
                        PIN ไม่ถูกต้อง ลองใหม่อีกครั้ง
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-primary" id="checkPinBtn">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('pmScript')
    <script src="{{ asset('js/pin.js') }}"></script>
    <script>
        let pmSearchTimeout;
        $('input[name="search"]').on('input', function() {
            clearTimeout(pmSearchTimeout);
            const searchValue = $(this).val().trim();
            pmSearchTimeout = setTimeout(() => {
                $.get("{{ route('pm_search') }}", {
                    search: searchValue
                }, function(data) {
                    $('.pm-result').html($(data).find('.pm-result').html());
                });
            }, 700);
        });
    </script>
@endpush
