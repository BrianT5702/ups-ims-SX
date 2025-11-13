<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Category;
use App\Rules\UniqueInCurrentDatabase;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;

#[Title('UR | Manage Category')]
class CategoryList extends Component
{
    use WithPagination; // Use WithPagination instead of just Pagination

    public $cat_name;

    public $categorySearchTerm = null;
    public $activePageNumber = 1;

    public $sortColumn = 'cat_name';
    public $sortOrder = 'asc';

    public function addCategory()
    {
        $this->validate([
            'cat_name' => ['required', 'min:3', 'max:50', new UniqueInCurrentDatabase('categories', 'cat_name')],
        ], [
            'cat_name.required' => 'The category name field is required.',
            'cat_name.min' => 'The category name must be at least 3 characters.',
            'cat_name.max' => 'The category name may not be greater than 50 characters.',
            'cat_name.unique' => 'This category name is already taken. Please enter a different one.',
        ]);

        try {
            Category::create([
                'cat_name' => $this->cat_name,
            ]);

            $this->cat_name = '';
            toastr()->success('Category added successfully');
        } catch (\Exception $e) {
            toastr()->error('An error occurred while adding the category: ' . $e->getMessage());
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

    public function fetchCategories(){
        return Category::where('cat_name', 'like', '%'. $this->categorySearchTerm. '%')->
        orderBy($this->sortColumn, $this->sortOrder)->
        paginate(8); 
    }

    public function render() {
        $categories = $this->fetchCategories();
        return view('livewire.category-list', compact('categories'))->layout('layouts.app');
    }

    public function deleteCategory(Category $category){


        if($category){
            if ($category->items()->exists()) {
                toastr()->error('This category cannot be deleted because it associated with item(s).');
                return;
            }
            try{
                $category->delete();
                toastr()->success('Category deleted successfully');
            }catch(\Exception $e){
                toastr()->error('An error occurred while deleting the category'. $e->getMessage());
            }
        }


        $categories = $this->fetchCategories();

        if($categories->isEmpty() && $this->activePageNumber > 1){
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
