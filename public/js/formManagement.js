// อัพเดท togglePatientFields function
function togglePatientFields() {
    const selected = document.querySelector('input[name="resource"]:checked');
    const hnGroup = document.getElementById("hn-group");
    const hospitalGroup = document.getElementById("hospital-group");

    if (!selected) return;

    // ล้าง alert เมื่อเปลี่ยนประเภทผู้ป่วย
    clearAppointmentAlert();

    if (selected.value === "in") {
        hnGroup.style.display = "flex";
        hospitalGroup.style.display = "none";

        // ล้างข้อมูลผู้ป่วยนอก
        const fnameInput = document.getElementById("fname");
        const lnameInput = document.getElementById("lname");
        if (fnameInput) fnameInput.value = "";
        if (lnameInput) lnameInput.value = "";
    } else {
        hnGroup.style.display = "none";
        hospitalGroup.style.display = "block";

        // ล้างข้อมูลผู้ป่วยใน
        const hnInput = document.getElementById("hn");
        const nameDisplay = document.getElementById("hn_name_display");
        if (hnInput) hnInput.value = "";
        if (nameDisplay) {
            nameDisplay.value = "";
            nameDisplay.classList.remove("text-danger");
        }
        showHNError(false);
    }
}

function setupPatientTypeToggleInModal() {
    const modal = document.querySelector("#addTreatment"); // หรือใช้ .modal
    if (!modal) return;

    const inSec = modal.querySelector("#in-section");
    const outSec = modal.querySelector("#out-section");
    const radios = modal.querySelectorAll('input[name="resource"]');

    radios.forEach((radio) => {
        radio.addEventListener("change", function () {
            if (radio.value === "in") {
                inSec.style.display = "";
                outSec.style.display = "none";
            } else {
                inSec.style.display = "none";
                outSec.style.display = "";
            }
        });
    });

    // เรียกครั้งแรกเพื่อ sync กับค่า checked ปัจจุบัน
    const selected = modal.querySelector('input[name="resource"]:checked');
    if (selected?.value === "in") {
        inSec.style.display = "";
        outSec.style.display = "none";
    } else {
        inSec.style.display = "none";
        outSec.style.display = "";
    }
}

document
    .getElementById("main-content")
    .addEventListener("keydown", async function (e) {
        if (e.key === "Enter") {
            const target = e.target;
            const modal = target.closest(".modal");
            if (!modal) return;

            // ป้องกันการ submit form เมื่อกด Enter
            if (
                target.id === "hn" ||
                target.id === "fname" ||
                target.id === "lname"
            ) {
                e.preventDefault();
                e.stopPropagation();
            }

            const hnInput = modal.querySelector("#hn");
            const nameDisplay = modal.querySelector("#hn_name_display");
            const extraFields = modal.querySelector("#extra-patient-fields");

            // เมื่อพิมพ์ HN
            if (target.id === "hn") {
                const hn = target.value.trim();
                if (!hn) {
                    if (nameDisplay) {
                        nameDisplay.value = "";
                        nameDisplay.classList.remove("text-danger");
                    }
                    if (extraFields) extraFields.style.display = "none";
                    clearAppointmentAlert();
                    disableAppointmentFields(modal);
                    return;
                }

                // Validate HN format - รองรับ HN ที่มีตัวเลข 1-7 หลัก
                if (!/^\d{1,7}$/.test(hn)) {
                    showHNError(true);
                    disableAppointmentFields(modal);
                    clearAppointmentAlert();
                    if (nameDisplay) {
                        nameDisplay.value = "";
                        nameDisplay.classList.remove("text-danger");
                    }
                    return;
                } else {
                    showHNError(false);
                }

                // แสดง loading state
                if (nameDisplay) {
                    nameDisplay.value = "กำลังโหลด...";
                    nameDisplay.classList.remove("text-danger");
                }

                try {
                    // เช็คชื่อผู้ป่วยก่อน
                    const res = await fetch(
                        `/api/patient-name?hn=${encodeURIComponent(hn)}`,
                        {
                            method: "GET",
                            headers: {
                                Accept: "application/json",
                                "X-CSRF-TOKEN":
                                    document
                                        .querySelector(
                                            'meta[name="csrf-token"]'
                                        )
                                        ?.getAttribute("content") || "",
                            },
                        }
                    );

                    if (!res.ok) {
                        throw new Error(
                            `HTTP ${res.status}: ${res.statusText}`
                        );
                    }

                    const data = await res.json();

                    if (data.name && nameDisplay) {
                        nameDisplay.value = data.name;
                        nameDisplay.classList.remove("text-danger");
                        if (extraFields) extraFields.style.display = "none";

                        enableAppointmentFields(modal);

                        // เช็คประวัติการนัดหลังจากพบชื่อ
                        await checkAppointmentHistory("in", hn);
                    } else {
                        if (nameDisplay) {
                            nameDisplay.value = "ไม่พบข้อมูล";
                            nameDisplay.classList.add("text-danger");
                        }
                        if (extraFields) extraFields.style.display = "block";
                        disableAppointmentFields(modal);
                        clearAppointmentAlert();
                    }
                } catch (err) {
                    console.error("เกิดข้อผิดพลาดในการดึงข้อมูล:", err);
                    if (nameDisplay) {
                        nameDisplay.value = "เกิดข้อผิดพลาดในการโหลดข้อมูล";
                        nameDisplay.classList.add("text-danger");
                    }
                    disableAppointmentFields(modal);
                    clearAppointmentAlert();

                    // แสดง error alert
                    showAppointmentAlert(
                        "ไม่สามารถเชื่อมต่อกับระบบได้ กรุณาลองใหม่อีกครั้ง",
                        "danger"
                    );
                }
            }

            // เมื่อพิมพ์ชื่อหรือนามสกุล
            if (["fname", "lname"].includes(target.id)) {
                const fname = modal.querySelector("#fname")?.value.trim();
                const lname = modal.querySelector("#lname")?.value.trim();

                const shouldEnable = fname && lname;

                if (shouldEnable) {
                    enableAppointmentFields(modal);

                    // เช็คว่าเป็นผู้ป่วยนอกหรือไม่ก่อนเช็คประวัติ
                    const resourceRadio = modal.querySelector(
                        'input[name="resource"]:checked'
                    );
                    if (resourceRadio && resourceRadio.value === "out") {
                        // เช็คประวัติการนัดเมื่อกรอกครบชื่อ-นามสกุลสำหรับผู้ป่วยนอก
                        await checkAppointmentHistory(
                            "out",
                            null,
                            fname,
                            lname
                        );
                    }
                } else {
                    disableAppointmentFields(modal);
                    clearAppointmentAlert();
                }
            }
        }
    });

// ฟังก์ชันสำหรับปิดใช้งานฟิลด์การนัด
function disableAppointmentFields(modal) {
    if (!modal) return;

    const fieldsToToggle = [
        "tel",
        "wardSelect",
        "doctorSelect",
        "appointmentDate",
        "note",
    ];

    fieldsToToggle.forEach((id) => {
        const el = modal.querySelector(`#${id}`);
        if (el) el.disabled = true;
    });

    // จัดการ radio แยก
    const radios = modal.querySelectorAll('input[name="appointment_time"]');
    radios.forEach((radio) => {
        radio.disabled = true;
    });

    // ปิดใช้งาน custom time input ด้วย
    const customTimeInputs = modal.querySelectorAll(
        "#custom_start_time, #custom_end_time"
    );
    customTimeInputs.forEach((input) => {
        input.disabled = true;
    });
}

// ฟังก์ชันสำหรับเปิดใช้งานฟิลด์การนัด
function enableAppointmentFields(modal) {
    if (!modal) return;

    const fieldsToToggle = [
        "tel",
        "wardSelect",
        "doctorSelect",
        "appointmentDate",
        "note",
    ];

    fieldsToToggle.forEach((id) => {
        const el = modal.querySelector(`#${id}`);
        if (el) el.disabled = false;
    });

    // เปิดใช้งาน radio
    const radios = modal.querySelectorAll('input[name="appointment_time"]');
    radios.forEach((radio) => {
        radio.disabled = false;
    });

    // เปิดใช้งาน custom time input ด้วย
    const customTimeInputs = modal.querySelectorAll(
        "#custom_start_time, #custom_end_time"
    );
    customTimeInputs.forEach((input) => {
        input.disabled = false;
    });
}

// ฟังก์ชันเช็คประวัติการนัด
async function checkAppointmentHistory(
    resource,
    hn = null,
    fname = null,
    lname = null
) {
    // Validate input parameters
    if (!resource || (resource !== "in" && resource !== "out")) {
        console.error("Invalid resource parameter:", resource);
        return;
    }

    // ตรวจสอบว่ามี modal ที่เปิดอยู่หรือไม่
    const modal = document.querySelector(".modal.show");
    if (!modal) {
        console.error("No active modal found");
        return;
    }

    let checkData = { resource: resource };

    if (resource === "in") {
        if (!hn || hn.trim() === "") {
            console.error("HN is required for 'in' resource");
            return;
        }
        checkData.hn = hn.trim();
    } else if (resource === "out") {
        if (!fname || !lname || fname.trim() === "" || lname.trim() === "") {
            console.error(
                "First name and last name are required for 'out' resource"
            );
            return;
        }
        checkData.fname = fname.trim();
        checkData.lname = lname.trim();
    }

    console.log("Checking appointment history with data:", checkData);

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        if (!csrfToken) {
            console.error("CSRF token not found");
            showAppointmentAlert(
                "ไม่พบ CSRF token กรุณาโหลดหน้าใหม่",
                "danger"
            );
            return;
        }

        const response = await fetch(
            `${window.location.origin}/check-appointment-history`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                },
                body: JSON.stringify(checkData),
            }
        );

        console.log("Response status:", response.status);
        console.log("Response ok:", response.ok);

        if (!response.ok) {
            const errorText = await response.text();
            console.error("HTTP error response:", errorText);

            // แสดง error message ที่เป็นมิตรกับผู้ใช้
            let errorMessage = "เกิดข้อผิดพลาดในการเชื่อมต่อ";
            if (response.status === 404) {
                errorMessage = "ไม่พบ API endpoint";
            } else if (response.status === 422) {
                errorMessage = "ข้อมูลที่ส่งไม่ถูกต้อง";
            } else if (response.status === 500) {
                errorMessage = "เกิดข้อผิดพลาดในระบบ";
            }

            showAppointmentAlert(errorMessage, "danger");
            return;
        }

        // ดึง response text ก่อน
        const responseText = await response.text();
        console.log("Raw response:", responseText);

        // ตรวจสอบว่า response เป็น JSON หรือไม่
        if (!responseText.trim()) {
            console.error("Empty response from server");
            showAppointmentAlert("ไม่ได้รับข้อมูลจากระบบ", "danger");
            return;
        }

        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonError) {
            console.error("JSON parse error:", jsonError);
            console.error("Response text:", responseText);
            showAppointmentAlert("ข้อมูลที่ได้รับจากระบบไม่ถูกต้อง", "danger");
            return;
        }

        console.log("Parsed appointment data:", data);

        // เคลียร์ alert เก่าก่อน
        clearAppointmentAlert();

        // แสดงผลตามสถานะ
        switch (data.status) {
            case "found":
                showAppointmentAlert(
                    data.message || "พบประวัติการนัดหมาย",
                    "warning"
                );
                break;
            case "no_history":
                showAppointmentAlert("ไม่พบประวัติการนัดหมายก่อนหน้า", "info");
                break;
            case "not_found":
                showAppointmentAlert(
                    data.message || "ไม่พบข้อมูลผู้ป่วย",
                    "info"
                );
                break;
            case "error":
                showAppointmentAlert(
                    data.message || "เกิดข้อผิดพลาดในการค้นหา",
                    "danger"
                );
                break;
            default:
                console.warn("Unknown status:", data.status);
                showAppointmentAlert("ได้รับข้อมูลที่ไม่คาดคิด", "warning");
        }
    } catch (error) {
        console.error("Appointment check error:", error);
        clearAppointmentAlert();

        // แสดง error message ที่เป็นมิตรกับผู้ใช้
        let errorMessage = "เกิดข้อผิดพลาดในการตรวจสอบข้อมูล";
        if (error.message.includes("Failed to fetch")) {
            errorMessage = "ไม่สามารถเชื่อมต่อกับระบบได้";
        } else if (error.message.includes("NetworkError")) {
            errorMessage = "เกิดปัญหาการเชื่อมต่อเครือข่าย";
        }

        showAppointmentAlert(errorMessage, "danger");
    }
}

function showAppointmentAlert(message, type = "info") {
    // Validate parameters
    if (!message) {
        console.error("Message is required for alert");
        return;
    }

    if (!["success", "info", "warning", "danger"].includes(type)) {
        console.warn("Invalid alert type, using 'info' as default");
        type = "info";
    }

    // เคลียร์ alert เก่าก่อน
    clearAppointmentAlert();

    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show mt-2" role="alert" id="appointment-alert">
            <i class="fas fa-info-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    `;

    // หา modal ที่เปิดอยู่
    const modal = document.querySelector(".modal.show");
    if (!modal) {
        console.warn("No active modal found for alert display");
        return;
    }

    // หาจุดที่เหมาะสมในการแสดง alert
    const resourceRadio = modal.querySelector('input[name="resource"]:checked');
    let targetElement;

    if (resourceRadio && resourceRadio.value === "in") {
        targetElement =
            modal.querySelector("#hn-group") ||
            modal.querySelector("#hn")?.parentElement;
    } else {
        targetElement =
            modal.querySelector("#hospital-group") ||
            modal.querySelector("#fname")?.parentElement ||
            modal.querySelector("#lname")?.parentElement;
    }

    // ถ้าหาไม่เจอ ให้หาจุดทั่วไปในฟอร์ม
    if (!targetElement) {
        targetElement = modal.querySelector(".modal-body") || modal;
    }

    if (targetElement) {
        targetElement.insertAdjacentHTML("afterend", alertHtml);

        // Auto-dismiss info alerts after 5 seconds
        if (type === "info") {
            setTimeout(() => {
                const alert = document.getElementById("appointment-alert");
                if (alert) {
                    alert.classList.remove("show");
                    setTimeout(() => alert.remove(), 150);
                }
            }, 5000);
        }
    } else {
        console.error("Could not find target element for alert");
    }
}

function clearAppointmentAlert() {
    const existingAlert = document.getElementById("appointment-alert");
    if (existingAlert) {
        existingAlert.remove();
    }
}

function showHNError(show) {
    const errorElement = document.getElementById("hn-error");
    if (errorElement) {
        if (show) {
            errorElement.classList.remove("d-none");
        } else {
            errorElement.classList.add("d-none");
        }
    }
}

// เพิ่มฟังก์ชันสำหรับ debounce เพื่อป้องกันการเรียก API บ่อยเกินไป
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Event listener สำหรับ input events (นอกเหนือจาก Enter key)
document.getElementById("main-content").addEventListener(
    "input",
    debounce(async function (e) {
        const target = e.target;
        const modal = target.closest(".modal");
        if (!modal) return;

        // Handle HN input changes
        if (target.id === "hn") {
            const hn = target.value.trim();
            const nameDisplay = modal.querySelector("#hn_name_display");
            const extraFields = modal.querySelector("#extra-patient-fields");

            if (!hn) {
                if (nameDisplay) {
                    nameDisplay.value = "";
                    nameDisplay.classList.remove("text-danger");
                }
                if (extraFields) extraFields.style.display = "none";
                disableAppointmentFields(modal);
                clearAppointmentAlert();
                showHNError(false);
                return;
            }

            // ตรวจสอบรูปแบบ HN
            if (!/^\d{1,7}$/.test(hn)) {
                showHNError(true);
                disableAppointmentFields(modal);
                clearAppointmentAlert();
                if (nameDisplay) {
                    nameDisplay.value = "";
                    nameDisplay.classList.remove("text-danger");
                }
                return;
            } else {
                showHNError(false);
            }
        }

        // Handle real-time validation for fname/lname
        if (["fname", "lname"].includes(target.id)) {
            const fname = modal.querySelector("#fname")?.value.trim();
            const lname = modal.querySelector("#lname")?.value.trim();

            // ตรวจสอบว่าทั้งสองช่องมีข้อมูลและยาวพอ
            if (fname && lname && fname.length >= 2 && lname.length >= 2) {
                enableAppointmentFields(modal);

                // เช็คว่าเป็นผู้ป่วยนอกหรือไม่ก่อนเช็คประวัติ
                const resourceRadio = modal.querySelector(
                    'input[name="resource"]:checked'
                );
                if (resourceRadio && resourceRadio.value === "out") {
                    // เช็คประวัติการนัดสำหรับผู้ป่วยนอก
                    await checkAppointmentHistory("out", null, fname, lname);
                }
            } else {
                // ถ้าข้อมูลไม่ครบหรือสั้นเกินไป ให้ล็อคช่อง
                disableAppointmentFields(modal);
                clearAppointmentAlert();
            }
        }
    }, 300)
); // Debounce for 300ms

// เพิ่ม event listener เพื่อป้องกัน form submission เมื่อกด Enter
document
    .getElementById("main-content")
    .addEventListener("keypress", function (e) {
        if (e.key === "Enter") {
            const target = e.target;
            const modal = target.closest(".modal");

            if (modal && ["hn", "fname", "lname", "tel"].includes(target.id)) {
                e.preventDefault();
                return false;
            }
        }
    });

// Event listener สำหรับ radio button changes
document
    .getElementById("main-content")
    .addEventListener("change", function (e) {
        const target = e.target;
        const modal = target.closest(".modal");
        if (!modal) return;

        // เมื่อเปลี่ยน resource type
        if (target.name === "resource") {
            const hnInput = modal.querySelector("#hn");
            const fnameInput = modal.querySelector("#fname");
            const lnameInput = modal.querySelector("#lname");
            const nameDisplay = modal.querySelector("#hn_name_display");
            const extraFields = modal.querySelector("#extra-patient-fields");

            // เคลียร์ข้อมูลเก่าและล็อคช่อง
            clearAppointmentAlert();
            disableAppointmentFields(modal);

            if (target.value === "in") {
                // ถ้าเป็นผู้ป่วยใน ให้เคลียร์ชื่อ-นามสกุล
                if (fnameInput) fnameInput.value = "";
                if (lnameInput) lnameInput.value = "";
                if (extraFields) extraFields.style.display = "none";

                // ถ้ามี HN อยู่แล้ว ให้ตรวจสอบใหม่
                if (hnInput && hnInput.value.trim()) {
                    // Trigger HN validation
                    const event = new Event("input", { bubbles: true });
                    hnInput.dispatchEvent(event);
                }
            } else {
                // ถ้าเป็นผู้ป่วยนอก ให้เคลียร์ HN
                if (hnInput) hnInput.value = "";
                if (nameDisplay) {
                    nameDisplay.value = "";
                    nameDisplay.classList.remove("text-danger");
                }
                showHNError(false);

                // ถ้ามีชื่อ-นามสกุลอยู่แล้ว ให้ตรวจสอบใหม่
                if (
                    fnameInput &&
                    lnameInput &&
                    fnameInput.value.trim() &&
                    lnameInput.value.trim()
                ) {
                    // Trigger name validation
                    const event = new Event("input", { bubbles: true });
                    fnameInput.dispatchEvent(event);
                }
            }
        }
    });

// ปุ้มอัพเดตนัด
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("edit-btn")) {
        const id = e.target.dataset.id;
        const form = document.getElementById(`patientInfoForm${id}`);
        if (!form) return;

        // เปลี่ยนปุ่มแก้ไขเป็น "กำลังแก้ไขอยู่..."
        e.target.innerText = "กำลังแก้ไขอยู่...";
        e.target.classList.remove("btn-warning");
        e.target.classList.add("btn-secondary");
        e.target.disabled = true;

        // เปิด input ยกเว้น field ที่ readonly ถาวร
        form.querySelectorAll("input, textarea").forEach((el) => {
            const editableFields = [
                `a_date${id}`,
                `tel${id}`,
                `note${id}`,
                `a_time_start${id}`,
                `a_time_end${id}`,
            ];
            // ซ่อน input:text
            document.getElementById(`a_date_text${id}`).style.display = "none";
            document.getElementById(`a_time_text${id}`).classList.add("d-none");
            // เปิดให้กรอก
            document.getElementById(`a_date${id}`).hidden = false;
            document.getElementById(`a_time_start${id}`).hidden = false;
            document.getElementById(`a_time_end${id}`).hidden = false;

            if (editableFields.includes(el.id)) {
                el.removeAttribute("readonly");
            }
        });

        // เปิดปุ่ม "บันทึก"
        const saveBtn = document.querySelector(
            `#patientInfo${id} .btn-primary`
        );
        if (saveBtn) saveBtn.disabled = false;
    }
});

function disableAppointmentFields() {
    const modal = document.querySelector(".modal.show");
    if (!modal) return;

    // Disable ฟิลด์ต่างๆ
    const fieldsToDisable = [
        "tel",
        "wardSelect",
        "doctorSelect",
        "appointmentDate",
        "note",
    ];
    fieldsToDisable.forEach((id) => {
        const element = modal.querySelector(`#${id}`);
        if (element) element.disabled = true;
    });

    // Disable radio buttons เวลา
    modal
        .querySelectorAll('input[name="appointment_time"]')
        .forEach((radio) => {
            radio.disabled = true;
            radio.checked = false;
        });

    // Disable custom time inputs
    const customTimeInputs = modal.querySelectorAll(
        "#custom_start_time, #custom_end_time"
    );
    customTimeInputs.forEach((input) => {
        input.disabled = true;
    });
}

$(document).on("shown.bs.modal", "#addAppointment", function () {
    const customRadio = document.getElementById("customTime");
    const startTime = document.getElementById("custom_start_time");
    const endTime = document.getElementById("custom_end_time");

    if (!customRadio || !startTime || !endTime) return;

    function toggleCustomTimeInputs() {
        const isCustom = customRadio.checked;
        startTime.disabled = !isCustom;
        endTime.disabled = !isCustom;
    }

    $('input[name="appointment_time"]').on("change", toggleCustomTimeInputs);
    toggleCustomTimeInputs();
});

// แก้ไข event listener ของปุ่ม "บันทึก"
document.addEventListener("click", function (e) {
    const saveBtn = e.target.closest(".save-btn");
    if (saveBtn) {
        const id = saveBtn.dataset.id;
        const form = document.getElementById(`patientInfoForm${id}`);
        if (!form) return;

        const formData = new FormData(form);

        // ดึง CSRF token จาก meta tag
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");

        fetch(`/appointments/update/${id}`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": csrfToken || "",
                Accept: "application/json",
                "X-Requested-With": "XMLHttpRequest",
            },
            body: formData,
        })
            .then((response) => {
                // ตรวจสอบว่าเป็น JSON หรือไม่
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    return response.json();
                } else {
                    return response.text().then((text) => {
                        console.log("Server response:", text);
                        throw new Error(
                            `Server returned HTML instead of JSON. Status: ${response.status}`
                        );
                    });
                }
            })
            .then((data) => {
                if (data.success) {
                    // Lock fields
                    form.querySelectorAll("input, textarea").forEach((el) => {
                        const editableFields = [
                            `a_date${id}`,
                            `tel${id}`,
                            `note${id}`,
                            `a_time_start${id}`,
                            `a_time_end${id}`,
                        ];
                        if (editableFields.includes(el.id)) {
                            el.setAttribute("readonly", true);
                        }
                    });

                    // Disable save button
                    saveBtn.disabled = true;

                    const a_date_input = document.getElementById(`a_date${id}`);
                    const a_date_text = document.getElementById(
                        `a_date_text${id}`
                    );

                    if (a_date_input && a_date_text) {
                        // แปลงค่าวันที่เป็นแบบไทย แล้วใส่กลับไปที่ช่องแสดงผล
                        a_date_text.value = formatThaiDate(a_date_input.value);
                        a_date_input.hidden = true;
                        a_date_text.style.display = "inline";
                    }

                    const start = document.getElementById(`a_time_start${id}`);
                    const end = document.getElementById(`a_time_end${id}`);
                    const a_time_text = document.getElementById(
                        `a_time_text${id}`
                    );

                    if (start && end && a_time_text) {
                        const timeValue = `${start.value}-${end.value}`;
                        a_time_text.value = timeValue;

                        // ซ่อน input เวลา และแสดงช่อง text
                        start.hidden = true;
                        end.hidden = true;
                        a_time_text.classList.remove("d-none");
                        a_time_text.style.display = "inline"; // ป้องกัน display: none
                    }

                    // เปลี่ยนปุ่ม "กำลังแก้ไขอยู่..." กลับเป็น "แก้ไข"
                    const editBtn = document.querySelector(
                        `.edit-btn[data-id="${id}"]`
                    );
                    if (editBtn) {
                        editBtn.innerText = "แก้ไข";
                        editBtn.classList.remove("btn-secondary");
                        editBtn.classList.add("btn-warning");
                        editBtn.disabled = false; // ← สำคัญ! เปิดใช้งานปุ่มแก้ไขใหม่
                    }

                    // แสดง sweetalert
                    Swal.fire({
                        icon: "success",
                        title: "บันทึกสำเร็จ",
                        text: "ข้อมูลได้รับการอัปเดตเรียบร้อยแล้ว",
                        confirmButtonText: "ตกลง",
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "เกิดข้อผิดพลาด",
                        text: data.message || "ไม่สามารถบันทึกข้อมูลได้",
                    });
                }
            })
            .catch((error) => {
                console.error("Fetch error:", error);

                // หากเกิดข้อผิดพลาด ให้เปิดปุ่มแก้ไขกลับมาด้วย
                const editBtn = document.querySelector(
                    `.edit-btn[data-id="${id}"]`
                );
                if (editBtn) {
                    editBtn.innerText = "แก้ไข";
                    editBtn.classList.remove("btn-secondary");
                    editBtn.classList.add("btn-warning");
                    editBtn.disabled = false;
                }

                Swal.fire({
                    icon: "error",
                    title: "เซิร์ฟเวอร์ผิดพลาด",
                    text: "ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้",
                });
            });
    }
});

function formatThaiDate(dateStr) {
    const months = [
        "มกราคม",
        "กุมภาพันธ์",
        "มีนาคม",
        "เมษายน",
        "พฤษภาคม",
        "มิถุนายน",
        "กรกฎาคม",
        "สิงหาคม",
        "กันยายน",
        "ตุลาคม",
        "พฤศจิกายน",
        "ธันวาคม",
    ];

    const [year, month, day] = dateStr.split("-");
    const thaiYear = parseInt(year) + 543;
    const monthName = months[parseInt(month) - 1];

    return `${parseInt(day)} ${monthName} ${thaiYear}`;
}
