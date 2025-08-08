@extends('layout')
@section('title', 'Test finding PM | EKG-ECHO')
@section('content')
    <div class="container py-4">
        <div class="row justify-content-center mb-4">
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h2 class="card-title text-center mb-3" style="font-weight: bold; color: #007bff;">
                            <i class="bi bi-search"></i> ค้นหา PM
                        </h2>
                        <form action="{{ route('pm_search') }}" method="GET" autocomplete="off">
                            <div class="input-group input-group-lg">
                                <input type="search" name="search" class="form-control rounded-start"
                                    placeholder="🔍 พิมพ์ชื่อ, รหัส หรือ Username เพื่อค้นหา..."
                                    value="{{ request('search') }}">
                                <button class="btn btn-primary rounded-end" type="submit">
                                    <i class="bi bi-search"></i> ค้นหา
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                @if ($allPm->isEmpty())
                    <div class="alert alert-warning text-center shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> ไม่พบ PM ที่ตรงกับคำค้นหา
                    </div>
                @else
                    <div class="table-responsive shadow-sm rounded">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-primary">
                                <tr>
                                    <th scope="col">รหัส PM</th>
                                    <th scope="col">ชื่อ-สกุล</th>
                                    <th scope="col">Username</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allPm as $pm)
                                    <tr>
                                        <td class="fw-bold">{{ $pm->cid }}</td>
                                        <td>{{ $pm->tname . ' ' . $pm->fname . ' ' . $pm->lname }}</td>
                                        <td><span class="badge bg-info text-dark">{{ $pm->username }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('pmScript')
    <script>
        let pmSearchTimeout;
        $('input[name="search"]').on('input', function() {
            clearTimeout(pmSearchTimeout);
            const searchValue = $(this).val().trim();
            pmSearchTimeout = setTimeout(() => {
                $.get("{{ route('pm_search') }}", {
                    search: searchValue
                }, function(data) {
                    // สมมติว่าคุณ render เฉพาะ table ใน response
                    $('.table-responsive').html($(data).find('.table-responsive').html());
                });
            }, 700);
        });
    </script>
@endpush
