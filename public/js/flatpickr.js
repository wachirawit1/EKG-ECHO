document.addEventListener("shown.bs.modal", function (e) {
    const modal = e.target;
    const id = modal
        .querySelector('[id^="a_time_text"]')
        ?.id?.replace("a_time_text", "");
    if (id) {
        initTimePickerForModal(id);
    }
});

function initTimePickerForModal(id) {
    const input = document.querySelector(`#a_time_text${id}`);
    if (!input || input._flatpickr) return;

    flatpickr(input, {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
        defaultDate: input.value.split("-")[0]?.trim() || null,
        allowInput: false,
        onChange: function (selectedDates, dateStr, instance) {
            const startTime = dateStr;
            const endTime = calculateEndTime(startTime); // ฟังก์ชันสำหรับกำหนดเวลาสิ้นสุด

            // อัปเดต input hidden
            document.querySelector(`#a_time_start${id}`).value = startTime;
            document.querySelector(`#a_time_end${id}`).value = endTime;

            // อัปเดต text ที่แสดง
            input.value = `${startTime}-${endTime}`;
        },
    });
}

function calculateEndTime(start) {
    // เพิ่มเวลา 1 ชั่วโมง เช่น 13:00 → 14:00
    const [h, m] = start.split(":").map(Number);
    const date = new Date();
    date.setHours(h);
    date.setMinutes(m + 60);
    const hh = String(date.getHours()).padStart(2, "0");
    const mm = String(date.getMinutes() % 60).padStart(2, "0");
    return `${hh}:${mm}`;
}
