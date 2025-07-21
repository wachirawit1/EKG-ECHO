// ฟังก์ชันสำหรับสร้าง PDF พร้อมเลย์เอาต์ที่สวยงาม
function generatePDFInNewTab(patientId) {
    console.log(`generatePDFInNewTab called with ID: ${patientId}`);

    // ตรวจสอบว่า library โหลดแล้วหรือยัง
    if (typeof html2canvas === "undefined") {
        console.error("html2canvas library ยังไม่โหลด");
        alert("กรุณารอสักครู่ แล้วลองใหม่อีกครั้ง (library กำลังโหลด)");
        return;
    }

    if (typeof window.jspdf === "undefined") {
        console.error("jsPDF library ยังไม่โหลด");
        alert("กรุณารอสักครู่ แล้วลองใหม่อีกครั้ง (PDF library กำลังโหลด)");
        return;
    }

    // ดึงข้อมูลจาก form
    const patientData = extractPatientData(patientId);
    if (!patientData) {
        alert("ไม่สามารถดึงข้อมูลผู้ป่วยได้");
        return;
    }

    // สร้าง HTML สำหรับ PDF
    const pdfHTML = createPDFHTML(patientData);

    // สร้าง element ชั่วคราวสำหรับ render
    const tempElement = document.createElement("div");
    tempElement.innerHTML = pdfHTML;
    tempElement.style.position = "absolute";
    tempElement.style.left = "-9999px";
    tempElement.style.top = "-9999px";
    tempElement.style.width = "794px"; // A4 width in pixels (210mm * 3.78)
    tempElement.style.backgroundColor = "white";
    tempElement.style.padding = "40px";
    tempElement.style.fontFamily = "Arial, sans-serif";

    document.body.appendChild(tempElement);

    console.log("Starting html2canvas for PDF generation...");

    // กำหนดค่า html2canvas
    const options = {
        scale: 2,
        useCORS: true,
        allowTaint: true,
        backgroundColor: "#ffffff",
        width: 794,
        height: tempElement.scrollHeight,
        logging: false,
    };

    html2canvas(tempElement, options)
        .then(function (canvasResult) {
            console.log("html2canvas success");

            // ลบ element ชั่วคราว
            document.body.removeChild(tempElement);

            // สร้าง PDF
            const { jsPDF } = window.jspdf;
            const pdf = new jsPDF("p", "mm", "a4");

            const imgData = canvasResult.toDataURL("image/png");
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
                pdf.addImage(imgData, "PNG", 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }

            // สร้าง blob URL และเปิดใน tab ใหม่
            const pdfBlob = pdf.output("blob");
            const blobUrl = URL.createObjectURL(pdfBlob);

            // เปิด tab ใหม่
            const newTab = window.open(blobUrl, "_blank");

            // ตรวจสอบว่าเปิด tab ได้หรือไม่
            if (!newTab) {
                // ถ้าเปิด tab ไม่ได้ ให้ใช้ iframe ซ่อนแล้วพิมพ์
                const iframe = document.createElement("iframe");
                iframe.style.display = "none";
                iframe.src = blobUrl;
                document.body.appendChild(iframe);

                iframe.onload = function () {
                    iframe.contentWindow.print();
                    // ลบ iframe หลังจากพิมพ์เสร็จ
                    setTimeout(() => {
                        document.body.removeChild(iframe);
                        URL.revokeObjectURL(blobUrl);
                    }, 2000);
                };
            } else {
                console.log("PDF opened in new tab successfully");

                // รอให้ PDF โหลดเสร็จแล้วเปิด print dialog
                setTimeout(() => {
                    newTab.focus();
                    newTab.print();

                    // ล้าง blob URL หลังจากพิมพ์เสร็จ
                    setTimeout(() => {
                        URL.revokeObjectURL(blobUrl);
                    }, 2000);
                }, 1000);
            }
        })
        .catch(function (error) {
            console.error("เกิดข้อผิดพลาดใน html2canvas:", error);
            alert("เกิดข้อผิดพลาดในการสร้าง PDF: " + error.message);
            // ลบ element ชั่วคราวในกรณีที่เกิดข้อผิดพลาด
            if (document.body.contains(tempElement)) {
                document.body.removeChild(tempElement);
            }
        });
}

// ฟังก์ชันสำหรับดึงข้อมูลจาก form
function extractPatientData(patientId) {
    try {
        const hnElement = document.getElementById(`hn${patientId}`);
        const ageElement = document.getElementById(`age${patientId}`);
        const addressElement = document.getElementById(`address${patientId}`);
        const dateElement = document.getElementById(`a_date_text${patientId}`);
        const timeElement = document.getElementById(`a_time_text${patientId}`);
        const telElement = document.getElementById(`tel${patientId}`);
        const noteElement = document.getElementById(`note${patientId}`);

        if (!hnElement) {
            console.error(`ไม่พบข้อมูลผู้ป่วย ID: ${patientId}`);
            return null;
        }

        // แยก HN และชื่อผู้ป่วย
        const hnValue = hnElement.value || "";
        const hnParts = hnValue.split(" - ");
        const hn = hnParts[0] || "";
        const patientName = hnParts[1] || "";

        return {
            hn: hn,
            patientName: patientName,
            age: ageElement ? ageElement.value : "",
            address: addressElement ? addressElement.value : "",
            appointmentDate: dateElement ? dateElement.value : "",
            appointmentTime: timeElement ? timeElement.value : "",
            tel: telElement ? telElement.value : "",
            note: noteElement ? noteElement.value : "",
        };
    } catch (error) {
        console.error("Error extracting patient data:", error);
        return null;
    }
}

// ฟังก์ชันสำหรับสร้าง HTML สำหรับ PDF
function createPDFHTML(data) {
    const currentDate = new Date().toLocaleDateString("th-TH", {
        year: "numeric",
        month: "long",
        day: "numeric",
    });

    return `
        <div style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6; color: #333;">
            <!-- Header -->
            <div style="text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 3px solid #2c5aa0;">
                <h1 style="color: #2c5aa0; margin: 0; font-size: 24px; font-weight: bold;">ข้อมูลผู้ป่วย</h1>
                <p style="color: #666; margin: 5px 0 0 0; font-size: 12px;">Patient Information</p>
            </div>

            <!-- Patient Info Grid -->
            <div style="margin-bottom: 30px;">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 25%; padding: 12px 0; border-bottom: 1px solid #eee;">
                            <strong style="color: #2c5aa0;">HN:</strong>
                        </td>
                        <td style="width: 25%; padding: 12px 0; border-bottom: 1px solid #eee;">
                            <span style="font-size: 16px; font-weight: bold;">${
                                data.hn
                            }</span>
                        </td>
                        <td style="width: 25%; padding: 12px 0; border-bottom: 1px solid #eee;">
                            <strong style="color: #2c5aa0;">อายุ:</strong>
                        </td>
                        <td style="width: 25%; padding: 12px 0; border-bottom: 1px solid #eee;">
                            <span>${data.age}</span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Patient Name -->
            <div style="margin-bottom: 20px;">
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #2c5aa0;">
                    <strong style="color: #2c5aa0; font-size: 16px;">ชื่อผู้ป่วย:</strong>
                    <div style="margin-top: 5px; font-size: 18px; font-weight: bold; color: #333;">
                        ${data.patientName}
                    </div>
                </div>
            </div>

            <!-- Address -->
            <div style="margin-bottom: 20px;">
                <div style="background-color: #f8f9fa; padding: 15px; border-radius: 8px; border-left: 4px solid #17a2b8;">
                    <strong style="color: #17a2b8; font-size: 16px;">ที่อยู่:</strong>
                    <div style="margin-top: 5px; line-height: 1.6;">
                        ${data.address.replace(/\n/g, "<br>")}
                    </div>
                </div>
            </div>

            <!-- Appointment Info -->
            <div style="margin-bottom: 20px;">
                <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;">
                    <strong style="color: #856404; font-size: 16px;">ข้อมูลการนัดหมาย:</strong>
                    <table style="width: 100%; margin-top: 10px;">
                        <tr>
                            <td style="width: 30%; padding: 8px 0;">
                                <strong style="color: #856404;">วันที่นัด:</strong>
                            </td>
                            <td style="padding: 8px 0;">
                                <span style="font-size: 16px; font-weight: bold;">${
                                    data.appointmentDate
                                }</span>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 8px 0;">
                                <strong style="color: #856404;">เวลา:</strong>
                            </td>
                            <td style="padding: 8px 0;">
                                <span style="font-size: 16px; font-weight: bold;">${
                                    data.appointmentTime
                                }</span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Contact Info -->
            <div style="margin-bottom: 20px;">
                <div style="background-color: #d4edda; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;">
                    <strong style="color: #155724; font-size: 16px;">เบอร์ติดต่อ:</strong>
                    <div style="margin-top: 5px; font-size: 16px; font-weight: bold;">
                        ${data.tel.replace(/\n/g, "<br>")}
                    </div>
                </div>
            </div>

            <!-- Notes -->
            ${
                data.note
                    ? `
            <div style="margin-bottom: 30px;">
                <div style="background-color: #e2e3e5; padding: 15px; border-radius: 8px; border-left: 4px solid #6c757d;">
                    <strong style="color: #495057; font-size: 16px;">หมายเหตุ:</strong>
                    <div style="margin-top: 5px; line-height: 1.6;">
                        ${data.note.replace(/\n/g, "<br>")}
                    </div>
                </div>
            </div>
            `
                    : ""
            }

            <!-- Footer -->
            <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #eee; text-align: center;">
                <p style="color: #666; font-size: 12px; margin: 0;">
                    เอกสารนี้ถูกสร้างเมื่อ ${currentDate}
                </p>
            </div>
        </div>
    `;
}
