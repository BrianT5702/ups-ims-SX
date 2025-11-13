<div class="container-fluid my-3">
    <div class="row">
        <div class="col-md-10 m-auto">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5 mb-0">Manage Category</h5>
                </div>
                <div class="card-body">
                    <div class="row align-items-end mb-3">
                        <div class="col-md-4">
                            <input type="text" wire:model.live.debounce.300ms="categorySearchTerm" class="form-control form-control-sm rounded" placeholder="Search category...">
                        </div>
                        <div class="col-md-8">
                            <form wire:submit.prevent="addCategory" class="row g-2 align-items-end">
                                <div class="col">
                                    <label for="cat_name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                    @error('cat_name')
                                        <p class="text-danger small mt-1">{{ $message }}</p>
                                    @enderror
                                    <input type="text" name="cat_name" wire:model="cat_name" class="form-control form-control-sm rounded" id="cat_name">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary btn">Add Category</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Category Name <span wire:click="sortBy('cat_name')">
                                            @if ($sortColumn === 'cat_name')
                                                @if ($sortOrder === 'asc')
                                                    <i class="fa-solid fa-sort-up"></i>
                                                @else
                                                    <i class="fa-solid fa-sort-down"></i>
                                                @endif
                                            @else
                                                <i class="fa-solid fa-sort"></i>
                                            @endif
                                    </span>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ ($categories->firstItem() ?? 0) + $loop->index }}</td>
                                        <td>
                                                {{ $category->cat_name }}</td>
                                            </a>
                                        <td>
                                            <button wire:confirm="Are you sure you want to delete?" wire:click="deleteCategory({{ $category->id }})" type="button" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No categories found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $categories->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>