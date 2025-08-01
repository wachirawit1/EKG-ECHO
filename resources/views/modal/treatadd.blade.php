{{-- addtreatment modal --}}
<div class="modal fade" id="addTreatment" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form action="{{ route('treatment.add') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">เพิ่มการรักษาใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <div class="form-label">ประเภทผู้ป่วย</div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="resource" id="in_patient"
                                value="in" checked>
                            <label class="form-check-label" for="in_patient">ในรพ.</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="resource" id="out_patient"
                                value="out">
                            <label class="form-check-label" for="out_patient">นอกรพ.</label>
                        </div>
                    </div>
                    {{-- in --}}
                    <div class="row mb-3" id="in-section">
                        <div class="col ">
                            <label for="hn" class="form-label">HN</label>
                            <input type="text" class="form-control" id="hn" name="hn"
                                placeholder="กรอก HN..." maxlength="7">
                            <small id="hn-error" class="text-danger d-none">HN ต้องเป็นตัวเลขไม่เกิน 7
                                หลัก</small>
                        </div>

                        <div class="col ">
                            <label class="form-label">ชื่อผู้ป่วย</label>
                            <input type="text" class="form-control" id="hn_name_display" disabled>
                        </div>
                    </div>
                    {{-- out --}}
                    <div class="mb-3" id="out-section" style="display: none;">
                        <div class="row">
                            <div class="col">
                                <label class="form-label">ชื่อ</label>
                                <input type="text" class="form-control" name="fname" placeholder="ชื่อผู้ป่วย">
                            </div>
                            <div class="col">
                                <label class="form-label">นามสกุล</label>
                                <input type="text" class="form-control" name="lname" placeholder="นามสกุลผู้ป่วย">
                            </div>
                        </div>
                    </div>


                    <div class="mb-3">
                        <label for="t_date" class="form-label">วันที่รักษา</label>
                        <input type="text" class="form-control" id="t_date" name="t_date"
                            onkeydown="return false" placeholder="เลือกวันที่รักษา" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="agency" class="form-label">หน่วยงาน</label>
                        <select class="form-select" aria-label="Default select example" id="agency" name="agency" disabled>
                            <option value="">เลือก</option>
                            <option value="none">ไม่มี</option>
                            <optgroup label="วอร์ด">
                                @foreach ($ward_list as $item)
                                    <option value="ward:{{ $item->ward_id }}">
                                        {{ $item->ward_id . ' - ' . $item->ward_name }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="แผนก">
                                @foreach ($dept_list as $item)
                                    <option value="dept:{{ $item->deptCode }}">
                                        {{ $item->deptCode . ' - ' . $item->deptDesc }}</option>
                                @endforeach
                            </optgroup>
                        </select>

                    </div>

                    <div class="mb-3">
                        <label for="forward" class="form-label">ส่งต่อ</label>
                        <select class="form-select" id="forward" name="forward" disabled>
                            <option value="">เลือก</option>
                            <option value="none">ไม่มี</option>
                            <optgroup label="วอร์ด">
                                @foreach ($ward_list as $item)
                                    <option value="ward:{{ $item->ward_id }}">
                                        {{ $item->ward_id . ' - ' . $item->ward_name }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="แผนก">
                                @foreach ($dept_list as $item)
                                    <option value="dept:{{ $item->deptCode }}">
                                        {{ $item->deptCode . ' - ' . $item->deptDesc }}</option>
                                @endforeach
                            </optgroup>
                        </select>
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
