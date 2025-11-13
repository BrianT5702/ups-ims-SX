<div class="container my-3">
    <div class="row">
        <div class="col-7 m-auto">
            <div class="card shadow-sm rounded" style="overflow: visible;">
                <div class="card-header">
                    <div class="row d-flex align-items-center justify-content-between">
                        <div class="col-8">
                            <h5 class="fw-bold fs-5">{{ $isView ? 'View' : ($customer ? 'Edit': 'Add' )}} Customer </h5>
                        </div>

                        <div class="col-4 text-end">
                            <a wire:navigate href="{{ route('customers') }}" class="btn btn-primary btn-sm rounded">Back</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form wire:submit.prevent="addCustomer">
                        
                        <div class="form-group mb-2">
                            <label for="account" class="form-label">Account Number <span class="text-danger">*</span></label>
                            <input type="text" name="account" wire:model.live="account" class="form-control form-control-sm rounded" id="account" {{ $isView ? 'disabled' : ''}}>
                            @error('account')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="cust_name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" name="cust_name" wire:model.live="cust_name" class="form-control form-control-sm rounded" id="cust_name" {{ $isView ? 'disabled' : ''}}>
                            @error('cust_name')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="address_line1" class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                            <input type="text" name="address_line1" wire:model.live="address_line1" class="form-control form-control-sm rounded" id="address_line1" {{ $isView ? 'disabled' : ''}}>
                            @error('address_line1')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="address_line2" class="form-label">Address Line 2 <span class="text-danger">*</span></label>
                            <input type="text" name="address_line2" wire:model.live="address_line2" class="form-control form-control-sm rounded" id="address_line2" {{ $isView ? 'disabled' : ''}}>
                            @error('address_line2')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="address_line3" class="form-label">Address Line 3</label>
                            <input type="text" name="address_line3" wire:model.live="address_line3" class="form-control form-control-sm rounded" id="address_line3" {{ $isView ? 'disabled' : ''}}>
                        </div>

                        <div class="form-group mb-2">
                            <label for="address_line4" class="form-label">Address Line 4</label>
                            <input type="text" name="address_line4" wire:model.live="address_line4" class="form-control form-control-sm rounded" id="address_line4" {{ $isView ? 'disabled' : ''}}>
                        </div>

                        <div class="form-group mb-2">
                            <label for="phone_num" class="form-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="phone_num" wire:model.live="phone_num" class="form-control form-control-sm rounded" id="phone_num" {{ $isView ? 'disabled' : ''}}>
                            @error('phone_num')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="fax_num" class="form-label">Fax No.</label>
                            <input type="text" name="fax_num" wire:model.live="fax_num" class="form-control form-control-sm rounded" id="fax_num" {{ $isView ? 'disabled' : ''}}>
                            @error('fax_num')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" name="email" wire:model.live="email" class="form-control form-control-sm rounded" id="email" {{ $isView ? 'disabled' : ''}}>
                            @error('email')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="area" class="form-label">Area</label>
                            <input type="text" name="area" wire:model.live="area" class="form-control form-control-sm rounded" id="area" {{ $isView ? 'disabled' : ''}}>
                            @error('area')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="term" class="form-label">Term <span class="text-danger">*</span></label>
                            <select name="term" class="form-select form-select-sm rounded" id="term" wire:model.live="term" {{ $isView ? 'disabled' : ''}}>
                                <option value="" disabled {{ empty($term) ? 'selected' : '' }}>Select a term</option>
                                @foreach($termOptions as $option)
                                    <option value="{{ $option }}">{{ $option }}</option>
                                @endforeach
                            </select>
                            @error('term')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="business_registration_no" class="form-label">Business Registration No.</label>
                            <input type="text" name="business_registration_no" wire:model.live="business_registration_no" class="form-control form-control-sm rounded" id="business_registration_no" {{ $isView ? 'disabled' : ''}}>
                            @error('business_registration_no')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="gst_registration_no" class="form-label">GST Registration No.</label>
                            <input type="text" name="gst_registration_no" wire:model.live="gst_registration_no" class="form-control form-control-sm rounded" id="gst_registration_no" {{ $isView ? 'disabled' : ''}}>
                            @error('gst_registration_no')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="form-group mb-2">
                            <label for="currency" class="form-label">Currency <span class="text-danger">*</span></label>
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
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        

                        <div class="form-group mb-2">
                            <label for="salesman_id" class="form-label">Salesperson <span class="text-danger">*</span></label>
                            <select id="salesman_id" class="form-select form-select-sm rounded" wire:model.live="salesman_id" {{ $isView ? 'disabled' : ''}}>
                                <option value="">Select Salesperson</option>
                                @foreach(\App\Models\User::role('Salesperson')->orderBy('name','asc')->get() as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                                @endforeach
                            </select>
                            @error('salesman_id')
                                <p class="text-danger">{{ $message }}</p>
                            @enderror
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