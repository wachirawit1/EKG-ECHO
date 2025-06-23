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
        radio.addEventListener('change', function () {
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

document.getElementById('main-content').addEventListener('input', async function (e) {
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
    
        const shouldEnable = fname && lname;
    
        // ปลดล็อกฟิลด์ทั่วไป
        const fieldsToToggle = ["tel", "wardSelect", "doctorSelect", "appointmentDate", "note"];
        fieldsToToggle.forEach(id => {
            const el = modal.querySelector(`#${id}`);
            if (el) el.disabled = !shouldEnable;
        });
    
        // ปลดล็อก radio ของช่วงเวลา
        const radios = modal.querySelectorAll('input[name="appointment_time"]');
        radios.forEach(radio => {
            radio.disabled = !shouldEnable;
        });
    
        // ปลดล็อก custom time input ด้วย
        const customTimeInputs = modal.querySelectorAll('#custom_start_time, #custom_end_time');
        customTimeInputs.forEach(input => {
            input.disabled = !shouldEnable;
        });
    }
    
});

// ปุ้มอัพเดตนัด
document.addEventListener('click', function (e) {
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

$(document).on('shown.bs.modal', '#addAppointment', function () {

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



document.addEventListener('click', function (e) {
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