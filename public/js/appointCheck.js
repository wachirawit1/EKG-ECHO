// JavaScript สำหรับเช็คประวัติการนัด
function checkAppointmentHistory() {
    const resource = document.querySelector('input[name="resource"]:checked').value;
    let checkData = { resource: resource };
    
    if (resource === 'in') {
        // ผู้ป่วยใน - เช็คจาก HN
        const hn = document.getElementById('hn').value.trim();
        if (!hn) return;
        
        // Validate HN (ตัวเลขไม่เกิน 7 หลัก)
        if (!/^\d{1,7}$/.test(hn)) {
            showHNError(true);
            return;
        } else {
            showHNError(false);
        }
        
        checkData.hn = hn;
    } else {
        // ผู้ป่วยนอก - เช็คจากชื่อ
        const fname = document.getElementById('fname').value.trim();
        const lname = document.getElementById('lname').value.trim();
        if (!fname || !lname) return;
        
        checkData.fname = fname;
        checkData.lname = lname;
    }
    
    // เรียก API เช็คประวัติ
    fetch('/check-appointment-history', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(checkData)
    })
    .then(response => response.json())
    .then(data => {
        clearAppointmentAlert();
        
        if (data.status === 'found') {
            showAppointmentAlert(data.message, 'warning');
            enableAppointmentFields();
        } else if (data.status === 'no_history') {
            showAppointmentAlert('ไม่พบประวัติการนัดหมายก่อนหน้า', 'info');
            enableAppointmentFields();
        } else if (data.status === 'not_found') {
            showAppointmentAlert(data.message, 'danger');
            disableAppointmentFields();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAppointmentAlert('เกิดข้อผิดพลาดในการตรวจสอบข้อมูล', 'danger');
    });
}

function showAppointmentAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show mt-2" role="alert" id="appointment-alert">
            <i class="fas fa-info-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // แสดงใต้ช่อง HN หรือชื่อ
    const targetElement = document.querySelector('input[name="resource"]:checked').value === 'in' 
        ? document.getElementById('hn-group')
        : document.getElementById('hospital-group');
    
    targetElement.insertAdjacentHTML('afterend', alertHtml);
}

function clearAppointmentAlert() {
    const existingAlert = document.getElementById('appointment-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
}

function showHNError(show) {
    const errorElement = document.getElementById('hn-error');
    if (show) {
        errorElement.classList.remove('d-none');
    } else {
        errorElement.classList.add('d-none');
    }
}

function enableAppointmentFields() {
    // Enable ฟิลด์ต่างๆ เมื่อข้อมูลผู้ป่วยถูกต้อง
    document.getElementById('tel').disabled = false;
    document.getElementById('wardSelect').disabled = false;
    document.getElementById('doctorSelect').disabled = false;
    document.getElementById('appointmentDate').disabled = false;
    document.getElementById('note').disabled = false;
    
    // Enable radio buttons เวลา
    document.querySelectorAll('input[name="appointment_time"]').forEach(radio => {
        radio.disabled = false;
    });
    
    // Enable custom time inputs
    document.getElementById('custom_start_time').disabled = false;
    document.getElementById('custom_end_time').disabled = false;
}

function disableAppointmentFields() {
    // Disable ฟิลด์ต่างๆ เมื่อไม่พบข้อมูลผู้ป่วย
    document.getElementById('tel').disabled = true;
    document.getElementById('wardSelect').disabled = true;
    document.getElementById('doctorSelect').disabled = true;
    document.getElementById('appointmentDate').disabled = true;
    document.getElementById('note').disabled = true;
    
    // Disable radio buttons เวลา
    document.querySelectorAll('input[name="appointment_time"]').forEach(radio => {
        radio.disabled = true;
        radio.checked = false;
    });
    
    // Disable custom time inputs
    document.getElementById('custom_start_time').disabled = true;
    document.getElementById('custom_end_time').disabled = true;
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // เช็คเมื่อกรอก HN (ผู้ป่วยใน)
    document.getElementById('hn').addEventListener('blur', function() {
        if (document.getElementById('in_patient').checked) {
            checkAppointmentHistory();
        }
    });
    
    // เช็คเมื่อกรอกชื่อ-นามสกุล (ผู้ป่วยนอก)
    document.getElementById('fname').addEventListener('blur', function() {
        if (document.getElementById('out_patient').checked && 
            document.getElementById('lname').value.trim()) {
            checkAppointmentHistory();
        }
    });
    
    document.getElementById('lname').addEventListener('blur', function() {
        if (document.getElementById('out_patient').checked && 
            document.getElementById('fname').value.trim()) {
            checkAppointmentHistory();
        }
    });
});

// อัพเดทฟังก์ชัน togglePatientFields ที่มีอยู่แล้ว
function togglePatientFields() {
    const isInPatient = document.getElementById('in_patient').checked;
    const hnGroup = document.getElementById('hn-group');
    const hospitalGroup = document.getElementById('hospital-group');
    
    clearAppointmentAlert(); // ล้าง alert เมื่อเปลี่ยนประเภทผู้ป่วย
    disableAppointmentFields(); // ปิดฟิลด์ทั้งหมดเมื่อเปลี่ยนประเภท
    
    if (isInPatient) {
        hnGroup.style.display = 'block';
        hospitalGroup.style.display = 'none';
        // ล้างค่าในฟิลด์ผู้ป่วยนอก
        document.getElementById('fname').value = '';
        document.getElementById('lname').value = '';
    } else {
        hnGroup.style.display = 'none';
        hospitalGroup.style.display = 'block';
        // ล้างค่าในฟิลด์ผู้ป่วยใน
        document.getElementById('hn').value = '';
        document.getElementById('hn_name_display').value = '';
        showHNError(false);
    }
}