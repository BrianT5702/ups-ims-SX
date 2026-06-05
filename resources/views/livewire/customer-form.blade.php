<div class="container my-3 customer-form-page compact-form-typography">
    <style>
        .customer-form-page .customer-form-section {
            border: 1px solid transparent;
            padding: 0.5rem 0.65rem;
        }
        .customer-form-page .customer-form-section-ids {
            margin-bottom: 0.5rem;
        }
        .customer-form-page .customer-form-sections-row {
            --bs-gutter-x: 0.5rem;
            --bs-gutter-y: 0.5rem;
        }
        .customer-form-page .card-body {
            padding: 0.75rem 1rem;
        }
        .customer-form-page .customer-form-section-ids {
            background-color: #e8f0fe;
            border-color: #c5d4f0;
        }
        .customer-form-page .customer-form-section-contact {
            background-color: #e8f6ee;
            border-color: #bfe0cc;
        }
        .customer-form-page .customer-form-section-commercial {
            background-color: #fff4e5;
            border-color: #f0d9b8;
        }
        .customer-form-page .customer-form-contact-block {
            border-top: 1px dashed rgba(0, 0, 0, 0.12);
            margin-top: 0.25rem;
            padding-top: 0.25rem;
        }
        /* Match DO header / PO compact-form-typography */
        .customer-form-page.compact-form-typography label,
        .customer-form-page.compact-form-typography .form-label {
            font-size: 0.82em;
            font-weight: 600;
            margin-bottom: 0.1rem;
        }
        .customer-form-page.compact-form-typography .form-control,
        .customer-form-page.compact-form-typography .form-select,
        .customer-form-page.compact-form-typography input {
            font-size: 0.8em;
        }
        .customer-form-page.compact-form-typography .form-control-sm,
        .customer-form-page.compact-form-typography .form-select-sm {
            font-size: 0.8em;
            padding: 0.12rem 0.35rem;
            min-height: calc(1.35em + 0.24rem + 2px);
        }
        .customer-form-page.compact-form-typography p,
        .customer-form-page.compact-form-typography small {
            font-size: 0.85em;
        }
        .customer-form-page.compact-form-typography .form-group {
            margin-bottom: 0.15rem;
        }
        .customer-form-page.compact-form-typography .btn-sm {
            font-size: 0.85em;
        }
    </style>
    <div class="row">
        <div class="col-lg-10 col-xl-9 m-auto">
            <div class="card shadow-sm rounded" style="overflow: visible;">
                <div class="card-header">
                    <div class="row d-flex align-items-center justify-content-between">
                        <div class="col-8">
                            <h5 class="fw-bold fs-5">{{ $isView ? 'View' : ($customer ? 'Edit': 'Add' )}} Customer </h5>
                        </div>

                        <div class="col-4 text-end d-flex justify-content-end align-items-center gap-2 flex-wrap">
                            @if($customer && $isView)
                                <a wire:navigate href="{{ route('customers.edit', $customer->id) }}" class="btn btn-success btn-sm rounded">Edit</a>
                                <button type="button" wire:confirm="Are you sure you want to delete this customer?" wire:click="deleteCustomer" class="btn btn-danger btn-sm rounded">Delete</button>
                            @elseif($customer && !$isView)
                                <button type="button" wire:confirm="Are you sure you want to delete this customer?" wire:click="deleteCustomer" class="btn btn-danger btn-sm rounded">Delete</button>
                            @endif
                            <a wire:navigate href="{{ route('customers') }}" class="btn btn-primary btn-sm rounded">Back</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="addCustomer">
                        {{-- Top: account & registration --}}
                        <div class="customer-form-section customer-form-section-ids rounded">
                            <div class="row g-1">
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="account" class="form-label">Account Number</label>
                                        <input type="text" name="account" wire:model.live="account" class="form-control form-control-sm rounded" id="account" {{ $isView ? 'disabled' : ''}}>
                                        @error('account')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="business_registration_no" class="form-label">Business Registration No.</label>
                                        <input type="text" name="business_registration_no" wire:model.live="business_registration_no" class="form-control form-control-sm rounded" id="business_registration_no" {{ $isView ? 'disabled' : ''}}>
                                        @error('business_registration_no')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-0">
                                        <label for="gst_registration_no" class="form-label">GST Registration No.</label>
                                        <input type="text" name="gst_registration_no" wire:model.live="gst_registration_no" class="form-control form-control-sm rounded" id="gst_registration_no" {{ $isView ? 'disabled' : ''}}>
                                        @error('gst_registration_no')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bottom: contact (left) & commercial (right) --}}
                        <div class="row customer-form-sections-row g-2">
                            <div class="col-md-8">
                                <div class="customer-form-section customer-form-section-contact rounded h-100">
                                    <div class="form-group">
                                        <label for="cust_name" class="form-label">Name <span class="text-danger">*</span></label>
                                        <input type="text" name="cust_name" wire:model.live="cust_name" class="form-control form-control-sm rounded" id="cust_name" {{ $isView ? 'disabled' : ''}}>
                                        @error('cust_name')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="address_line1" class="form-label">Address Line 1</label>
                                        <input type="text" name="address_line1" wire:model.live="address_line1" class="form-control form-control-sm rounded" id="address_line1" {{ $isView ? 'disabled' : ''}}>
                                        @error('address_line1')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="address_line2" class="form-label">Address Line 2</label>
                                        <input type="text" name="address_line2" wire:model.live="address_line2" class="form-control form-control-sm rounded" id="address_line2" {{ $isView ? 'disabled' : ''}}>
                                        @error('address_line2')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="address_line3" class="form-label">Address Line 3</label>
                                        <input type="text" name="address_line3" wire:model.live="address_line3" class="form-control form-control-sm rounded" id="address_line3" {{ $isView ? 'disabled' : ''}}>
                                    </div>
                                    <div class="form-group">
                                        <label for="address_line4" class="form-label">Address Line 4</label>
                                        <input type="text" name="address_line4" wire:model.live="address_line4" class="form-control form-control-sm rounded" id="address_line4" {{ $isView ? 'disabled' : ''}}>
                                    </div>

                                    <div class="customer-form-contact-block">
                                        <div class="form-group">
                                            <label for="phone_num" class="form-label">Phone Number</label>
                                            <input type="text" name="phone_num" wire:model.live="phone_num" class="form-control form-control-sm rounded" id="phone_num" {{ $isView ? 'disabled' : ''}}>
                                            @error('phone_num')
                                                <p class="text-danger mb-0 small">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label for="fax_num" class="form-label">Fax No.</label>
                                            <input type="text" name="fax_num" wire:model.live="fax_num" class="form-control form-control-sm rounded" id="fax_num" {{ $isView ? 'disabled' : ''}}>
                                            @error('fax_num')
                                                <p class="text-danger mb-0 small">{{ $message }}</p>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-0">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" name="email" wire:model.live="email" class="form-control form-control-sm rounded" id="email" {{ $isView ? 'disabled' : ''}}>
                                            @error('email')
                                                <p class="text-danger mb-0 small">{{ $message }}</p>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="customer-form-section customer-form-section-commercial rounded h-100">
                                    <div class="form-group">
                                        <label for="term" class="form-label">Term</label>
                                        <select name="term" class="form-select form-select-sm rounded" id="term" wire:model.live="term" {{ $isView ? 'disabled' : ''}}>
                                            <option value="" disabled {{ empty($term) ? 'selected' : '' }}>Select a term</option>
                                            @foreach($termOptions as $option)
                                                <option value="{{ $option }}">{{ $option }}</option>
                                            @endforeach
                                        </select>
                                        @error('term')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="salesman_id" class="form-label">Salesperson <span class="text-danger">*</span></label>
                                        <select id="salesman_id" class="form-select form-select-sm rounded" wire:model.live="salesman_id" {{ $isView ? 'disabled' : ''}}>
                                            <option value="">Select Salesperson</option>
                                            @php
                                                $connection = session('active_db') ?: config('database.default');
                                                $salespersons = \App\Models\User::on($connection)->role('Salesperson')->orderBy('name','asc')->get();
                                            @endphp
                                            @foreach($salespersons as $user)
                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('salesman_id')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="currency" class="form-label">Currency</label>
                                        <select name="currency" class="form-select form-select-sm rounded" id="currency" wire:model.live="currency" {{ $isView ? 'disabled' : ''}}>
                                            <option value="RM" selected>RM - Malaysian Ringgit</option>
                                            <option value="USD">USD - US Dollar</option>
                                            <option value="SGD">SGD - Singapore Dollar</option>
                                            <option value="EUR">EUR - Euro</option>
                                            <option value="GBP">GBP - British Pound</option>
                                            <option value="JPY">JPY - Japanese Yen</option>
                                            <option value="CNY">CNY - Chinese Yuan</option>
                                            <option value="THB">THB - Thai Baht</option>
                                            <option value="IDR">IDR - Indonesian Rupiah</option>
                                            <option value="PHP">PHP - Philippine Peso</option>
                                        </select>
                                        @error('currency')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="form-group mb-0">
                                        <label for="area" class="form-label">Area</label>
                                        <input type="text" name="area" wire:model.live="area" class="form-control form-control-sm rounded" id="area" {{ $isView ? 'disabled' : ''}}>
                                        @error('area')
                                            <p class="text-danger mb-0 small">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(!$isView)
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">{{ $customer ? 'Update' : 'Add' }}</button>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>