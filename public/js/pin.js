document.addEventListener("DOMContentLoaded", function () {
    // เปิด modal ตอนโหลดหน้า
    let pinModal = new bootstrap.Modal(document.getElementById("pinModal"));
    pinModal.show();

    const myModal = document
        .getElementById("pinModal")
        .addEventListener("shown.bs.modal", function () {
            document.getElementById("pinInput").focus();
        });

    function checkPin() {
        let pin = document.getElementById("pinInput").value;
        let correctPin = "543669";

        if (pin === correctPin) {
            pinModal.hide();
            document.getElementById("pm-content").style.display = "block"; // แสดงเนื้อหา
        } else {
            document.getElementById("pinError").style.display = "block";
            document.getElementById("pinInput").value = "";
            document.getElementById("pinInput").focus();
        }
    }

    //กดปุ่ม "ยืนนยัน"
    document.getElementById("checkPinBtn").addEventListener("click", checkPin);

    // กด Enter ใน input
    document
        .getElementById("pinInput")
        .addEventListener("keypress", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                checkPin();
            }
        });
});
