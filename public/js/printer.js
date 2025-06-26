// สำรองไว้ถ้าไม่มี jQuery
if (typeof $ === "undefined") {
    document.addEventListener("DOMContentLoaded", function () {
        console.log("PDF Script loaded (vanilla JS)");

        // ใช้ event delegation
        document.addEventListener("click", function (e) {
            if (
                e.target.classList.contains("print-btn") ||
                e.target.closest(".print-btn")
            ) {
                console.log("Print button clicked");
                const button = e.target.classList.contains("print-btn")
                    ? e.target
                    : e.target.closest(".print-btn");
                const patientId = button.dataset.id;
                console.log("Patient ID:", patientId);
                showPDFPreview(patientId);
            }

            if (
                e.target.classList.contains("download-pdf-btn") ||
                e.target.closest(".download-pdf-btn")
            ) {
                const button = e.target.classList.contains("download-pdf-btn")
                    ? e.target
                    : e.target.closest(".download-pdf-btn");
                const patientId = button.dataset.id;
                generatePDF(patientId, true);
            }

            if (
                e.target.classList.contains("close-preview-btn") ||
                e.target.closest(".close-preview-btn")
            ) {
                const button = e.target.classList.contains("close-preview-btn")
                    ? e.target
                    : e.target.closest(".close-preview-btn");
                const patientId = button.dataset.id;
                document.getElementById(
                    `pdfPreview${patientId}`
                ).style.display = "none";
            }
        });
    });
}

function showPDFPreview(patientId) {
    console.log(`showPDFPreview called with ID: ${patientId}`); // debug

    try {
        // ตรวจสอบว่า element มีอยู่หรือไม่
        const printDateElement = document.getElementById(
            `printDate${patientId}`
        );
        const previewElement = document.getElementById(
            `pdfPreview${patientId}`
        );
        const contentElement = document.getElementById(
            `printableContent${patientId}`
        );

        console.log("Elements found:", {
            printDate: !!printDateElement,
            preview: !!previewElement,
            content: !!contentElement,
        });

        if (!printDateElement || !previewElement || !contentElement) {
            alert(`ไม่พบ element ที่จำเป็น สำหรับ Patient ID: ${patientId}`);
            return;
        }

        // แสดงวันที่พิมพ์
        const now = new Date();
        const thaiDate = now.toLocaleDateString("th-TH", {
            year: "numeric",
            month: "long",
            day: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
        printDateElement.textContent = thaiDate;

        // แสดง preview container
        previewElement.style.display = "block";

        // สร้าง preview
        generatePDF(patientId, false);
    } catch (error) {
        console.error("Error in showPDFPreview:", error);
        alert("เกิดข้อผิดพลาด: " + error.message);
    }
}

function generatePDF(patientId, download = false) {
    console.log(
        `generatePDF called with ID: ${patientId}, download: ${download}`
    ); // debug

    const element = document.getElementById(`printableContent${patientId}`);
    const canvas = document.getElementById(`pdfCanvas${patientId}`);

    // ตรวจสอบว่า element มีอยู่หรือไม่
    if (!element) {
        console.error(`ไม่พบ element: printableContent${patientId}`);
        alert("ไม่สามารถหา element ที่ต้องการพิมพ์ได้");
        return;
    }

    if (!canvas) {
        console.error(`ไม่พบ canvas: pdfCanvas${patientId}`);
        alert("ไม่สามารถหา canvas สำหรับแสดงผลได้");
        return;
    }

    // ตรวจสอบว่า library โหลดแล้วหรือยัง
    if (typeof html2canvas === "undefined") {
        console.error("html2canvas library ยังไม่โหลด");
        alert("กรุณารอสักครู่ แล้วลองใหม่อีกครั้ง (library กำลังโหลด)");
        return;
    }

    console.log("Starting html2canvas..."); // debug

    // กำหนดค่า html2canvas
    const options = {
        scale: 2,
        useCORS: true,
        allowTaint: true,
        backgroundColor: "#ffffff",
        width: element.scrollWidth,
        height: element.scrollHeight,
        logging: true, // เพิ่ม logging สำหรับ debug
        onrendered: function (canvas) {
            console.log("html2canvas completed"); // debug
        },
    };

    html2canvas(element, options)
        .then(function (canvasResult) {
            console.log("html2canvas success"); // debug
            const imgData = canvasResult.toDataURL("image/png");

            if (download) {
                // ตรวจสอบ jsPDF
                if (typeof window.jspdf === "undefined") {
                    console.error("jsPDF library ยังไม่โหลด");
                    alert(
                        "กรุณารอสักครู่ แล้วลองใหม่อีกครั้ง (PDF library กำลังโหลด)"
                    );
                    return;
                }

                // สร้าง PDF สำหรับดาวน์โหลด
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF("p", "mm", "a4");

                const imgWidth = 210; // A4 width in mm
                const pageHeight = 295; // A4 height in mm
                const imgHeight =
                    (canvasResult.height * imgWidth) / canvasResult.width;
                let heightLeft = imgHeight;
                let position = 0;

                // เพิ่มหน้าแรก
                pdf.addImage(imgData, "PNG", 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                // เพิ่มหน้าต่อไปถ้าจำเป็น
                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(
                        imgData,
                        "PNG",
                        0,
                        position,
                        imgWidth,
                        imgHeight
                    );
                    heightLeft -= pageHeight;
                }

                // ดาวน์โหลด PDF
                const hnElement = document.getElementById(`hn${patientId}`);
                const hn = hnElement
                    ? hnElement.value.split(" - ")[0]
                    : "unknown";
                const fileName = `patient_info_${hn}_${new Date().getTime()}.pdf`;
                console.log(`Downloading PDF: ${fileName}`); // debug
                pdf.save(fileName);
            } else {
                // แสดง preview
                const previewContainer = document.getElementById(
                    `pdfCanvas${patientId}`
                );
                previewContainer.innerHTML = "";

                const img = document.createElement("img");
                img.src = imgData;
                img.style.width = "100%";
                img.style.height = "auto";
                img.style.border = "1px solid #ddd";
                img.style.borderRadius = "4px";

                previewContainer.appendChild(img);
                console.log("Preview image added"); // debug
            }
        })
        .catch(function (error) {
            console.error("เกิดข้อผิดพลาดใน html2canvas:", error);
            alert("เกิดข้อผิดพลาดในการสร้าง PDF: " + error.message);
        });
}
