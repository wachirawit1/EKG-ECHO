{{-- resources/views/components/pagination.blade.php --}}
@props(['page', 'totalPages', 'startNum' , 'endNum' ,'total'])

@if ($totalPages > 1 || $totalPages == 1)
    <!-- Pagination Container -->
    <div class="mt-4 mb-3">
        <div class="row align-items-center">
            <!-- แสดงจำนวนรายการ -->
            <div class="col-12 col-md-6 mb-2 mb-md-0">
                <small class="text-muted">
                    แสดง {{ number_format($startNum) }} - {{ number_format($endNum) }} 
                    จากทั้งหมด {{ number_format($total) }} รายการ
                </small>
            </div>
            
            <!-- Pagination -->
            <div class="col-12 col-md-6">
                <nav aria-label="Page navigation" class="d-flex justify-content-md-end justify-content-center">
                    <ul class="pagination pagination-sm mb-0">
                        <!-- Previous Button -->
                        <li class="page-item {{ $page == 1 ? 'disabled' : '' }}">
                            <button class="page-link page-btn" data-page="{{ $page - 1 }}"
                                {{ $page == 1 ? 'disabled' : '' }}>
                                <i class="bi bi-chevron-left"></i>
                            </button>
                        </li>

                        @php
                            $start = max(1, $page - 2);
                            $end = min($totalPages, $page + 2);
                        @endphp

                        <!-- First Page -->
                        @if ($start > 1)
                            <li class="page-item">
                                <button class="page-link page-btn" data-page="1">1</button>
                            </li>
                            @if ($start > 3)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                        @endif

                        <!-- Page Numbers -->
                        @for ($i = $start; $i <= $end; $i++)
                            <li class="page-item {{ $page == $i ? 'active' : '' }}">
                                @if ($page == $i)
                                    <span class="page-link">{{ $i }}</span>
                                @else
                                    <button class="page-link page-btn" data-page="{{ $i }}">{{ $i }}</button>
                                @endif
                            </li>
                        @endfor

                        <!-- Last Page -->
                        @if ($end < $totalPages)
                            @if ($end < $totalPages - 2)
                                <li class="page-item disabled">
                                    <span class="page-link">...</span>
                                </li>
                            @endif
                            <li class="page-item">
                                <button class="page-link page-btn" data-page="{{ $totalPages }}">{{ $totalPages }}</button>
                            </li>
                        @endif

                        <!-- Next Button -->
                        <li class="page-item {{ $page == $totalPages ? 'disabled' : '' }}">
                            <button class="page-link page-btn" data-page="{{ $page + 1 }}"
                                {{ $page == $totalPages ? 'disabled' : '' }}>
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
@endif