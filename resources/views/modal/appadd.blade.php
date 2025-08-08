<div class="modal fade" id="addAppointment" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
    aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <form method="POST" action="{{ route('app.add') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">เพิ่มนัดหมายใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">


                    <!-- Section: เลือกประเภทผู้ป่วย -->
                    <div class="mb-3">
                        <div class="form-label">ประเภทผู้ป่วย</div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="resource" id="in_patient"
                                value="in" onchange="togglePatientFields()" checked>
                            <label class="form-check-label" for="in_patient">ในรพ.</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="resource" id="out_patient"
                                value="out" onchange="togglePatientFields()">
                            <label class="form-check-label" for="out_patient">นอกรพ.</label>
                        </div>
                    </div>

                    <!-- ช่องกรอก HN -->
                    <div class="row mb-3" id="hn-group">
                        <div class="col">
                            <label for="hn" class="form-label">HN</label>
                            <input type="text" class="form-control" id="hn" name="hn"
                                placeholder="กรอก HN..." maxlength="7">
                            <small id="hn-error" class="text-danger d-none">HN ต้องเป็นตัวเลขไม่เกิน 7
                                หลัก</small>
                        </div>
                        <div class="col">
                            <label class="form-label">ชื่อผู้ป่วย</label>
                            <input type="text" class="form-control" id="hn_name_display" disabled>
                        </div>
                    </div>



                    <!-- ช่องกรอกชื่อโรงพยาบาล -->
                    <div class="mb-3" id="hospital-group" style="display: none;">

                        <div class="row mb-3">
                            <div class="col-2">
                                <label for="titleName" class="form-label">คำนำหน้า</label>
                                <select class="form-select" name="titleName" id="titleName">
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
                            <div class="col">
                                <label for="hospital_name" class="form-label">โรงพยาบาล</label>
                                <select type="text" class="form-select" id="hospital_name" name="hospital_name">
                                    <option value="">เลือกโรงพยาบาล</option>
                                    @foreach ($hospcode as $item)
                                        <option value="{{ $item->OFF_NAME1 }}">{{ $item->OFF_NAME1 }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- เบอร์ติดต่อ --}}
                    <div class="mb-3">
                        <label for="tel" class="form-label">เบอร์โทรติดต่อ</label>
                        <input type="text" class="form-control" id="tel" name="tel"
                            placeholder="xxx-xxx-xxxx" disabled>
                    </div>

                    <div class="row">
                        {{-- วอร์ด/แผนก --}}
                        <div class="col-md-6 mb-3">
                            <label for="ward" class="form-label">วอร์ด/แผนก</label>

                            <select class="form-select" id="wardSelect" name="ward" disabled>
                                <option value="" selected>เลือก</option>
                                <option value="none">ไม่มี</option>
                                <optgroup label="วอร์ด">
                                    @foreach ($ward_list as $item)
                                        <option value="ward:{{ $item->ward_id }}">{{ $item->ward_id }} -
                                            {{ $item->ward_name }}</option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="แผนก">
                                    @foreach ($dept_list as $item)
                                        <option value="dept:{{ $item->deptCode }}">{{ $item->deptCode }} -
                                            {{ $item->deptDesc }}</option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        {{-- กรอกแพทย์ --}}
                        <div class="col-md-6 mb-3">
                            <label for="doctorSelect" class="form-label">แพทย์</label>

                            <select id="doctorSelect" class="form-select" name="docID" disabled>
                                <option value="" selected>เลือก</option>
                                <option value="none">ไม่ระบุแพทย์</option>
                                @foreach ($doc as $doctor)
                                    <option value="{{ $doctor->docCode }}">
                                        {{ $doctor->doctitle . ' ' . $doctor->docName . ' ' . $doctor->docLName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
<<<<<<< HEAD

=======
                    
>>>>>>> 6638ce4 (Initial commit Laravel project)
                    <div class="mb-3">
                        <label for="appointmentDate" class="form-label">วันที่นัด</label>
                        <input class="form-control date-lock" id="appointmentDate" name="appointmentDate" disabled
                            placeholder="เลือกวันที่นัด">
                    </div>

<<<<<<< HEAD

=======
>>>>>>> 6638ce4 (Initial commit Laravel project)
                    <div class="mb-3 px-2">
                        <label class="form-label">ช่วงเวลานัด</label>

                        <!-- ตัวเลือก 1 -->
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="appointment_time" id="time1"
                                value="8:30-10:30" disabled />
                            <label class="form-check-label" for="time1">08:30 - 10:30</label>
                        </div>

                        <!-- ตัวเลือก 2 -->
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="appointment_time" id="time2"
                                value="11:00-11:30" disabled />
                            <label class="form-check-label" for="time2">10:30 - 11:30</label>
                        </div>

                        <!-- ตัวเลือก 3 -->
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="appointment_time" id="time3"
                                value="14:00-14:30" disabled />
                            <label class="form-check-label" for="time2">15:00 - 16:00</label>
                        </div>

                        <!-- ตัวเลือกกรอกเอง -->
                        <div class="form-check col-sm-4 mb-2">
                            <input class="form-check-input" type="radio" name="appointment_time" id="customTime"
                                value="custom" disabled />
                            <div class="row g-2">
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm" id="custom_start_time"
                                        name="custom_start_time" disabled />
                                </div>
                                <div class="col-6">
                                    <input type="text" class="form-control form-control-sm" id="custom_end_time"
                                        name="custom_end_time" disabled />
                                </div>
                            </div>
                        </div>

                    </div>


                    <div class="mb-3">
                        <label for="note" class="form-label">หมายเหตุ</label>
                        <textarea name="note" id="note" cols="3" rows="3" class="form-control"
                            placeholder="กรอกหมายเหตุ(ถ้ามี)" disabled></textarea>
                    </div>


                </div>


                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary">เพิ่ม</button>
                </div>
            </form>
        </div>
    </div>
</div>
