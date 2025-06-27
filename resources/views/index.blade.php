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
    <!-- เพิ่ม JavaScript สำหรับ PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    {{-- jquery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- select2 js --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/loadPage.js') }}"></script>
    <script src="{{ asset('js/formManagement.js') }}"></script>
    <script scr="{{ asset('js/appointCheck.js') }}"></script>
    <script src="{{ asset('js/search.js') }}"></script>
    <script src="{{ asset('js/cursor.js') }}"></script>
    <script src="{{ asset('js/printer.js') }}"></script>
    <script>
        // เรียกฟังก์ชันทันทีตอนโหลดหน้า (สำหรับตั้งค่าครั้งแรก)
        document.addEventListener("DOMContentLoaded", function() {
            togglePatientFields();

        });

        // โหลดหน้าแรกเป็นตารางนัด
        loadPage('appointments');

        $(document).ready(function() {
            console.log('PDF Script loaded'); // สำหรับ debug

            // ใช้ event delegation เพื่อให้ทำงานกับ modal ที่โหลดภายหลัง
            $(document).on('click', '.print-btn', function() {
                console.log('Print button clicked'); // debug
                const patientId = $(this).data('id');
                console.log('Patient ID:', patientId); // debug
                generatePDFInNewTab(patientId);
            });

            $(document).on('click', '.download-pdf-btn', function() {
                const patientId = $(this).data('id');
                generatePDF(patientId, true);
            });

            $(document).on('click', '.close-preview-btn', function() {
                const patientId = $(this).data('id');
                $(`#pdfPreview${patientId}`).hide();
            });
        });
    </script>

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
