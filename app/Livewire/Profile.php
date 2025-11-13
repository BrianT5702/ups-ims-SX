<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\CompanyProfile;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('UR | Company Profile')]
class Profile extends Component
{
    // Use Validate attribute for each field
    #[Validate('required|string|max:255', message: 'Company name is required')]
    public $company_name;

    #[Validate('required|string|max:255', message: 'Company number is required')]
    public $company_no;

    #[Validate('required|string|max:255', message: 'GST registration number is required')]
    public $gst_no;

    #[Validate('required|string|max:255', message: 'Address line 1 is required')]
    public $address_line1;

    #[Validate('required|string|max:255', message: 'Address line 2 is required')]
    public $address_line2;
    public $address_line3;
    public $address_line4;

    #[Validate('required|string|max:50', message: 'Primary phone number is required')]
    public $phone_num1;

    public $phone_num2;

    #[Validate('nullable|string|max:50', message: 'Fax number is required')]
    public $fax_num;

    #[Validate('required|email|max:255', message: 'A valid email is required')]
    public $email;

    // Mount method to load existing data
    public function mount()
    {
        $profile = CompanyProfile::first();
        
        if ($profile) {
            $this->company_name = $profile->company_name;
            $this->company_no = $profile->company_no;
            $this->gst_no = $profile->gst_no;
            $this->address_line1 = $profile->address_line1;
            $this->address_line2 = $profile->address_line2;
            $this->address_line3 = $profile->address_line3;
            $this->address_line4 = $profile->address_line4;
            $this->phone_num1 = $profile->phone_num1;
            $this->phone_num2 = $profile->phone_num2;
            $this->fax_num = $profile->fax_num;
            $this->email = $profile->email;
        }
    }

    // Update method
    public function updateProfile()
    {
        // Validate the form data
        $this->validate();

        try {
            // Find the first company profile or create a new one
            $profile = CompanyProfile::first() ?? new CompanyProfile();

            // Update the profile with validated data
            $profile->company_name = $this->company_name;
            $profile->company_no = $this->company_no;
            $profile->gst_no = $this->gst_no;
            $profile->address_line1 = $this->address_line1;
            $profile->address_line2 = $this->address_line2;
            $profile->address_line3 = $this->address_line3;
            $profile->address_line4 = $this->address_line4;
            $profile->phone_num1 = $this->phone_num1;
            $profile->phone_num2 = $this->phone_num2;
            $profile->fax_num = $this->fax_num;
            $profile->email = $this->email;

            // Save the profile
            $profile->save();

            // Success notification
            toastr()->success('Company profile updated successfully');
        } catch (\Exception $e) {
            // Error notification
            toastr()->error('An error occurred while updating the company profile: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.profile')->layout('layouts.app');
    }
}