<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>


    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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

                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            Dropdown
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#">Action</a></li>
                            <li><a class="dropdown-item" href="#">Another action</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" href="#">Something else here</a></li>
                        </ul>
                    </li>
                </ul>

            </div>
        </div>
    </nav>
    <div class="container">
        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.5/dist/js/bootstrap.min.js"
        integrity="sha384-VQqxDN0EQCkWoxt/0vsQvZswzTHUVOImccYmSyhJTp7kGtPed0Qcx8rK9h9YEgx+" crossorigin="anonymous">
    </script>
</body>

</html>
