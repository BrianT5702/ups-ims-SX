<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Customer;
use App\Models\User;
use App\Rules\UniqueInCurrentDatabase;
use App\Rules\ExistsInCurrentDatabase;
use Livewire\Attributes\Title;

#[Title('UR | Manage Customer')]

class CustomerForm extends Component
{
    public $isView = false;
    public $customer = null;

    public $account;
    public $cust_name;
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
    public $salesman_id;
    public $currency = 'RM';


    public $pricingTiers = [];

    public $termOptions = [
        'C.O.D',
        '30 DAYS',
        'CASH'
    ];

    protected function rules() 
    {
        return [
            'account' => $this->customer 
                ? ['nullable', 'string', new UniqueInCurrentDatabase('customers', 'account', $this->customer->id)]
                : ['nullable', 'string', new UniqueInCurrentDatabase('customers', 'account')],
            'cust_name' => 'required|min:3|max:60',
            'address_line1' => 'nullable|max:255',
            'address_line2' => 'nullable|max:255',
            'phone_num' => 'nullable',
            'fax_num' => 'nullable|max:20',
            'email' => 'nullable|email',
            'area' => 'nullable|string',
            'term' => 'nullable|in:C.O.D,30 DAYS,CASH',
            'business_registration_no' => 'nullable|string',
            'gst_registration_no' => 'nullable|string',
            'salesman_id' => ['required', new ExistsInCurrentDatabase('users', 'id')],
            'currency' => 'nullable|string|in:RM,USD,SGD,EUR,GBP,JPY,CNY,THB,IDR,PHP',
        ];
    }

    protected function messages()
    {
        return [
            'account.unique' => 'This account number is already taken.',
            
            'cust_name.required' => 'The customer name field is required.',
            'cust_name.min' => 'The customer name must be at least 3 characters.',
            'cust_name.max' => 'The customer name may not be greater than 60 characters.',
            
            'address_line1.max' => 'The address line 1 may not be greater than 255 characters.',
            'address_line2.max' => 'The address line 2 may not be greater than 255 characters.',

            'salesman_id.required' => 'The salesperson field is required.',
            'term.in' => 'Please select a valid term.',
        ];
    }

    public function mount(Customer $customer) {
        $this->isView = request()->routeIs('customers.view');
        
        if ($customer->id) {
            $customer->load('salesman');
            $this->customer = $customer;
            $this->account = $customer->account;
            $this->cust_name = $customer->cust_name;
            $this->address_line1 = $customer->address_line1;
            $this->address_line2 = $customer->address_line2;
            $this->address_line3 = $customer->address_line3;
            $this->address_line4 = $customer->address_line4;
            $this->phone_num = $customer->phone_num;
            $this->fax_num = $customer->fax_num;
            $this->email = $customer->email;
            $this->area = $customer->area;
            $this->term = $customer->term;
            $this->business_registration_no = $customer->business_registration_no;
            $this->gst_registration_no = $customer->gst_registration_no;
            $this->salesman_id = $customer->salesman_id;
            $this->currency = $customer->currency ?? 'RM';
        }else {
            // Ensure no default term is preselected on Add mode
            $this->term = '';
        }
    }

    public function updated($propertyName)
    {
        $this->resetErrorBag($propertyName);
        $this->validateOnly($propertyName);
    }

    protected function getCustomerAttributes(): array
    {
        return [
            'account' => $this->account ?: null,
            'cust_name' => $this->cust_name,
            'address_line1' => $this->address_line1 ?: null,
            'address_line2' => $this->address_line2 ?: null,
            'address_line3' => $this->address_line3 ?: null,
            'address_line4' => $this->address_line4 ?: null,
            'phone_num' => $this->phone_num ?: null,
            'fax_num' => $this->fax_num ?: null,
            'email' => $this->email ?: null,
            'area' => $this->area ?: null,
            'term' => $this->term ?: null,
            'business_registration_no' => $this->business_registration_no ?: null,
            'gst_registration_no' => $this->gst_registration_no ?: null,
            'salesman_id' => $this->salesman_id,
            'currency' => $this->currency ?: 'RM',
        ];
    }

    public function addCustomer() {

        $this->validate();

        $attributes = $this->getCustomerAttributes();

        if ($this->customer) {
            try {
                $this->customer->update($attributes);
                $this->resetErrorBag();
                toastr()->success('Customer updated successfully');
            } catch (\Exception $e) {
                toastr()->error('An error occurred while updating the customer: ' . $e->getMessage());
            }
        } else {
            try {
                Customer::create($attributes);
                $this->resetErrorBag();
                toastr()->success('Customer added successfully');
            } catch (\Exception $e) {
                toastr()->error('An error occurred while adding the customer: ' . $e->getMessage());
            }
        }

        return $this->redirect('/customers', navigate: true);
    }

    public function render() {
        return view('livewire.customer-form')->layout('layouts.app');
    }
}