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

    {{-- jquery --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- select2 js --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        // เรียกฟังก์ชันทันทีตอนโหลดหน้า (สำหรับตั้งค่าครั้งแรก)
        document.addEventListener("DOMContentLoaded", function() {
            togglePatientFields();


        });

        // toggle
        function togglePatientFields() {
            const selected = document.querySelector('input[name="resource"]:checked');
            const hnGroup = document.getElementById('hn-group');
            const hospitalGroup = document.getElementById('hospital-group');

            if (!selected) return;


            if (selected.value === 'in') {
                hnGroup.style.display = 'flex';
                hospitalGroup.style.display = 'none';
            } else {
                hnGroup.style.display = 'none';
                hospitalGroup.style.display = 'block';
            }
        }

        function setupPatientTypeToggleInModal() {
            const modal = document.querySelector('#addTreatment'); // หรือใช้ .modal
            if (!modal) return;

            const inSec = modal.querySelector('#in-section');
            const outSec = modal.querySelector('#out-section');
            const radios = modal.querySelectorAll('input[name="resource"]');

            radios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (radio.value === 'in') {
                        inSec.style.display = '';
                        outSec.style.display = 'none';
                    } else {
                        inSec.style.display = 'none';
                        outSec.style.display = '';
                    }
                });
            });

            // เรียกครั้งแรกเพื่อ sync กับค่า checked ปัจจุบัน
            const selected = modal.querySelector('input[name="resource"]:checked');
            if (selected?.value === 'in') {
                inSec.style.display = '';
                outSec.style.display = 'none';
            } else {
                inSec.style.display = 'none';
                outSec.style.display = '';
            }
        }

        function setupPatientTypeToggle() {
            const radios = document.querySelectorAll('input[name="resource"]');
            radios.forEach(radio => {
                radio.addEventListener('change', togglePatientFields);
            });
        }

        // โหลดหน้าแรกเป็นตารางนัด
        loadPage('appointments');

        function togglePage(page) {
            loadPage(page);

            // ปรับคลาส active
            document.getElementById('btn-appointments').classList.remove('active');
            document.getElementById('btn-treatments').classList.remove('active');
            document.getElementById('btn-' + page).classList.add('active');
        }

        // ป้องกัน addEventListener ซ้ำ
        let isFormValidated = false;
        let isSelect2Initialized = false;

        // โหลดหน้าพร้อมคิวรี่
        function loadPage(page, params = {}) {
            const query = new URLSearchParams(params).toString();
            const fullUrl = `/fragments/${page}${query ? '?' + query : ''}`;


            setCursorWait(); //  ใช้ cursor wait ทั่วหน้าแบบบังคับ

            fetch(fullUrl)
                .then(res => res.text())
                .then(html => {
                    document.getElementById('main-content').innerHTML = html;

                    setupSelect2InModal();
                    setupSelect2InModal1();
                    setupPaginationLinks();
                    setupAppointmentValidation();
                    setupTreatmentFormValidation();
                    setupAddPatientValidation();

                    if (page === 'treatments') {
                        setupPatientTypeToggleInModal();
                    }
                })
                .catch(err => {
                    console.error("โหลดไม่สำเร็จ", err);
                    alert("โหลดไม่สำเร็จ");
                })
                .finally(() => {
                    // document.documentElement.style.cursor = 'default';
                    resetCursor(); //  กลับ cursor เป็นปกติ
                });
        }

        // Select2 ใน Modal
        function setupSelect2InModal() {
            $('#addAppointment').on('shown.bs.modal', function() {
                if (!isSelect2Initialized) {
                    $('#wardSelect').select2({
                        dropdownParent: $('#addAppointment .modal-body'),
                        width: '100%'
                    });
                    isSelect2Initialized = true;
                }
            });
            $('#addAppointment').on('hidden.bs.modal', function() {
                isSelect2Initialized = false;
                $('#wardSelect').select2('destroy');
            });
        }

        function setupSelect2InModal1() {
            $('#addTreatment').on('shown.bs.modal', function() {
                $('#agency').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#addTreatment .modal-body'),
                    width: '100%'
                });

                $('#forward').select2({
                    theme: 'bootstrap-5',
                    dropdownParent: $('#addTreatment .modal-body'),
                    allowClear: true,
                    width: '100%'
                });
            });

            $('#addTreatment').on('hidden.bs.modal', function() {
                $('#agency').select2('destroy');
                $('#forward').select2('destroy');
            });
        }


        // Pagination dynamic
        function setupPaginationLinks() {
            const paginationButtons = document.querySelectorAll('#main-content .page-btn');
            paginationButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const pageNum = this.getAttribute('data-page');
                    const currentPage = document.querySelector('.btn-toggle.active')?.id.replace('btn-',
                        '') || 'appointments';
                    loadPage(currentPage, {
                        page: pageNum
                    });
                });
            });
        }

        // Form Validation + HN Check (appointment)
        function setupAppointmentValidation() {
            // if (isFormValidated) return; // ป้องกันไม่ให้ addEventListener ซ้ำ

            const modalEl = document.getElementById('addAppointment');
            if (!modalEl || modalEl.dataset.validated === "true") return;

            modalEl.addEventListener('shown.bs.modal', function() {
                console.log('correct!');

                const form = modalEl.querySelector("form");
                const hnInput = modalEl.querySelector("#hn");
                const hnError = modalEl.querySelector("#hn-error");
                const telInput = modalEl.querySelector("#tel");



                if (!form || !hnInput || !hnError) return;

                // hn realtime validation
                hnInput.addEventListener("input", () => {
                    const value = hnInput.value.trim();
                    if (!/^\d{1,7}$/.test(value)) {
                        hnInput.classList.add("is-invalid");
                        hnError.classList.remove("d-none");
                    } else {
                        hnInput.classList.remove("is-invalid");
                        hnError.classList.add("d-none");
                    }
                });




                form.addEventListener('submit', function(e) {

                    const resource = form.querySelector('input[name="resource"]:checked')?.value || '';
                    const hn = hnInput.value.trim();
                    const telRaw = telInput.value.trim();
                    const docID = form.querySelector('[name="docID"]')?.value || '';
                    const date = form.querySelector('[name="appointmentDate"]')?.value || '';
                    const ward = form.querySelector('[name="ward"]')?.value || '';
                    const fname = form.querySelector('[name="fname"]')?.value.trim() || '';
                    const lname = form.querySelector('[name="lname"]')?.value.trim() || '';
                    const timeOption = form.querySelector('input[name="appointment_time"]:checked')
                        ?.value || '';
                    let errors = [];

                    if (resource !== 'in' && resource !== 'out') {
                        errors.push('กรุณาเลือกประเภทผู้ป่วย');
                    }

                    if (resource === 'in') {
                        if (!hn) {
                            errors.push('กรุณากรอก HN สำหรับผู้ป่วยใน');
                        } else if (!/^\d{1,7}$/.test(hn)) {
                            errors.push('HN ต้องเป็นตัวเลขไม่เกิน 7 หลัก');
                        }
                    }

                    // validate ชื่อ-นามสกุล (เฉพาะผู้ป่วยนอก)
                    if (resource === 'out') {
                        if (!fname) errors.push('กรุณากรอกชื่อผู้ป่วยนอก');
                        if (!lname) errors.push('กรุณากรอกนามสกุลผู้ป่วยนอก');
                    }

                    // validate เบอร์โทรศัพท์
                    if (!telRaw) {
                        errors.push('กรุณากรอกเบอร์โทรศัพท์');
                    } else {
                        // split เบอร์ด้วย comma หรือ space
                        const telNumbers = telRaw.split(/[, ]+/).filter(t => t.trim() !== '');
                        const invalids = telNumbers.filter(t => !/^\d{9,10}$/.test(t));
                        if (invalids.length > 0) {
                            errors.push('เบอร์โทรบางรายการไม่ถูกต้อง (ต้องเป็นตัวเลข 9-10 หลัก)');
                        }
                    }

                    // validate วอร์ด / แพทย์ / วันที่
                    if (!ward) errors.push('กรุณาเลือกวอร์ด');
                    if (!docID) errors.push('กรุณาเลือกแพทย์');
                    if (!date) errors.push('กรุณาเลือกวันที่นัด');
                    //เวลานัด
                    if (!timeOption) {
                        errors.push('กรุณาเลือกช่วงเวลานัดหมาย');
                    }

                    if (timeOption === 'custom') {
                        const startTime = form.querySelector('input[name="custom_start_time"]').value;
                        const endTime = form.querySelector('input[name="custom_end_time"]').value;

                        if (!startTime || !endTime) {
                            errors.push('กรุณากรอกเวลาเริ่มและสิ้นสุดของช่วงเวลานัดหมาย');
                        } else if (startTime >= endTime) {
                            errors.push('เวลาสิ้นสุดต้องมากกว่าเวลาเริ่มต้น');
                        }
                    }

                    if (errors.length > 0) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'ข้อมูลไม่ถูกต้อง',
                            html: errors.join('<br>'),
                            confirmButtonText: 'ตกลง'
                        });
                    } else {
                        // ส่งค่าที่ clean แล้วกลับไปที่ form
                        form.tel.value = telRaw;
                    }
                });
            });

            // isFormValidated = true; // เซตว่า add แล้ว
            modalEl.dataset.validated = "true";
        }

        function setupTreatmentFormValidation() {
            const modalEl = document.getElementById('addTreatment');
            if (!modalEl || modalEl.dataset.validated === "true") return;

            modalEl.addEventListener('shown.bs.modal', function() {
                const form = modalEl.querySelector('form');
                const hnInput = modalEl.querySelector('#hn');
                const hnError = modalEl.querySelector("#hn-error");

                if (!form || !hnInput || !hnError) return;

                // hn realtime validation
                hnInput.addEventListener("input", () => {
                    const value = hnInput.value.trim();
                    if (!/^\d{1,7}$/.test(value)) {
                        hnInput.classList.add("is-invalid");
                        hnError.classList.remove("d-none");
                    } else {
                        hnInput.classList.remove("is-invalid");
                        hnError.classList.add("d-none");
                    }
                });





                form.addEventListener('submit', function(e) {
                    const resource = form.querySelector('input[name="resource"]:checked')?.value;
                    const hn = form.querySelector('#hn')?.value.trim();
                    const nameDisplay = form.querySelector('#hn_name_display')?.value.trim();
                    const fname = form.querySelector('[name="fname"]')?.value.trim();
                    const lname = form.querySelector('[name="lname"]')?.value.trim();
                    const agency = form.querySelector('#agency')?.value;
                    const forward = form.querySelector('#forward')?.value;
                    const t_date = form.querySelector('#t_date')?.value;

                    let errors = [];

                    if (resource === 'in') {
                        // คนไข้ใน ต้องกรอก hn และ hn ต้องเป็นเลข 1-7 หลัก
                        if (!hn) {
                            errors.push("กรุณากรอก HN สำหรับผู้ป่วยใน");
                        } else if (!/^\d{1,7}$/.test(hn)) {
                            errors.push("HN ต้องเป็นตัวเลข 1-7 หลัก");
                        }
                    } else if (resource === 'out') {
                        // คนไข้นอก ต้องกรอกชื่อและนามสกุล
                        if (!fname) errors.push("กรุณากรอกชื่อผู้ป่วย");
                        if (!lname) errors.push("กรุณากรอกนามสกุลผู้ป่วย");
                    } else {
                        errors.push("กรุณาเลือกประเภทผู้ป่วย (ใน / นอก)");
                    }

                    if (!agency) {
                        errors.push("กรุณาเลือกหน่วยงาน");
                    }

                    if (!forward) {
                        errors.push("กรุณาเลือกหน่วยงานส่งต่อ");
                    }

                    if (!t_date) {
                        errors.push("กรุณาระบุวันที่รักษา");
                    }

                    if (errors.length > 0) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'ข้อมูลไม่ถูกต้อง',
                            html: errors.join('<br>'),
                            confirmButtonText: 'ตกลง'
                        });
                    }
                });
            });
            modalEl.dataset.validated = "true";
        }

        function setupAddPatientValidation() {
            const modalEl = document.getElementById('addPatient'); // ตรวจให้ตรงกับ ID ของ modal
            if (!modalEl || modalEl.dataset.validated === "true") return;

            modalEl.addEventListener('shown.bs.modal', function() {
                const form = modalEl.querySelector('form');
                if (!form) return;

                form.addEventListener('submit', function(e) {
                    const hospitalName = form.querySelector('[name="hospital_name"]')?.value.trim();
                    const fname = form.querySelector('[name="fname"]')?.value.trim();
                    const lname = form.querySelector('[name="lname"]')?.value.trim();
                    const idCard = form.querySelector('[name="id_card"]')?.value.trim();
                    const gender = form.querySelector('[name="gender"]')?.value;
                    const dob = form.querySelector('[name="dob"]')?.value;

                    let errors = [];

                    if (!hospitalName) errors.push("กรุณาเลือกโรงพยาบาล");
                    if (!fname) errors.push("กรุณากรอกชื่อ");
                    if (!lname) errors.push("กรุณากรอกนามสกุล");
                    if (!idCard || idCard.length !== 13) errors.push(
                        "กรุณากรอกเลขบัตรประชาชนให้ครบ 13 หลัก");
                    if (!gender) errors.push("กรุณาเลือกเพศ");
                    if (!dob) errors.push("กรุณาระบุวันเดือนปีเกิด");

                    if (errors.length > 0) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'ข้อมูลไม่ถูกต้อง',
                            html: errors.join('<br>'),
                            confirmButtonText: 'ตกลง'
                        });
                    }
                });
            });
            modalEl.dataset.validated = "true";
        }




        document.getElementById('main-content').addEventListener('input', async function(e) {
            const target = e.target;
            const modal = target.closest('.modal');
            if (!modal) return;


            const hnInput = modal.querySelector('#hn');
            const nameDisplay = modal.querySelector('#hn_name_display');
            const extraFields = modal.querySelector('#extra-patient-fields');




            // เมื่อพิมพ์ HN
            if (target.id === 'hn') {
                const hn = target.value.trim();
                if (!hn) {
                    if (nameDisplay) nameDisplay.value = '';
                    return;
                }

                try {
                    const res = await fetch(`/api/patient-name?hn=${encodeURIComponent(hn)}`);
                    const data = await res.json();

                    if (data.name && nameDisplay) {
                        nameDisplay.value = data.name;
                        nameDisplay.classList.remove('text-danger');
                        if (extraFields) extraFields.style.display = 'none';
                    } else {
                        nameDisplay.value = 'ไม่พบข้อมูล';
                        nameDisplay.classList.add('text-danger');
                        if (extraFields) extraFields.style.display = 'block';
                    }

                    // ช่องที่ต้องล็อกหรือปลดล็อก
                    const fieldsToToggle = ["tel", "wardSelect", "doctorSelect", "appointmentDate", "note"];
                    fieldsToToggle.forEach(id => {
                        const el = modal.querySelector(`#${id}`);
                        if (el) el.disabled = !data.name;
                    });

                    // จัดการ radio แยก
                    const radios = modal.querySelectorAll('input[name="appointment_time"]');
                    radios.forEach(radio => {
                        radio.disabled = !data.name;
                    });

                } catch (err) {
                    console.log('เกิดข้อผิดพลาดในการดึงข้อมูล:', err);
                    if (nameDisplay) nameDisplay.value = 'โหลดข้อมูลผิดพลาด';
                }
            }

            // เมื่อพิมพ์ชื่อหรือนามสกุล
            if (['fname', 'lname'].includes(target.id)) {
                const fname = modal.querySelector('#fname')?.value.trim();
                const lname = modal.querySelector('#lname')?.value.trim();

                const fieldsToToggle = ["tel", "wardSelect", "doctorSelect", "appointmentDate", "note"];
                const shouldEnable = fname && lname;

                fieldsToToggle.forEach(id => {
                    const el = modal.querySelector(`#${id}`);
                    if (el) el.disabled = !shouldEnable;
                });
            }
        });

        // ปุ้มอัพเดตนัด
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('edit-btn')) {
                const id = e.target.dataset.id;
                const form = document.getElementById(`patientInfoForm${id}`);
                if (!form) return;


                // เปลี่ยนปุ่มแก้ไขเป็น "กำลังแก้ไขอยู่..."
                e.target.innerText = 'กำลังแก้ไขอยู่...';
                e.target.classList.remove('btn-warning');
                e.target.classList.add('btn-secondary');
                e.target.disabled = true;

                // เปิด input ยกเว้น field ที่ readonly ถาวร
                form.querySelectorAll('input, textarea').forEach(el => {
                    const editableFields = [`a_date${id}`, `tel${id}`, `note${id}`, `a_time_start${id}`,
                        `a_time_end${id}`
                    ];
                    // ซ่อน input:text
                    document.getElementById(`a_date_text${id}`).style.display = 'none';
                    document.getElementById(`a_time_text${id}`).classList.add('d-none');
                    // เปิดให้กรอก
                    document.getElementById(`a_date${id}`).hidden = false;
                    document.getElementById(`a_time_start${id}`).hidden = false;
                    document.getElementById(`a_time_end${id}`).hidden = false;

                    if (editableFields.includes(el.id)) {
                        el.removeAttribute('readonly');
                    }
                });

                // เปิดปุ่ม "บันทึก"
                const saveBtn = document.querySelector(`#patientInfo${id} .btn-primary`);
                if (saveBtn) saveBtn.disabled = false;
            }
        });

        $(document).on('shown.bs.modal', '#addAppointment', function() {

            const customRadio = document.getElementById("customTime");
            const startTime = document.getElementById("custom_start_time");
            const endTime = document.getElementById("custom_end_time");

            if (!customRadio || !startTime || !endTime) return;

            function toggleCustomTimeInputs() {
                const isCustom = customRadio.checked;
                startTime.disabled = !isCustom;
                endTime.disabled = !isCustom;
            }

            $('input[name="appointment_time"]').on('change', toggleCustomTimeInputs);
            toggleCustomTimeInputs();
        });



        document.addEventListener('click', function(e) {
            const saveBtn = e.target.closest('.save-btn');
            if (saveBtn) {
                const id = saveBtn.dataset.id;
                const form = document.getElementById(`patientInfoForm${id}`);
                if (!form) return;

                const formData = new FormData(form);

                fetch(`/appointments/update/${id}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Lock fields
                            form.querySelectorAll('input, textarea').forEach(el => {
                                const editableFields = [`a_date${id}`, `tel${id}`, `note${id}`,
                                    `a_time_start${id}`, `a_time_end${id}`
                                ];
                                if (editableFields.includes(el.id)) {
                                    el.setAttribute('readonly', true);
                                }
                            });

                            // Disable save button
                            saveBtn.disabled = true;

                            const a_date_input = document.getElementById(`a_date${id}`);
                            const a_date_text = document.getElementById(`a_date_text${id}`);

                            if (a_date_input && a_date_text) {
                                // แปลงค่าวันที่เป็นแบบไทย แล้วใส่กลับไปที่ช่องแสดงผล
                                a_date_text.value = formatThaiDate(a_date_input.value);
                                a_date_input.hidden = true;
                                a_date_text.style.display = 'inline';
                            }

                            const start = document.getElementById(`a_time_start${id}`);
                            const end = document.getElementById(`a_time_end${id}`);
                            const a_time_text = document.getElementById(`a_time_text${id}`);

                            if (start && end && a_time_text) {
                                const timeValue = `${start.value}-${end.value}`;
                                a_time_text.value = timeValue;

                                // ซ่อน input เวลา และแสดงช่อง text
                                start.hidden = true;
                                end.hidden = true;
                                a_time_text.classList.remove('d-none');
                                a_time_text.style.display = 'inline'; // ป้องกัน display: none
                            }



                            // เปลี่ยนปุ่ม "กำลังแก้ไขอยู่..." กลับเป็น "แก้ไข"
                            const editBtn = document.querySelector(`.edit-btn[data-id="${id}"]`);
                            if (editBtn) {
                                editBtn.innerText = 'แก้ไข';
                                editBtn.classList.remove('btn-secondary');
                                editBtn.classList.add('btn-warning');
                                editBtn.disabled = false;
                            }

                            // แสดง sweetalert
                            Swal.fire({
                                icon: 'success',
                                title: 'บันทึกสำเร็จ',
                                text: 'ข้อมูลได้รับการอัปเดตเรียบร้อยแล้ว',
                                confirmButtonText: 'ตกลง'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'เกิดข้อผิดพลาด',
                                text: data.message || 'ไม่สามารถบันทึกข้อมูลได้'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'เซิร์ฟเวอร์ผิดพลาด',
                            text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
                        });
                    });
            }
        });




        // ค้นหานัดโดย hn
        function searchAppointments(e) {
            e.preventDefault(); // ยกเลิก submit แบบปกติ
            const form = document.getElementById('searchForm');
            const formData = new FormData(form);

            const hn = formData.get('hn');
            const doc_id = formData.get('doc_id');
            const start_date = formData.get('start_date');
            const end_date = formData.get('end_date');

            loadPage('appointments', {
                hn,
                start_date,
                end_date,
                doc_id
            });
        }

        // ค้นหาการรักษาโดย hn
        function searchTreatments(e) {
            e.preventDefault();
            const form = document.getElementById('searchTreatForm');
            const formData = new FormData(form);

            const hn = formData.get('hn');
            const start_date = formData.get('start_date');
            const end_date = formData.get('end_date');
            loadPage('treatments', {
                hn: hn,
                start_date,
                end_date
            });



        }

        function formatThaiDate(dateStr) {
            const months = [
                'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
            ];

            const [year, month, day] = dateStr.split('-');
            const thaiYear = parseInt(year) + 543;
            const monthName = months[parseInt(month) - 1];

            return `${parseInt(day)} ${monthName} ${thaiYear}`;
        }

        function setCursorWait() {
            const style = document.createElement('style');
            style.id = 'global-wait-cursor';
            style.innerHTML = `
        * {
            cursor: progress !important;
        }
    `;
            document.head.appendChild(style);
        }

        function resetCursor() {
            const style = document.getElementById('global-wait-cursor');
            if (style) style.remove();
        }
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
