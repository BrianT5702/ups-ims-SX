<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Validate;
use App\Models\Supplier;
use App\Rules\UniqueInCurrentDatabase;
use Livewire\Attributes\Title;

#[Title('UR | Manage Supplier')]

class SupplierForm extends Component
{
    public $isView = false;
    public $supplier = null;

    public $account;
    public $sup_name;
    public $address_line1;
    public $address_line2;
    public $address_line3;
    public $address_line4;
    public $phone_num;
    public $fax_num;
    public $email;
    public $area;
    public $term;
    public $business_registration_no;
    public $gst_registration_no;
    public $currency = 'MYR';

    public $termOptions = [
        'C.O.D',
        'CASH',
        'NET 30 DAY',
        '30 DAYS',
        '60 DAYS'
    ];

    protected function rules() 
    {
        return [
            'account' => $this->supplier 
                ? ['required', 'string', new UniqueInCurrentDatabase('suppliers', 'account', $this->supplier->id)]
                : ['required', 'string', new UniqueInCurrentDatabase('suppliers', 'account')],
            'sup_name' => 'required|min:3|max:60',
            'address_line1' => 'required|max:255',
            'address_line2' => 'required|max:255',
            'phone_num' => 'required',
            'fax_num' => 'nullable|max:20',
            'email' => 'nullable|email',
            'area' => 'nullable|string',
            'term' => 'required|in:C.O.D,NET 30 DAY,30 DAYS,60 DAYS,CASH',
            'business_registration_no' => 'nullable|string',
            'gst_registration_no' => 'nullable|string',
            'currency' => 'required|string|in:MYR,USD,SGD,EUR,GBP,JPY,CNY,THB,IDR,PHP',
        ];
    }

    protected function messages()
    {
        return [
            'account.required' => 'The account number field is required.',
            'account.unique' => 'This account number is already taken.',
            
            'sup_name.required' => 'The supplier name field is required.',
            'sup_name.min' => 'The supplier name must be at least 3 characters.',
            'sup_name.max' => 'The supplier name may not be greater than 60 characters.',
            
            'address_line1.required' => 'The address line 1 field is required.',
            'address_line1.max' => 'The address line 1 may not be greater than 255 characters.',
            
            'address_line2.required' => 'The address line 2 field is required.',
            'address_line2.max' => 'The address line 2 may not be greater than 255 characters.',
            
            'phone_num.required' => 'The phone number field is required.',
            'term.required' => 'The term field is required.',
            'term.in' => 'Please select a valid term.',
        ];
    }

    public function mount(Supplier $supplier) {
        $this->isView = request()->routeIs('suppliers.view');
        
        if ($supplier->id) {
            $this->supplier = $supplier;
            $this->account = $supplier->account;
            $this->sup_name = $supplier->sup_name;
            $this->address_line1 = $supplier->address_line1;
            $this->address_line2 = $supplier->address_line2;
            $this->address_line3 = $supplier->address_line3;
            $this->address_line4 = $supplier->address_line4;
            $this->phone_num = $supplier->phone_num;
            $this->fax_num = $supplier->fax_num;
            $this->email = $supplier->email;
            $this->area = $supplier->area;
            $this->term = $supplier->term;
            $this->business_registration_no = $supplier->business_registration_no;
            $this->gst_registration_no = $supplier->gst_registration_no;
            $this->currency = $supplier->currency ?? 'MYR';
        } else {
            // Ensure no default term is preselected on Add mode
            $this->term = '';
        }
    }

    public function updated($propertyName)
    {
        $this->resetErrorBag($propertyName);
        $this->validateOnly($propertyName);
    }

    public function addSupplier() {

        $validatedData = $this->validate();

        if ($this->supplier) {
            try {
                $this->supplier->update([
                    'account' => $this->account,
                    'sup_name' => $this->sup_name,
                    'address_line1' => $this->address_line1,
                    'address_line2' => $this->address_line2,
                    'address_line3' => $this->address_line3,
                    'address_line4' => $this->address_line4,
                    'phone_num' => $this->phone_num,
                    'fax_num' => $this->fax_num,
                    'email' => $this->email,
                    'area' => $this->area,
                    'term' => $this->term,
                    'business_registration_no' => $this->business_registration_no,
                    'gst_registration_no' => $this->gst_registration_no,
                    'currency' => $this->currency,
                ]);
                
                $this->resetErrorBag();

                toastr()->success('Supplier updated successfully');
            } catch (\Exception $e) {
                toastr()->error('An error occurred while updating the supplier: ' . $e->getMessage());
            }
        }else {
            try {
                Supplier::create([
                    'account' => $this->account,
                    'sup_name' => $this->sup_name,
                    'address_line1' => $this->address_line1,
                    'address_line2' => $this->address_line2,
                    'address_line3' => $this->address_line3,
                    'address_line4' => $this->address_line4,
                    'phone_num' => $this->phone_num,
                    'fax_num' => $this->fax_num,
                    'email' => $this->email,
                    'area' => $this->area,
                    'term' => $this->term,
                    'business_registration_no' => $this->business_registration_no,
                    'gst_registration_no' => $this->gst_registration_no,
                    'currency' => $this->currency,
                ]);
                
                $this->resetErrorBag();
                
                toastr()->success('Supplier added successfully');
            } catch (\Exception $e) {
                toastr()->error('An error occurred while adding the supplier: ' . $e->getMessage());
            }
        }

        return $this->redirect('/suppliers', navigate: true);
    }

    public function render() {
        return view('livewire.supplier-form')->layout('layouts.app');
    }
}
