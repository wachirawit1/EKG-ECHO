
        // เก็บเงื่อนไขการค้นหาปัจจุบัน
        let currentSearchParams = {};
        let currentTreatmentSearchParams = {};

        // ค้นหานัดโดย hn
        function searchAppointments(e) {
            e.preventDefault(); // ยกเลิก submit แบบปกติ
            const form = document.getElementById('searchForm');
            const formData = new FormData(form);

            const hn = formData.get('hn');
            const doc_id = formData.get('doc_id');
            const start_date = formData.get('start_date');
            const end_date = formData.get('end_date');
            // เก็บเงื่อนไขการค้นหาไว้
            currentSearchParams = {
                hn,
                start_date,
                end_date,
                doc_id
            };

            // รีเซ็ตไปหน้าแรกเมื่อค้นหาใหม่
            loadPage('appointments', {
                ...currentSearchParams,
                page: 1
            });
        }

        // ค้นหาการรักษาโดย hn
        function searchTreatments(e) {
            e.preventDefault();
            const form = document.getElementById('searchTreatForm');
            const formData = new FormData(form);

            const hn = formData.get('hn');
            const start_date = formData.get('start_date');
            const end_date = formData.get('end_date');
            // เก็บเงื่อนไขการค้นหาไว้สำหรับ treatments
            currentTreatmentSearchParams = {
                hn,
                start_date,
                end_date
            };

            // รีเซ็ตไปหน้าแรกเมื่อค้นหาใหม่
            loadPage('treatments', {
                ...currentTreatmentSearchParams,
                page: 1
            });



        }