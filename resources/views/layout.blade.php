<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>

    {{-- flatpickr --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/themes/material_green.css">

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/air-datepicker@3.3.5/air-datepicker.min.js"></script>

    <!-- สำหรับ Font Awesome 5 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    {{-- select2/bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- favicon --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <style>
        .btn-toggle {
            background-color: #e9ecef;
            color: #495057;
            border: none;
            transition: 0.3s;
        }

        .btn-toggle.active {
            background-color: #14B8A6;
            color: white;
        }

        .btn-toggle:hover {
            background-color: #adb5bd;
            color: white;
        }

        .modal-dialog-scrollable .modal-body {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }

        #doctorSuggestions {
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
        }

        .custom-teal {
            background-color: #14B8A6;
        }

        .btn-teal {
            background-color: #14B8A6;
            /* teal-500 */
            color: #fff;
            border: none;
        }

        .btn-teal:hover {
            background-color: #0f766e;
            /* teal-700 */
            color: #fff;
        }

        .pagination .page-link {
            color: #14B8A6;
        }

        .pagination .page-link:hover {
            background-color: #ccfbf1;
            color: #0f766e;
        }

        .pagination .active .page-link {
            background-color: #14B8A6;
            border-color: #14B8A6;
            color: white;
        }


        .page-item.active .page-link:disabled {
            color: white;
            cursor: not-allowed;
        }



        /* CSS สำหรับแสดงการแจ้งเตือนประวัติการนัด */
        #appointment-alert {
            border-left: 4px solid;
            border-radius: 6px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            animation: slideDown 0.3s ease-out;
        }

        #appointment-alert.alert-warning {
            border-left-color: #f0ad4e;
            background-color: #fcf8e3;
            color: #8a6d3b;
        }

        #appointment-alert.alert-info {
            border-left-color: #5bc0de;
            background-color: #d9edf7;
            color: #31708f;
        }

        #appointment-alert.alert-danger {
            border-left-color: #d9534f;
            background-color: #f2dede;
            color: #a94442;
        }

        #appointment-alert .fas {
            color: inherit;
            opacity: 0.8;
        }

        @keyframes slideDown {
            0% {
                opacity: 0;
                transform: translateY(-10px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ปรับแต่ง input fields */
        .form-control.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }

        .invalid-feedback {
            display: block;
            width: 100%;
            margin-top: 0.25rem;
            font-size: 0.875em;
            color: #dc3545;
        }

        /* Animation สำหรับการ enable/disable fields */
        .form-control:disabled {
            background-color: #f8f9fa;
            opacity: 0.6;
            transition: all 0.2s ease;
        }

        .form-control:not(:disabled) {
            transition: all 0.2s ease;
        }

        /* ปุ่ม radio ที่ disabled */
        input[type="radio"]:disabled+label {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Select2 disabled state */
        .select2-container--disabled .select2-selection {
            background-color: #f8f9fa !important;
            opacity: 0.6;
            cursor: default !important;
        }

        select.form-select:disabled {
            background-color: #f8f9fa !important;
            opacity: 0.6;
            color: #6c757d;
            /* ตัวหนังสือสีเทาอ่อน */
            cursor: default;
        }


        /* Loading state สำหรับ HN input */
        .hn-loading {
            position: relative;
        }

        .hn-loading::after {
            content: '';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: translateY(-50%) rotate(0deg);
            }

            100% {
                transform: translateY(-50%) rotate(360deg);
            }
        }

        /* เพิ่ม CSS สำหรับ PDF */
        .pdf-preview-container {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .printable-content {
            background: white;
            padding: 20px;
            font-family: 'Sarabun', sans-serif;
        }

        .printable-content .form-label {
            color: #333;
            margin-bottom: 5px;
        }

        .printable-content .form-control {
            border: 1px solid #ddd;
            background-color: #f8f9fa;
        }

        /* สำหรับ print preview */
        @media print {
            .printable-content {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark custom-teal">
        <div class="container-fluid">
            <a class="navbar-brand" href="/">EKG-ECHO</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false"
                aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse " id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link  {{ Route::currentRouteName() == 'app.show' ? 'active' : '' }}"
                            aria-current="page" href="/">หน้าแรก</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link  {{ Route::currentRouteName() == 'dashboard.show' || Route::currentRouteName() == 'report.show' ? 'active' : '' }}"
                            aria-current="page" href="{{ route('dashboard.show') }}">แดชบอร์ด</a>
                    </li>

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            ตัวเลือก
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">ตัวเลือก 1</a></li>
                            <li><a class="dropdown-item" href="#">ตัวเลือก 2</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="#">ออกจากระบบ</a></li>
                        </ul>
                    </li>
                </ul>

            </div>
        </div>
    </nav>
    <div class="container">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/l10n/th.js"></script>

    <!-- เพิ่ม JavaScript สำหรับ PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    {{-- jquery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- select2 js --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/loadPage.js') }}"></script>
    <script scr="{{ asset('js/appointCheck.js') }}"></script>
    <script src="{{ asset('js/search.js') }}"></script>
    <script src="{{ asset('js/cursor.js') }}"></script>
    <script src="{{ asset('js/printer.js') }}"></script>
    <script src="{{ asset('js/flatpickr.js') }}"></script>

    @stack('script')

    @stack('pmScript')





    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js"
        integrity="sha384-VQqxDN0EQCkWoxt/0vsQvZswzTHUVOImccYmSyhJTp7kGtPed0Qcx8rK9h9YEgx+" crossorigin="anonymous">
    </script>

</body>

</html>
