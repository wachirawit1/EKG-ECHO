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
            const container = document.getElementById('main-content');
            container.innerHTML = html;
            container.scrollIntoView({
                behavior: 'smooth'
            });

            setupSelect2InModal();
            setupSelect2InModal1();
            setupPaginationLinks();
            setupAppointmentValidation();
            setupTreatmentFormValidation();
            setupAddPatientValidation();

            document.getElementById('resetSearch')?.addEventListener('click', function () {
                // รีเซ็ตเงื่อนไขการค้นหา
                if (page == 'appointments') {
                    currentSearchParams = {};
                    loadPage('appointments'); // แก้ path นี้ให้ตรงกับที่โหลด fragment
                } else {
                    currentTreatmentSearchParams = {};
                    loadPage('treatments')
                }
            });
            if (page === 'treatments') {
                setupPatientTypeToggleInModal();
            }
        })
        .catch(err => {
            console.error("โหลดข้อมูลไม่สำเร็จ:", err);

            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: 'ไม่สามารถโหลดข้อมูลได้ในขณะนี้ กรุณาลองใหม่ภายหลัง',
                confirmButtonText: 'ตกลง'
            });
        })
        .finally(() => {
            resetCursor(); //  กลับ cursor เป็นปกติ
        });
}

// Select2 ใน Modal
function setupSelect2InModal() {
    $('#addAppointment').on('shown.bs.modal', function () {
        if (!isSelect2Initialized) {
            $('#wardSelect').select2({
                dropdownParent: $('#addAppointment .modal-body'),
                width: '100%'
            });
            isSelect2Initialized = true;
        }
    });
    $('#addAppointment').on('hidden.bs.modal', function () {
        isSelect2Initialized = false;
        $('#wardSelect').select2('destroy');
    });
}

function setupSelect2InModal1() {
    $('#addTreatment').on('shown.bs.modal', function () {
        $('#agency').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addTreatment .modal-body'),
            width: '100%'
        });

        $('#forward').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#addTreatment .modal-body'),
            width: '100%'
        });
    });

    $('#addTreatment').on('hidden.bs.modal', function () {
        $('#agency').select2('destroy');
        $('#forward').select2('destroy');
    });
}


// Pagination dynamic
function setupPaginationLinks() {
    const paginationButtons = document.querySelectorAll('#main-content .page-btn');
    paginationButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const pageNum = this.getAttribute('data-page');

        
            // ตรวจสอบว่าอยู่หน้าไหนจาก URL หรือจาก element บนหน้า
            const currentUrl = window.location.pathname;
            let params = {};
            let pageName = '';

            // ตรวจสอบจาก content ที่แสดงอยู่ในหน้า
            if (document.querySelector('#searchForm')) {
                // หน้า appointments
                pageName = 'appointments';
                params = {
                    ...currentSearchParams,
                    page: pageNum
                };
            } else if (document.querySelector('#searchTreatForm')) {
                // หน้า treatments
                pageName = 'treatments';
                params = {
                    ...currentTreatmentSearchParams,
                    page: pageNum
                };
            }

            if (pageName) {
                loadPage(pageName, params);
            }
        });
    });
}

// Form Validation + HN Check (appointment)
function setupAppointmentValidation() {
    // if (isFormValidated) return; // ป้องกันไม่ให้ addEventListener ซ้ำ

    const modalEl = document.getElementById('addAppointment');
    if (!modalEl || modalEl.dataset.validated === "true") return;

    modalEl.addEventListener('shown.bs.modal', function () {
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




        form.addEventListener('submit', function (e) {

            const resource = form.querySelector('input[name="resource"]:checked')?.value || '';
            const hn = hnInput.value.trim();
            const telRaw = telInput.value.trim();
            const docID = form.querySelector('[name="docID"]')?.value || '';
            const date = form.querySelector('[name="appointmentDate"]')?.value || '';
            const ward = form.querySelector('[name="ward"]')?.value || '';
            const fname = form.querySelector('[name="fname"]')?.value.trim() || '';
            const lname = form.querySelector('[name="lname"]')?.value.trim() || '';
            const hospitalName = form.querySelector('[name="hospital_name"]')?.value.trim();
            const titleName = form.querySelector('[name="titleName"]')?.value.trim();
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
                if(!titleName) errors.push('กรุณากรอกคำนำหน้า');
                if (!fname) errors.push('กรุณากรอกชื่อ');
                if (!lname) errors.push('กรุณากรอกนามสกุล');
                if (!hospitalName) errors.push('กรุณาเลือกโรงพยาบาล');
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

    modalEl.addEventListener('shown.bs.modal', function () {
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





        form.addEventListener('submit', function (e) {
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

    modalEl.addEventListener('shown.bs.modal', function () {
        const form = modalEl.querySelector('form');
        if (!form) return;

        form.addEventListener('submit', function (e) {
            const hospitalName = form.querySelector('[name="hospital_name"]')?.value.trim();
            const titleName = form.querySelector('[name="titleName"]')?.value.trim();
            const fname = form.querySelector('[name="fname"]')?.value.trim();
            const lname = form.querySelector('[name="lname"]')?.value.trim();

            let errors = [];

            if (!hospitalName) errors.push("กรุณาเลือกโรงพยาบาล");
            if(!titleName) errors.push("กรุณากรอกคำนำหน้า")
            if (!fname) errors.push("กรุณากรอกชื่อ");
            if (!lname) errors.push("กรุณากรอกนามสกุล");
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

function setupPatientTypeToggle() {
    const radios = document.querySelectorAll('input[name="resource"]');
    radios.forEach(radio => {
        radio.addEventListener('change', togglePatientFields);
    });
}