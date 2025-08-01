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

//ทำต่อตรงนี้นะจ๊ะตะเอง
document
    .getElementById("main-content")
    .addEventListener("keydown", async function (e) {
        if (e.key === "Enter") {
            const target = e.target;
            const modal = target.closest(".modal");
            if (!modal) return;

            // ตรวจสอบว่าเป็นฟอร์มไหน
            const isAppointmentModal = modal.id === "addAppointment";
            const isTreatmentModal = modal.id === "addTreatment";

            // ป้องกันการ submit form เมื่อกด Enter
            if (
                target.id === "hn" ||
                target.id === "fname" ||
                target.id === "lname" ||
                target.name === "fname" || // ช่องชื่อในฟอร์ม Treatment
                target.name === "lname" || // ช่องนามสกุลในฟอร์ม Treatment
                target.tagName === "SELECT" ||
                target.id === "titleName"
            ) {
                e.preventDefault();
                e.stopPropagation();
            }

            // === APPOINTMENT MODAL LOGIC ===
            if (isAppointmentModal) {
                await handleAppointmentModal(e, target, modal);
            }

            // === TREATMENT MODAL LOGIC ===
            if (isTreatmentModal) {
                await handleTreatmentModal(e, target, modal);
            }
        }
    });

// เพิ่ม Event Listener สำหรับ Radio Button ในฟอร์ม Treatment
document.addEventListener("change", function (e) {
    const target = e.target;
    const modal = target.closest(".modal");

    // ตรวจสอบว่าเป็นการเปลี่ยน radio button ของ resource ในฟอร์ม Treatment
    if (modal && modal.id === "addTreatment" && target.name === "resource") {
        handleTreatmentResourceChange(target, modal);
    }
});

// ฟังก์ชันจัดการการเปลี่ยน Radio Button ในฟอร์ม Treatment
function handleTreatmentResourceChange(target, modal) {
    const isOutPatient = target.value === "out";

    // เคลียร์และล็อคช่องต่างๆ เมื่อเปลี่ยน radio
    clearAndDisableTreatmentFields(modal);

    // เคลียร์ข้อมูลผู้ป่วยทั้งหมด
    clearTreatmentPatientData(modal);

    console.log(
        "Treatment - เปลี่ยนประเภทผู้ป่วยเป็น:",
        isOutPatient ? "นอก" : "ใน"
    );
}

// ฟังก์ชันสำหรับจัดการ Appointment Modal
async function handleAppointmentModal(e, target, modal) {
    // เมื่อกด Enter ในช่องโรงพยาบาล - ให้ตรวจสอบข้อมูลและเปิดใช้งานช่องอื่น
    if (target.id === "hospital_name") {
        const fname = modal.querySelector("#fname")?.value.trim();
        const lname = modal.querySelector("#lname")?.value.trim();
        const hospitalName = target.value.trim();

        // ตรวจสอบว่ามีข้อมูลครบหรือไม่
        if (fname && lname && hospitalName) {
            enableAppointmentFields(modal);

            // เช็คประวัติการนัดสำหรับผู้ป่วยนอก
            const resourceRadio = modal.querySelector(
                'input[name="resource"]:checked'
            );
            if (resourceRadio && resourceRadio.value === "out") {
                await checkAppointmentHistory("out", null, fname, lname);
            }
        }
        return;
    }

    const hnInput = modal.querySelector("#hn");
    const nameDisplay = modal.querySelector("#hn_name_display");
    const extraFields = modal.querySelector("#extra-patient-fields");

    // เมื่อพิมพ์ HN ในฟอร์ม Appointment
    if (target.id === "hn") {
        await handleAppointmentHNInput(target, modal, nameDisplay, extraFields);
    }

    // เมื่อพิมพ์ชื่อหรือนามสกุลในฟอร์ม Appointment
    if (["fname", "lname"].includes(target.id)) {
        await handleAppointmentNameInput(target, modal);
    }
}

// ฟังก์ชันสำหรับจัดการ Treatment Modal
async function handleTreatmentModal(e, target, modal) {
    const hnInput = modal.querySelector("#hn");
    const nameDisplay = modal.querySelector("#hn_name_display");

    // ตรวจสอบว่าเป็นผู้ป่วยในหรือนอก
    const resourceRadio = modal.querySelector('input[name="resource"]:checked');
    const isOutPatient = resourceRadio && resourceRadio.value === "out";

    // เมื่อพิมพ์ HN ในฟอร์ม Treatment (เฉพาะผู้ป่วยใน)
    if (target.id === "hn" && !isOutPatient) {
        await handleTreatmentHNInput(target, modal, nameDisplay);
    }

    // เมื่อพิมพ์ชื่อหรือนามสกุลในฟอร์ม Treatment
    if (target.name === "fname" || target.name === "lname") {
        await handleTreatmentNameInput(target, modal, isOutPatient);
    }

    // เมื่อเลือกวันที่รักษาในฟอร์ม Treatment
    if (target.id === "t_date") {
        handleTreatmentDateInput(target, modal);
    }

    // เมื่อเลือกหน่วยงานในฟอร์ม Treatment
    if (target.id === "agency") {
        handleTreatmentAgencyInput(target, modal);
    }
}

// === APPOINTMENT MODAL FUNCTIONS ===
async function handleAppointmentHNInput(
    target,
    modal,
    nameDisplay,
    extraFields
) {
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
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
            }
        );

        if (!res.ok) {
            throw new Error(`HTTP ${res.status}: ${res.statusText}`);
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

        // ล็อคช่องต่างๆ เมื่อเกิดข้อผิดพลาด
        disableTreatmentFields(modal);
        disableAppointmentFields(modal);
        clearAppointmentAlert();

        // แสดง error alert
        showAppointmentAlert(
            "ไม่สามารถเชื่อมต่อกับระบบได้ กรุณาลองใหม่อีกครั้ง",
            "danger"
        );
    }
}

async function handleAppointmentNameInput(target, modal) {
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
            await checkAppointmentHistory("out", null, fname, lname);
        }
    } else {
        disableAppointmentFields(modal);
        clearAppointmentAlert();
    }
}

// === TREATMENT MODAL FUNCTIONS ===
async function handleTreatmentHNInput(target, modal, nameDisplay) {
    const hn = target.value.trim();

    if (!hn) {
        if (nameDisplay) {
            nameDisplay.value = "";
            nameDisplay.classList.remove("text-danger");
        }
        // ล็อคช่องต่างๆ เมื่อไม่มี HN
        disableTreatmentFields(modal);
        return;
    }

    // Validate HN format - รองรับ HN ที่มีตัวเลข 1-7 หลัก
    if (!/^\d{1,7}$/.test(hn)) {
        showTreatmentHNError(true);
        if (nameDisplay) {
            nameDisplay.value = "";
            nameDisplay.classList.remove("text-danger");
        }
        return;
    } else {
        showTreatmentHNError(false);
    }

    // แสดง loading state
    if (nameDisplay) {
        nameDisplay.value = "กำลังโหลด...";
        nameDisplay.classList.remove("text-danger");
    }

    try {
        // เช็คชื่อผู้ป่วยสำหรับฟอร์ม Treatment
        const res = await fetch(
            `/api/patient-name?hn=${encodeURIComponent(hn)}`,
            {
                method: "GET",
                headers: {
                    Accept: "application/json",
                    "X-CSRF-TOKEN":
                        document
                            .querySelector('meta[name="csrf-token"]')
                            ?.getAttribute("content") || "",
                },
            }
        );

        if (!res.ok) {
            throw new Error(`HTTP ${res.status}: ${res.statusText}`);
        }

        const data = await res.json();

        if (data.name && nameDisplay) {
            nameDisplay.value = data.name;
            nameDisplay.classList.remove("text-danger");

            // ปลดล็อคช่องต่างๆ เมื่อพบชื่อผู้ป่วย
            enableTreatmentFields(modal);
        } else {
            if (nameDisplay) {
                nameDisplay.value = "ไม่พบข้อมูล";
                nameDisplay.classList.add("text-danger");
            }
            // ล็อคช่องต่างๆ เมื่อไม่พบข้อมูล
            disableTreatmentFields(modal);
        }
    } catch (err) {
        console.error("เกิดข้อผิดพลาดในการดึงข้อมูล Treatment:", err);
        if (nameDisplay) {
            nameDisplay.value = "เกิดข้อผิดพลาดในการโหลดข้อมูล";
            nameDisplay.classList.add("text-danger");
        }

        // TODO: แสดง error alert สำหรับ treatment
        // showTreatmentAlert("ไม่สามารถเชื่อมต่อกับระบบได้ กรุณาลองใหม่อีกครั้ง", "danger");
    }
}

async function handleTreatmentNameInput(target, modal, isOutPatient) {
    // ดึงค่าชื่อและนามสกุลจากฟิลด์ที่ถูกต้อง
    const fname = modal.querySelector('input[name="fname"]')?.value.trim();
    const lname = modal.querySelector('input[name="lname"]')?.value.trim();

    // ถ้าเป็นผู้ป่วยนอก ให้เช็คเมื่อกรอกครบทั้งชื่อและนามสกุล
    if (isOutPatient && fname && lname) {
        console.log(
            "Treatment - ผู้ป่วยนอก - ชื่อและนามสกุลครบแล้ว:",
            fname,
            lname
        );

        // ปลดล็อคช่องต่างๆ เมื่อกรอกชื่อ-นามสกุลครบ
        enableTreatmentFields(modal);
    } else if (!isOutPatient) {
        // ถ้าเป็นผู้ป่วยใน ไม่ควรมีการกรอกชื่อ-นามสกุลแยก (ควรใช้ HN)
        console.log("Treatment - ผู้ป่วยใน - ไม่ควรกรอกชื่อแยก");
    } else {
        // ถ้าเป็นผู้ป่วยนอกแต่ยังกรอกไม่ครบ
        console.log("Treatment - ผู้ป่วยนอก - ยังกรอกข้อมูลไม่ครบ");
        disableTreatmentFields(modal);
    }
}

function handleTreatmentDateInput(target, modal) {
    const selectedDate = target.value;

    // TODO: เพิ่ม logic สำหรับการจัดการวันที่รักษา
    console.log("Treatment - เลือกวันที่:", selectedDate);
    // TODO: อาจจะตรวจสอบว่าวันที่ถูกต้องหรือไม่, enable fields อื่นๆ
}

function handleTreatmentAgencyInput(target, modal) {
    const selectedAgency = target.value;

    // TODO: เพิ่ม logic สำหรับการจัดการหน่วยงาน
    console.log("Treatment - เลือกหน่วยงาน:", selectedAgency);
    // TODO: อาจจะ auto-fill ข้อมูลอื่นๆ หรือ enable/disable fields ตามหน่วยงานที่เลือก
}

// === UTILITY FUNCTIONS ===
// TODO: เพิ่มฟังก์ชันเหล่านี้สำหรับ Treatment Modal
function showTreatmentHNError(show) {
    const errorElement = document.querySelector("#addTreatment #hn-error");
    if (errorElement) {
        if (show) {
            errorElement.classList.remove("d-none");
        } else {
            errorElement.classList.add("d-none");
        }
    }
}

// TODO: เพิ่มฟังก์ชันอื่นๆ ที่จำเป็นสำหรับ Treatment Modal
function enableTreatmentFields(modal) {
    // ปลดล็อคช่องวันที่รักษา
    const tDateField = modal.querySelector("#t_date");
    if (tDateField) tDateField.disabled = false;

    // ปลดล็อคช่องหน่วยงาน
    const agencyField = modal.querySelector("#agency");
    if (agencyField) agencyField.disabled = false;

    // ปลดล็อคช่องส่งต่อ
    const forwardField = modal.querySelector("#forward");
    if (forwardField) forwardField.disabled = false;
}

function disableTreatmentFields(modal) {
    // ล็อคช่องวันที่รักษา
    const tDateField = modal.querySelector("#t_date");
    if (tDateField) {
        tDateField.disabled = true;
        tDateField.value = "";
    }

    // ล็อคช่องหน่วยงาน
    const agencyField = modal.querySelector("#agency");
    if (agencyField) {
        agencyField.disabled = true;
        agencyField.value = "";
    }

    // ล็อคช่องส่งต่อ
    const forwardField = modal.querySelector("#forward");
    if (forwardField) {
        forwardField.disabled = true;
        forwardField.value = "";
    }
}

function clearAndDisableTreatmentFields(modal) {
    // เคลียร์และล็อคช่องวันที่รักษา
    const tDateField = modal.querySelector("#t_date");
    if (tDateField) {
        tDateField.disabled = true;
        tDateField.value = "";
    }

    // เคลียร์และล็อคช่องหน่วยงาน
    const agencyField = modal.querySelector("#agency");
    if (agencyField) {
        agencyField.disabled = true;
        agencyField.value = "";
    }

    // เคลียร์และล็อคช่องส่งต่อ
    const forwardField = modal.querySelector("#forward");
    if (forwardField) {
        forwardField.disabled = true;
        forwardField.value = "";
    }
}

function clearTreatmentPatientData(modal) {
    // เคลียร์ HN
    const hnField = modal.querySelector("#hn");
    if (hnField) {
        hnField.value = "";
    }

    // เคลียร์ชื่อผู้ป่วยที่แสดง
    const nameDisplay = modal.querySelector("#hn_name_display");
    if (nameDisplay) {
        nameDisplay.value = "";
        nameDisplay.classList.remove("text-danger");
    }

    // เคลียร์ชื่อ-นามสกุล
    const fnameField = modal.querySelector('input[name="fname"]');
    const lnameField = modal.querySelector('input[name="lname"]');
    if (fnameField) fnameField.value = "";
    if (lnameField) lnameField.value = "";

    // ซ่อน error message ของ HN
    const hnError = modal.querySelector("#hn-error");
    if (hnError) {
        hnError.classList.add("d-none");
    }
}

// function showTreatmentAlert(message, type) { ... }
// function clearTreatmentAlert() { ... }
// function checkTreatmentHistory(type, hn, fname, lname) { ... }

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
    if (!resource || (resource !== "in" && resource !== "out")) return;

    const modal = document.querySelector(".modal.show");
    if (!modal) return;

    let checkData = { resource };

    if (resource === "in") {
        if (!hn || hn.trim() === "") return;
        checkData.hn = hn.trim();
    } else if (resource === "out") {
        if (!fname || !lname || fname.trim() === "" || lname.trim() === "")
            return;
        checkData.fname = fname.trim();
        checkData.lname = lname.trim();
    }

    try {
        const csrfToken = document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute("content");
        if (!csrfToken) {
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

        const responseText = await response.text();
        if (!response.ok || !responseText.trim()) {
            let errorMessage = "เกิดข้อผิดพลาดในการเชื่อมต่อ";
            if (response.status === 404) errorMessage = "ไม่พบ API endpoint";
            else if (response.status === 422)
                errorMessage = "ข้อมูลที่ส่งไม่ถูกต้อง";
            else if (response.status === 500)
                errorMessage = "เกิดข้อผิดพลาดในระบบ";
            else if (!responseText.trim())
                errorMessage = "ไม่ได้รับข้อมูลจากระบบ";

            showAppointmentAlert(errorMessage, "danger");
            return;
        }

        let data;
        try {
            data = JSON.parse(responseText);
        } catch {
            showAppointmentAlert("ข้อมูลที่ได้รับจากระบบไม่ถูกต้อง", "danger");
            return;
        }

        clearAppointmentAlert();

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
                disableAppointmentFields(modal);
                showAppointmentAlert(
                    data.message || "เกิดข้อผิดพลาดในการค้นหา",
                    "danger"
                );
                break;
            default:
                showAppointmentAlert("ได้รับข้อมูลที่ไม่คาดคิด", "warning");
        }
    } catch (error) {
        clearAppointmentAlert();
        let errorMessage = "เกิดข้อผิดพลาดในการตรวจสอบข้อมูล";
        if (error.message.includes("Failed to fetch"))
            errorMessage = "ไม่สามารถเชื่อมต่อกับระบบได้";
        else if (error.message.includes("NetworkError"))
            errorMessage = "เกิดปัญหาการเชื่อมต่อเครือข่าย";

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
                // enableAppointmentFields(modal);

                // เช็คว่าเป็นผู้ป่วยนอกหรือไม่ก่อนเช็คประวัติ
                const resourceRadio = modal.querySelector(
                    'input[name="resource"]:checked'
                );
                // if (resourceRadio && resourceRadio.value === "out") {
                //     // เช็คประวัติการนัดสำหรับผู้ป่วยนอก
                //     await checkAppointmentHistory("out", null, fname, lname);
                // }
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

        // ซ่อน input:text
        document.getElementById(`a_date_text${id}`).style.display = "none";
        document.getElementById(`a_time_text${id}`).classList.add("d-none");

        // เปิด input
        const dateInput = document.getElementById(`a_date${id}`);
        const timeStartInput = document.getElementById(`a_time_start${id}`);
        const timeEndInput = document.getElementById(`a_time_end${id}`);

        dateInput.hidden = false;
        timeStartInput.hidden = false;
        timeEndInput.hidden = false;

        // เปิดให้กรอก
        form.querySelectorAll("input, textarea").forEach((el) => {
            const editableFields = [
                `a_date${id}`,
                `tel${id}`,
                `note${id}`,
                `a_time_start${id}`,
                `a_time_end${id}`,
            ];
            if (editableFields.includes(el.id)) {
                el.removeAttribute("readonly");
            }
        });

        // ✅ Flatpickr: วันนัด (ห้ามย้อนหลัง)
        flatpickr(dateInput, {
            dateFormat: "Y-m-d",
            minDate: "today",
            allowInput: true,
        });

        // ✅ Flatpickr: เวลาเริ่ม (เริ่มไม่ต่ำกว่า 08:00)
        flatpickr(timeStartInput, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            minTime: "08:00",
            allowInput: true,
        });

        // ✅ Flatpickr: เวลาสิ้นสุด (เลือกได้อิสระ แต่ใช้ 24 ชม)
        flatpickr(timeEndInput, {
            enableTime: true,
            noCalendar: true,
            dateFormat: "H:i",
            time_24hr: true,
            allowInput: true,
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
