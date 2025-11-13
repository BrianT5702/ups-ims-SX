<div class="container-fluid my-3">
    <div class="row">
        <div class="col-md-10 m-auto">
            <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="fw-bold fs-5 mb-0">
                    @if($filteredCategory)
                        {{ $filteredCategory->cat_name }} - Total Brand(s): {{ $categoryItemCount }}
                    @else
                        Manage Brand
                    @endif
                </h5>
                @if($filteredCategory)
                <a href="javascript:history.back()" class="btn btn-primary btn-sm">Back</a>
                @endif
            </div>

                <div class="card-body">
                    <div class="row align-items-end mb-3">
                        <div class="col-md-4">
                            <input type="text" wire:model.live.debounce.300ms="brandSearchTerm" class="form-control form-control-sm rounded" placeholder="Search brand...">
                        </div>
                        <div class="col-md-8">
                            <form wire:submit.prevent="addBrand" class="row g-2 align-items-end">
                                <div class="col">
                                    <label for="brand_name" class="form-label">Brand Name <span class="text-danger">*</span></label>
                                    @error('brand_name')
                                        <p class="text-danger small mt-1">{{ $message }}</p>
                                    @enderror
                                    <input type="text" name="brand_name" wire:model="brand_name" class="form-control form-control-sm rounded" id="brand_name">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary btn">Add Brand</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="table-responsive mt-3">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Brand Name <span wire:click="sortBy('brand_name')">
                                            @if ($sortColumn === 'brand_name')
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
                                @forelse($brands as $brand)
                                    <tr>
                                        <td>{{ ($brands->firstItem() ?? 0) + $loop->index }}</td>
                                        <td>
                                                {{ $brand->brand_name }}
                                            </a>
                                        </td>
                                        <td>
                                            <button wire:confirm="Are you sure you want to delete?" wire:click="deleteBrand({{ $brand->id }})" type="button" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No brands found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $brands->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>