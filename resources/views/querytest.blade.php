@extends('layout')
@section('title', 'Query testing')
@section('content')
    <h1>ทดสอบการดึงข้อมูลผ่าน sql server</h1>
    <table class="table table-bordered table-hover">
        <thead class="text-center">
            <tr>
                <th>#</th>
                <th>รหัสหมอ - ชื่อหมอ</th>
                <th>HN</th>
                <th>วันนัด</th>
                <th>action</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($appoint as $index => $item)
                <tr>
                    <th scope="row">{{ ($appoint->currentPage() - 1) * $appoint->perPage() + $index + 1 }}</th>
                    <td>{{ $item->doctor }} {{ $item->docName ?? '-' }} {{ $item->docLName ?? '-' }}</td>
                    <td>{{ $item->hn }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->appoint_date)->format('d/m/Y H:i') }}</td>
                    <td class="text-center">
                        <div class="btn-group" role="group">
                            <button class="btn btn-primary btn-sm"><i class="bi bi-eye"></i></button>
                            <button class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i></button>
                        </div>
                    </td>
                </tr>
            @endforeach

        </tbody>
    </table>
    {{-- Pagination links --}}
    <div class="mt-4">
        {{ $appoint->links('pagination::bootstrap-5') }}
    </div>
@endsection
