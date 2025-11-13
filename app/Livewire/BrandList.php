<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Brand;
use App\Rules\UniqueInCurrentDatabase;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use App\Models\Category;

#[Title('UR | Manage Brand')]
class BrandList extends Component
{
    use WithPagination; // Use WithPagination instead of just Pagination

    public $brand_name;

    public $filterCategoryId = null;

    public $brandSearchTerm = null;
    public $activePageNumber = 1;

    public $sortColumn = 'brand_name';
    public $sortOrder = 'asc';

    public function mount($categoryId = null)
    {
        $this->filterCategoryId = $categoryId;
    }


    public function addBrand()
    {
        $this->validate([
            'brand_name' => ['required', 'min:3', 'max:50', new UniqueInCurrentDatabase('brands', 'brand_name')],
        ], [
            'brand_name.required' => 'The brand name field is required.',
            'brand_name.min' => 'The brand name must be at least 3 characters.',
            'brand_name.max' => 'The brand name may not be greater than 50 characters.',
            'brand_name.unique' => 'This brand name is already taken. Please enter a different one.',
        ]);

        try {
            Brand::create([
                'brand_name' => $this->brand_name,
            ]);

            $this->brand_name = '';
            toastr()->success('Brand added successfully');
        } catch (\Exception $e) {
            toastr()->error('An error occurred while adding the brand: ' . $e->getMessage());
        }
    }

    public function sortBy($columnName){
        if($this->sortColumn === $columnName){
            $this->sortOrder = $this->sortOrder === 'asc' ? 'desc' : 'asc';
        }else{
            $this->sortColumn = $columnName;
            $this->sortOrder = 'asc';
        }
    }

    public function fetchBrands()
    {
        $query = Brand::query();
        
        if ($this->filterCategoryId) {
            $brandIds = \App\Models\Item::where('cat_id', $this->filterCategoryId)
                ->distinct()
                ->pluck('brand_id');
            $query->whereIn('id', $brandIds);
        }
        
        $query->where('brand_name', 'like', '%' . $this->brandSearchTerm . '%')
              ->orderBy($this->sortColumn, $this->sortOrder);
        
        return $query->paginate(8);
    }

    public function render() 
    {
        $brands = $this->fetchBrands();
        $filteredCategory = $this->filterCategoryId ? Category::findOrFail($this->filterCategoryId) : null;
        
        $categoryItemCount = $this->filterCategoryId ? 
            \App\Models\Item::where('cat_id', $this->filterCategoryId)
                ->distinct('brand_id')
                ->count('brand_id') 
            : null;
        
        return view('livewire.brand-list', [
            'brands' => $brands,
            'filteredCategory' => $filteredCategory,
            'categoryItemCount' => $categoryItemCount,
        ])->layout('layouts.app');
    }

    public function deleteBrand(Brand $brand){


        if($brand){
            if ($brand->items()->exists()) {
                toastr()->error('This brand cannot be deleted because it associated with item(s).');
                return;
            }
            try{
                $brand->delete();
                toastr()->success('Brand deleted successfully');
            }catch(\Exception $e){
                toastr()->error('An error occurred while deleting the brand'. $e->getMessage());
            }
        }


        $brands = $this->fetchBrands();

        if($brands->isEmpty() && $this->activePageNumber > 1){
            $this->gotoPage($this->activePageNumber - 1);
        }

        else{
            $this->gotoPage($this->activePageNumber);
        }
    }

    public function updatingPage($pageNumber){
        $this->activePageNumber = $pageNumber;
    }
}
