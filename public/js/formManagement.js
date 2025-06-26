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
    .addEventListener("input", async function (e) {
        const target = e.target;
        const modal = target.closest(".modal");
        if (!modal) return;

        const hnInput = modal.querySelector("#hn");
        const nameDisplay = modal.querySelector("#hn_name_display");
        const extraFields = modal.querySelector("#extra-patient-fields");

        // เมื่อพิมพ์ HN
        if (target.id === "hn") {
            const hn = target.value.trim();
            if (!hn) {
                if (nameDisplay) nameDisplay.value = "";
                clearAppointmentAlert();
                return;
            }

            // Validate HN format
            if (!/^\d{1,7}$/.test(hn)) {
                showHNError(true);
                disableAppointmentFields();
                clearAppointmentAlert();
                return;
            } else {
                showHNError(false);
            }

            try {
                // เช็คชื่อผู้ป่วยก่อน
                const res = await fetch(
                    `/api/patient-name?hn=${encodeURIComponent(hn)}`
                );
                const data = await res.json();

                if (data.name && nameDisplay) {
                    nameDisplay.value = data.name;
                    nameDisplay.classList.remove("text-danger");
                    if (extraFields) extraFields.style.display = "none";

                    // เช็คประวัติการนัดหลังจากพบชื่อ
                    await checkAppointmentHistory("in", hn);
                } else {
                    nameDisplay.value = "ไม่พบข้อมูล";
                    nameDisplay.classList.add("text-danger");
                    if (extraFields) extraFields.style.display = "block";
                    clearAppointmentAlert();
                }

                // ช่องที่ต้องล็อกหรือปลดล็อก
                const fieldsToToggle = [
                    "tel",
                    "wardSelect",
                    "doctorSelect",
                    "appointmentDate",
                    "note",
                ];
                fieldsToToggle.forEach((id) => {
                    const el = modal.querySelector(`#${id}`);
                    if (el) el.disabled = !data.name;
                });

                // จัดการ radio แยก
                const radios = modal.querySelectorAll(
                    'input[name="appointment_time"]'
                );
                radios.forEach((radio) => {
                    radio.disabled = !data.name;
                });
            } catch (err) {
                console.log("เกิดข้อผิดพลาดในการดึงข้อมูล:", err);
                if (nameDisplay) nameDisplay.value = "โหลดข้อมูลผิดพลาด";
                clearAppointmentAlert();
            }
        }

        // เมื่อพิมพ์ชื่อหรือนามสกุล
        if (["fname", "lname"].includes(target.id)) {
            const fname = modal.querySelector("#fname")?.value.trim();
            const lname = modal.querySelector("#lname")?.value.trim();

            const shouldEnable = fname && lname;

            // ปลดล็อกฟิลด์ทั่วไป
            const fieldsToToggle = [
                "tel",
                "wardSelect",
                "doctorSelect",
                "appointmentDate",
                "note",
            ];
            fieldsToToggle.forEach((id) => {
                const el = modal.querySelector(`#${id}`);
                if (el) el.disabled = !shouldEnable;
            });

            // ปลดล็อก radio ของช่วงเวลา
            const radios = modal.querySelectorAll(
                'input[name="appointment_time"]'
            );
            radios.forEach((radio) => {
                radio.disabled = !shouldEnable;
            });

            // ปลดล็อก custom time input ด้วย
            const customTimeInputs = modal.querySelectorAll(
                "#custom_start_time, #custom_end_time"
            );
            customTimeInputs.forEach((input) => {
                input.disabled = !shouldEnable;
            });

            // เช็คประวัติการนัดเมื่อกรอกครบชื่อ-นามสกุล
            if (shouldEnable) {
                await checkAppointmentHistory("out", null, fname, lname);
            } else {
                clearAppointmentAlert();
            }
        }
    });

// ฟังก์ชันเช็คประวัติการนัด
async function checkAppointmentHistory(
    resource,
    hn = null,
    fname = null,
    lname = null
) {
    let checkData = { resource: resource };

    if (resource === "in" && hn) {
        checkData.hn = hn;
    } else if (resource === "out" && fname && lname) {
        checkData.fname = fname;
        checkData.lname = lname;
    } else {
        console.log("Missing required data for appointment check");
        return;
    }

    console.log("Sending data:", checkData);

    try {
        const response = await fetch(
            `${window.location.origin}/check-appointment-history`,
            {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document
                        .querySelector('meta[name="csrf-token"]')
                        .getAttribute("content"),
                    Accept: "application/json",
                },
                body: JSON.stringify(checkData),
            }
        );

        console.log("Response status:", response.status);
        console.log("Response headers:", response.headers);

        // แสดง response text ก่อนแปลง JSON
        const responseText = await response.text();
        console.log("Raw response:", responseText);

        // เช็ค response status ก่อน
        if (!response.ok) {
            throw new Error(
                `HTTP error! status: ${response.status}, body: ${responseText}`
            );
        }

        // พยายามแปลง JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (jsonError) {
            console.error("JSON parse error:", jsonError);
            throw new Error("Invalid JSON response from server");
        }

        console.log("Parsed data:", data);
        clearAppointmentAlert();

        if (data.status === "found") {
            showAppointmentAlert(data.message, "warning");
        } else if (data.status === "no_history") {
            showAppointmentAlert("ไม่พบประวัติการนัดหมายก่อนหน้า", "info");
        } else if (data.status === "not_found") {
            showAppointmentAlert(data.message, "danger");
        } else if (data.status === "error") {
            showAppointmentAlert(data.message, "danger");
        }
    } catch (error) {
        console.error("Full error details:", error);
        showAppointmentAlert(
            "เกิดข้อผิดพลาดในการตรวจสอบข้อมูล: " + error.message,
            "danger"
        );
    }
}

function showAppointmentAlert(message, type) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show mt-2" role="alert" id="appointment-alert">
            <i class="fas fa-info-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;

    // หา modal ที่เปิดอยู่
    const modal = document.querySelector(".modal.show");
    if (modal) {
        // หาจุดที่เหมาะสมในการแสดง alert
        const resource = modal.querySelector('input[name="resource"]:checked');
        let targetElement;

        if (resource && resource.value === "in") {
            targetElement =
                modal.querySelector("#hn-group") ||
                modal.querySelector("#hn").parentElement;
        } else {
            targetElement =
                modal.querySelector("#hospital-group") ||
                modal.querySelector("#fname").parentElement;
        }

        if (targetElement) {
            targetElement.insertAdjacentHTML("afterend", alertHtml);
        }
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
