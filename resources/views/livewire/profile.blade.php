<div class="container my-3 company-profile-page compact-form-typography">
    <style>
        .company-profile-page .company-profile-section {
            border: 1px solid transparent;
            padding: 0.5rem 0.65rem;
        }
        .company-profile-page .company-profile-section-ids {
            margin-bottom: 0.5rem;
            background-color: #e8f0fe;
            border-color: #c5d4f0;
        }
        .company-profile-page .company-profile-sections-row {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }
        .company-profile-page .card-body {
            padding: 0.75rem 1rem;
        }
        .company-profile-page .company-profile-section-contact {
            background-color: #e8f6ee;
            border-color: #bfe0cc;
        }
        .company-profile-page .company-profile-section-side {
            background-color: #fff4e5;
            border-color: #f0d9b8;
        }
        .company-profile-page .company-profile-address-block {
            border-top: 1px dashed rgba(0, 0, 0, 0.12);
            margin-top: 0.25rem;
            padding-top: 0.25rem;
        }
        .company-profile-page.compact-form-typography label,
        .company-profile-page.compact-form-typography .form-label {
            font-size: 0.82em;
            font-weight: 600;
            margin-bottom: 0.1rem;
        }
        .company-profile-page.compact-form-typography .form-control,
        .company-profile-page.compact-form-typography .form-select,
        .company-profile-page.compact-form-typography input {
            font-size: 0.8em;
        }
        .company-profile-page.compact-form-typography .form-control-sm,
        .company-profile-page.compact-form-typography .form-select-sm {
            font-size: 0.8em;
            padding: 0.12rem 0.35rem;
            min-height: calc(1.35em + 0.24rem + 2px);
        }
        .company-profile-page.compact-form-typography p,
        .company-profile-page.compact-form-typography small {
            font-size: 0.85em;
        }
        .company-profile-page.compact-form-typography .form-group {
            margin-bottom: 0.15rem;
        }
        .company-profile-page.compact-form-typography .btn-sm {
            font-size: 0.85em;
        }
    </style>
    <div class="row">
        <div class="col-lg-10 col-xl-9 m-auto">
            <div class="card shadow-sm rounded" style="overflow: visible;">
                <div class="card-header">
                    <div class="row d-flex align-items-center justify-content-between">
                        <div class="col-8">
                            <h5 class="fw-bold fs-5">Edit Company Profile</h5>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="updateProfile">
                        {{-- Top: company number & GST --}}
                        <div class="company-profile-section company-profile-section-ids rounded">
                            <div class="row g-1">
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="company_no" class="form-label">Company Number <span class="text-danger">*</span></label>
                                        <input type="text" name="company_no" wire:model="company_no" class="form-control form-control-sm rounded" id="company_no">
                                        @error('company_no')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-0">
                                        <label for="gst_no" class="form-label">GST Registration Number <span class="text-danger">*</span></label>
                                        <input type="text" name="gst_no" wire:model="gst_no" class="form-control form-control-sm rounded" id="gst_no">
                                        @error('gst_no')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bottom: name & phones (left) | fax & email (right) --}}
                        <div class="row company-profile-sections-row g-2">
                            <div class="col-md-8">
                                <div class="company-profile-section company-profile-section-contact rounded h-100">
                                    <div class="form-group">
                                        <label for="company_name" class="form-label">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" name="company_name" wire:model="company_name" class="form-control form-control-sm rounded" id="company_name">
                                        @error('company_name')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="phone_num1" class="form-label">Primary Phone Number <span class="text-danger">*</span></label>
                                        <input type="text" name="phone_num1" wire:model="phone_num1" class="form-control form-control-sm rounded" id="phone_num1">
                                        @error('phone_num1')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="phone_num2" class="form-label">Secondary Phone Number</label>
                                        <input type="text" name="phone_num2" wire:model="phone_num2" class="form-control form-control-sm rounded" id="phone_num2">
                                    </div>

                                    <div class="company-profile-address-block">
                                        <div class="form-group">
                                            <label for="address_line1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                                            <input type="text" name="address_line1" wire:model="address_line1" class="form-control form-control-sm rounded" id="address_line1">
                                            @error('address_line1')
                                                <p class="text-danger mb-0 small">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="form-group">
                                            <label for="address_line2" class="form-label">Address Line 2 <span class="text-danger">*</span></label>
                                            <input type="text" name="address_line2" wire:model="address_line2" class="form-control form-control-sm rounded" id="address_line2">
                                        </div>
                                        <div class="form-group">
                                            <label for="address_line3" class="form-label">Address Line 3</label>
                                            <input type="text" name="address_line3" wire:model="address_line3" class="form-control form-control-sm rounded" id="address_line3">
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="address_line4" class="form-label">Address Line 4</label>
                                            <input type="text" name="address_line4" wire:model="address_line4" class="form-control form-control-sm rounded" id="address_line4">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="company-profile-section company-profile-section-side rounded h-100">
                                    <div class="form-group">
                                        <label for="fax_num" class="form-label">Fax Number <span class="text-danger">*</span></label>
                                        <input type="text" name="fax_num" wire:model="fax_num" class="form-control form-control-sm rounded" id="fax_num">
                                        @error('fax_num')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-0">
                                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                        <input type="email" name="email" wire:model="email" class="form-control form-control-sm rounded" id="email">
                                        @error('email')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary btn-sm">Update Company Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
