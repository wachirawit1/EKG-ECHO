{{-- resources/views/components/pagination.blade.php --}}
@props(['page', 'totalPages'])

@if ($totalPages > 1 || $totalPages == 1)
    <div class="fixed-bottom bg-white border-top shadow-sm py-2" >
        <nav class="d-flex justify-content-center" aria-label="Page navigation">
            <ul class="pagination mb-0">
                <li class="page-item {{ $page == 1 ? 'disabled' : '' }}">
                    <button class="page-link page-btn" data-page="{{ $page - 1 }}"
                        {{ $page == 1 ? 'disabled' : '' }}>ก่อนหน้า</button>
                </li>

                @php
                    $start = max(1, $page - 2);
                    $end = min($totalPages, $page + 2);
                @endphp

                @if ($start > 1)
                    <li class="page-item"><button class="page-link page-btn" data-page="1">1</button></li>
                    @if ($start > 2)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                @endif

                @for ($i = $start; $i <= $end; $i++)
                    <li class="page-item {{ $page == $i ? 'active' : '' }}">
                        <button class="page-link page-btn" data-page="{{ $i }}">{{ $i }}</button>
                    </li>
                @endfor

                @if ($end < $totalPages)
                    @if ($end < $totalPages - 1)
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    @endif
                    <li class="page-item"><button class="page-link page-btn"
                            data-page="{{ $totalPages }}">{{ $totalPages }}</button></li>
                @endif

                <li class="page-item {{ $page == $totalPages ? 'disabled' : '' }}">
                    <button class="page-link page-btn" data-page="{{ $page + 1 }}"
                        {{ $page == $totalPages ? 'disabled' : '' }}>ถัดไป</button>
                </li>
            </ul>
        </nav>
    </div>
@endif
