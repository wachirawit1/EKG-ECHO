@extends('layout_pm')
@section('title', 'ระบบค้นหาข้อมูลบุคลากร (PM Search)')
@section('content')
    <style>
        /* Modern Card Styling */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-radius: 1.5rem;
            transition: transform 0.3s ease;
        }

        /* Search Input Styling */
        .search-input-group {
            background: white;
            border-radius: 50px;
            padding: 5px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid #eef2f5;
            transition: all 0.3s ease;
        }

        .search-input-group:focus-within {
            box-shadow: 0 8px 30px rgba(20, 184, 166, 0.25);
            /* Teal glow */
            border-color: #14B8A6;
            transform: translateY(-2px);
        }

        .search-input {
            border: none;
            box-shadow: none;
            font-size: 1.1rem;
            padding-left: 20px;
            color: #444;
            background: transparent;
        }

        .search-input:focus {
            outline: none;
            box-shadow: none;
        }

        .search-icon {
            color: #14B8A6;
            font-size: 1.2rem;
            padding: 0 15px;
        }

        /* Table Styling */
        .custom-table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .custom-table thead th {
            border: none;
            background: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            color: #8898aa;
            padding-bottom: 10px;
        }

        .custom-table tbody tr {
            background: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
            transition: all 0.2s ease;
            border-radius: 10px;
        }

        .custom-table tbody tr:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            z-index: 1;
        }

        .custom-table td {
            border: none;
            padding: 15px 20px;
            vertical-align: middle;
            font-size: 0.95rem;
            color: #525f7f;
        }

        .custom-table td:first-child {
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
        }

        .custom-table td:last-child {
            border-top-right-radius: 10px;
            border-bottom-right-radius: 10px;
        }

        /* Badge Styling */
        .badge-username {
            background: rgba(20, 184, 166, 0.1);
            /* Teal weak */
            color: #0f766e;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        /* Hero Header */
        .hero-section {
            background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
            padding: 3rem 1rem;
            border-radius: 2rem;
            margin-bottom: 3rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(20, 184, 166, 0.1);
            border-radius: 50%;
        }

        .hero-section::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: -30px;
            width: 150px;
            height: 150px;
            background: rgba(20, 184, 166, 0.15);
            border-radius: 50%;
        }
    </style>

    <div class="container-fluid px-4 py-3" id="pm-content" style="display:none;">

        <!-- Hero Search Section -->
        <div class="hero-section glass-card">
            <h1 class="display-6 fw-bold text-teal-800 mb-2" style="color: #0d9488;">ระบบค้นหาข้อมูลบุคลากร</h1>
            <p class="text-muted mb-4">ค้นหา PM, รหัสพนักงาน, หรือข้อมูลสังกัด ได้อย่างรวดเร็ว</p>

            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="search-input-group d-flex align-items-center">
                        <span class="search-icon"><i class="fas fa-search"></i></span>
                        <input type="search" name="search" class="form-control search-input"
                            placeholder="พิมพ์ชื่อ, รหัส, หรืองาน เพื่อค้นหา..." value="{{ request('search') }}"
                            autocomplete="off" autofocus>
                        <!-- Loading Spinner will be appended here by JS -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Section -->
        <div class="row justify-content-center">
            <div class="col-12 pm-result">
                @if ($allPm->isEmpty())
                    <div class="text-center py-5">
                        <div class="mb-3 text-secondary" style="opacity: 0.3;">
                            <i class="fas fa-folder-open fa-4x"></i>
                        </div>
                        <h5 class="text-muted fw-normal">ไม่พบข้อมูลที่ตรงกับคำค้นหา</h5>
                        <p class="text-muted small">ลองตรวจสอบตัวสะกดหรือใช้คำค้นหาอื่น</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table custom-table">
                            <thead>
                                <tr>
                                    <th scope="col" class="ps-4">รหัสบัตรประชาชน</th>
                                    <th scope="col">ชื่อ-นามสกุล</th>
                                    <th scope="col">บัญชีผู้ใช้ (Username)</th>
                                    <th scope="col">วันเกิด</th>
                                    <th scope="col">ตำแหน่ง</th>
                                    <th scope="col" class="pe-4">หน่วยงาน/กลุ่มงาน</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($allPm as $pm)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center copy-trigger position-relative"
                                                style="cursor: pointer;" data-text="{{ $pm->cid }}"
                                                title="คลิกเพื่อคัดลอก">
                                                <span class="fw-bold text-secondary me-2">{{ $pm->cid }}</span>
                                                <i class="far fa-copy text-muted opacity-50 small copy-icon"></i>
                                            </div>
                                        </td>
                                        <td class="fw-semibold text-dark" style="font-size: 1.05rem;">
                                            {{ $pm->tname . ' ' . $pm->fname . ' ' . $pm->lname }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center copy-trigger position-relative"
                                                style="cursor: pointer;" data-text="{{ $pm->username }}"
                                                title="คลิกเพื่อคัดลอก">
                                                <span class="badge-username me-2">
                                                    <i class="fas fa-user-circle me-1"></i>{{ $pm->username }}
                                                </span>
                                                <i class="far fa-copy text-muted opacity-50 small copy-icon"></i>
                                            </div>
                                        </td>
                                        <td class="text-muted">
                                            <i class="far fa-calendar-alt me-1 text-teal-light"></i>
                                            {{ \App\Helpers\DateHelper::formatThaiDate($pm->birthday, 'medium') }}
                                        </td>
                                        <td>
                                            <span style="min-width: 150px; display: block;">
                                                {{ $pm->position . ($pm->position2 ?? '') }}
                                            </span>
                                        </td>
                                        <td class="pe-4">
                                            <span style="min-width: 180px; display: block;">
                                                {{ $pm->department }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer Credit for this page --}}
                    <div class="text-center mt-4 text-muted small opacity-50">
                        แสดงผล {{ min(50, count($allPm)) }} รายการ
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal PIN -->
    <div class="modal fade" id="pinModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
        aria-labelledby="pinModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-body p-4 text-center">
                    <div class="mb-3 text-teal" style="color: #14B8A6;">
                        <i class="fas fa-lock fa-3x"></i>
                    </div>
                    <h5 class="fw-bold mb-3 text-dark">ยืนยันตัวตน</h5>
                    <p class="text-muted small mb-4">กรุณากรอกรหัส PIN เพื่อเข้าใช้งาน</p>

                    <input type="password" id="pinInput"
                        class="form-control form-control-lg text-center bg-light border-0 mb-3" placeholder="• • • • • •"
                        maxlength="6" style="letter-spacing: 5px; font-weight: bold;">

                    <div id="pinError" class="badge bg-danger-subtle text-danger mb-3 p-2 w-100" style="display:none;">
                        <i class="fas fa-exclamation-circle me-1"></i> รหัส PIN ไม่ถูกต้อง
                    </div>

                    <button type="button" class="btn btn-teal w-100 rounded-pill py-2 fw-bold shadow-sm" id="checkPinBtn">
                        เข้าใช้งาน
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('pmScript')
    <script src="{{ asset('js/pin.js') }}"></script>
    <script>
        let pmSearchTimeout;
        const searchInput = $('input[name="search"]');

        // สร้าง Loading Spinner ที่สวยงามขึ้น
        const loadingSpinner = $(
            '<div class="spinner-border text-teal position-absolute" role="status" style="display:none; width: 1.5rem; height: 1.5rem; right: 15px; color: #14B8A6;"><span class="visually-hidden">Loading...</span></div>'
        );
        // Append Spinner ไปยัง .search-input-group แทน parent ธรรมดา เพราะเราเปลี่ยนโครงสร้าง
        $('.search-input-group').append(loadingSpinner);

        searchInput.on('input', function() {
            clearTimeout(pmSearchTimeout);
            const searchValue = $(this).val().trim();

            // Show loading
            loadingSpinner.show();

            pmSearchTimeout = setTimeout(() => {
                $.get("{{ route('pm_search') }}", {
                    search: searchValue
                }, function(data) {
                    $('.pm-result').html($(data).find('.pm-result').html());
                    loadingSpinner.hide();
                }).fail(function() {
                    loadingSpinner.hide();
                    // Optional: Show simplified error toast/message
                });
            }, 700);
        });

        // Focus input after PIN success (Optional enhancement to pin.js logic via observer or interval)
        // Copy to Clipboard (Event Delegation for dynamic content)
        $(document).on('click', '.copy-trigger', function() {
            const textToCopy = $(this).data('text');
            const element = $(this);

            // Use fallback if navigator.clipboard is not available (e.g. non-https locally sometimes)
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(textToCopy).then(() => showCopyFeedback(element))
                    .catch(err => console.error('Copy failed', err));
            } else {
                // Fallback method
                const textArea = document.createElement("textarea");
                textArea.value = textToCopy;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    showCopyFeedback(element);
                } catch (err) {
                    console.error('Fallback copy failed', err);
                }
                document.body.removeChild(textArea);
            }
        });

        function showCopyFeedback(element) {
            const icon = element.find('.copy-icon');
            const originalClass = "far fa-copy text-muted opacity-50 small copy-icon";

            // Change icon
            icon.removeClass('far fa-copy text-muted opacity-50').addClass('fas fa-check text-success opacity-100');

            // Show Tooltip
            const toastHtml = `
                <div class="copy-feedback position-absolute bg-dark text-white px-2 py-1 rounded small shadow-sm" 
                        style="top: -35px; left: 50%; transform: translateX(-50%); z-index: 1050; white-space: nowrap; font-size: 0.8rem; opacity: 0; transition: opacity 0.2s;">
                    คัดลอกแล้ว!
                </div>
            `;

            // Remove existing feedback first
            element.find('.copy-feedback').remove();

            const toastEl = $(toastHtml).appendTo(element);

            // Trigger reflow for transition
            setTimeout(() => toastEl.css('opacity', '1'), 10);

            setTimeout(() => {
                icon.attr('class', originalClass);
                toastEl.css('opacity', '0');
                setTimeout(() => toastEl.remove(), 200);
            }, 1500);
        }

        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === "attributes" && mutation.attributeName === "style") {
                    if (document.getElementById('pm-content').style.display !== 'none') {
                        setTimeout(() => searchInput.focus(), 300);
                    }
                }
            });
        });
        observer.observe(document.getElementById('pm-content'), {
            attributes: true
        });
    </script>
@endpush
