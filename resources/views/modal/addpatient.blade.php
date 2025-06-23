<!-- Modal เพิ่มคนไข้ -->
<div class="modal fade" id="addPatient" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="{{ route('patient.add') }}">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">เพิ่มคนไข้ใหม่(นอกโรงพยาบาล)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @csrf
                    <!-- ช่องกรอกชื่อโรงพยาบาล -->

                    <div class="mb-3">
                        <label for="hospital_name" class="form-label">โรงพยาบาล</label>
                        <input type="text" class="form-control" id="hospital_name" name="hospital_name"
                            placeholder="เช่น โรงพยาบาลลำปลายมาศ">
                    </div>

                    <div class="row mb-3"> 
                        <div class="col-2">
                            <label for="titleName" class="form-label">คำนำหน้า</label>
                            <select class="form-control" name="titleName" id="titleName">
                                <option value="">เลือก</option>
                                <option value="นาย">นาย</option>
                                <option value="นาง">นาง</option>
                                <option value="นางสาว">นางสาว</option>
                            </select>
                        </div>
                        <div class="col">
                            <label for="fname" class="form-label">ชื่อ</label>
                            <input type="text" class="form-control " id="fname" name="fname"
                                placeholder="กรอกชื่อ...">
                        </div>
                        <div class="col">
                            <label for="lname" class="form-label">นามสกุล</label>
                            <input type="text" class="form-control " id="lname" name="lname"
                                placeholder="กรอกนามสกุล...">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="gender">เพศ</label>
                        <select id="gender" name="gender" class="form-control">
                            <option value="">เลือก</option>
                            <option value="ช">ชาย</option>
                            <option value="ญ">หญิง</option>
                        </select>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">บันทึก</button>
                </div>
            </div>
        </form>
    </div>
</div>
