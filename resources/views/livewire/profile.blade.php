<div class="container my-3">
    <div class="row">
        <div class="col-7 m-auto">
            <div class="card shadow-sm" style="overflow: visible;">
                <div class="card-header">
                    <div class="row d-flex align-items-center justify-content-between">
                        <div class="col-8">
                            <h5 class="fw-bold fs-5">Edit Company Profile</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="updateProfile">
                        <div class="form-group mb-2">
                            <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" wire:model="company_name" class="form-control form-control-sm rounded" id="company_name">
                            @error('company_name')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="company_no" class="form-label">Company Number <span class="text-danger">*</span></label>
                            <input type="text" name="company_no" wire:model="company_no" class="form-control form-control-sm rounded" id="company_no">
                            @error('company_no')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="gst_no" class="form-label">GST Registration Number <span class="text-danger">*</span></label>
                            <input type="text" name="gst_no" wire:model="gst_no" class="form-control form-control-sm rounded" id="gst_no">
                            @error('gst_no')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="address_line1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" name="address_line1" wire:model="address_line1" class="form-control form-control-sm rounded" id="address_line1">
                            @error('address_line1')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="address_line2" class="form-label">Address Line 2 <span class="text-danger">*</span></label>
                            <input type="text" name="address_line2" wire:model="address_line2" class="form-control form-control-sm rounded" id="address_line2">
                        </div>

                        <div class="form-group mb-2">
                            <label for="address_line3" class="form-label">Address Line 3</label>
                            <input type="text" name="address_line3" wire:model="address_line3" class="form-control form-control-sm rounded" id="address_line3">
                        </div>

                        <div class="form-group mb-2">
                            <label for="address_line4" class="form-label">Address Line 4</label>
                            <input type="text" name="address_line4" wire:model="address_line4" class="form-control form-control-sm rounded" id="address_line4">
                        </div>

                        <div class="form-group mb-2">
                            <label for="phone_num1" class="form-label">Primary Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone_num1" wire:model="phone_num1" class="form-control form-control-sm rounded" id="phone_num1">
                            @error('phone_num1')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="phone_num2" class="form-label">Secondary Phone Number</label>
                            <input type="text" name="phone_num2" wire:model="phone_num2" class="form-control form-control-sm rounded" id="phone_num2">
                        </div>

                        <div class="form-group mb-2">
                            <label for="fax_num" class="form-label">Fax Number <span class="text-danger">*</span></label>
                            <input type="text" name="fax_num" wire:model="fax_num" class="form-control form-control-sm rounded" id="fax_num">
                            @error('fax_num')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" wire:model="email" class="form-control form-control-sm rounded" id="email">
                            @error('email')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="card-footer mb-2">
                            <div class="form-group text-end">
                                <button type="submit" class="btn btn-primary">Update Company Profile</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>