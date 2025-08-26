@extends('layout')
@section('title', 'Home | EKG-ECHO')
@section('content')

    <div class="d-flex justify-content-center align-items-center m-3">
        <div class="bg-light rounded-pill p-1 d-inline-flex" role="group"
            aria-label="Toggle between appointments and treatments">
            <button type="button" class="btn btn-sm btn-toggle rounded-pill px-3 py-1 me-1 active" id="btn-appointments"
                onclick="togglePage('appointments')">
                การนัด
            </button>
            <button type="button" class="btn btn-sm btn-toggle rounded-pill px-3 py-1" id="btn-treatments"
                onclick="togglePage('treatments')">
                การตรวจ EKG
            </button>
        </div>
    </div>

    <div class="" id="main-content"></div>

    @push('script')
    <script src="{{ asset('js/formManagement.js') }}"></script>
        <script>
            // เรียกฟังก์ชันทันทีตอนโหลดหน้า (สำหรับตั้งค่าครั้งแรก)  
            document.addEventListener("DOMContentLoaded", function() {
                togglePatientFields();

            });

            // โหลดหน้าแรกเป็นตารางนัด
            loadPage('appointments');

            // Event Handlers
            if (typeof $ !== "undefined") {
                // jQuery version
                $(document).ready(function() {

                    $(document).on("click", ".print-btn", function() {
                        console.log("Print button clicked");
                        const patientId = $(this).data("id");
                        console.log("Patient ID:", patientId);
                        generatePDFInNewTab(patientId);
                    });
                });
            } else {
                // Vanilla JS version
                document.addEventListener("DOMContentLoaded", function() {
                    document.addEventListener("click", function(e) {
                        if (
                            e.target.classList.contains("print-btn") ||
                            e.target.closest(".print-btn")
                        ) {
                            console.log("Print button clicked");
                            const button = e.target.classList.contains("print-btn") ?
                                e.target :
                                e.target.closest(".print-btn");
                            const patientId = button.dataset.id;
                            console.log("Patient ID:", patientId);
                            generatePDFInNewTab(patientId);
                        }
                    });
                });
            }
        </script>
    @endpush

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    {{-- Alert message --}}
    @if (session('message'))
        @php
            $message = session('message');
        @endphp

        @if ($message['status'] == 1)
            <script>
                Swal.fire({
                    title: '{{ $message['title'] }}',
                    text: '{{ $message['message'] }}',
                    icon: 'success',
                    confirmButtonText: 'ตกลง'
                });
            </script>
        @else
            <script>
                Swal.fire({
                    title: '{{ $message['title'] }}',
                    text: '{{ $message['message'] }}',
                    icon: 'error',
                    confirmButtonText: 'ตกลง'
                });
            </script>
        @endif


    @endif
@endsection
