<div class="container-fluid my-4">
    <div class="row">
        <div class="col-md-12 m-auto">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold fs-5 mb-0">Manage Locations</h5>
                    <button 
                        wire:click="toggleEditMode" 
                        class="btn {{ $isEditMode ? 'btn-danger' : 'btn-primary' }}"
                    >
                        <i class="fas {{ $isEditMode ? 'fa-lock' : 'fa-edit' }} me-2"></i>
                        {{ $isEditMode ? 'Exit Edit Mode' : 'Enter Edit Mode' }}
                    </button>
                </div>
                <div class="card-body">
                    <div class="row align-items-end mb-4">
                        <div class="col-md-4">
                            <div class="form-group position-relative" x-data="{ hi: 0 }">
                                <label class="form-label">Search Items</label>
                                <div class="input-group">
                                    <input 
                                        type="text" 
                                        wire:model.live.debounce.300ms="itemSearchTerm"
                                        class="form-control rounded" 
                                        placeholder="Search items by name or code..."
                                        x-on:input="hi = 0"
                                        x-on:keydown.arrow-down.prevent="(() => { const list = $refs.res; const items = list ? list.querySelectorAll('[data-idx]') : []; if(items.length===0) return; hi = Math.min(hi + 1, items.length - 1); $nextTick(() => { const el = items[hi]; if(!el) return; const elTop = el.offsetTop; const elBottom = elTop + el.offsetHeight; const viewTop = list.scrollTop; const viewBottom = viewTop + list.clientHeight; if (elTop < viewTop) { list.scrollTop = elTop; } else if (elBottom > viewBottom) { list.scrollTop = elBottom - list.clientHeight; } }); })()"
                                        x-on:keydown.arrow-up.prevent="(() => { const list = $refs.res; const items = list ? list.querySelectorAll('[data-idx]') : []; if(items.length===0) return; hi = Math.max(hi - 1, 0); $nextTick(() => { const el = items[hi]; if(!el) return; const elTop = el.offsetTop; const elBottom = elTop + el.offsetHeight; const viewTop = list.scrollTop; const viewBottom = viewTop + list.clientHeight; if (elTop < viewTop) { list.scrollTop = elTop; } else if (elBottom > viewBottom) { list.scrollTop = elBottom - list.clientHeight; } }); })()"
                                        x-on:keydown.enter.prevent="(() => { const list = $refs.res; const items = list ? list.querySelectorAll('[data-idx]') : []; const el = items && items[hi]; if(el) el.click(); })()"
                                    >
                                </div>
                                
                                @if(!empty($searchResults) && !$selectedItem)
                                    <div class="position-absolute w-100 mt-1 bg-white border rounded shadow-sm" 
                                         style="max-height: 300px; overflow-y: auto; z-index: 1000;" x-ref="res">
                                        @forelse($searchResults as $idx => $item)
                                            <div class="p-2 border-bottom cursor-pointer"
                                                 data-idx="{{ $idx }}"
                                                 wire:click="selectItem({{ $item['id'] }})"
                                                 x-ref="resItem"
                                                 :class="{ 'bg-primary text-white': hi === {{ $idx }} }">
                                                <div class="fw-bold">{{ $item['item_name'] }}</div>
                                                <div class="small text-muted">{{ $item['item_code'] }}</div>
                                            </div>
                                        @empty
                                            <div class="p-2 text-muted">No items found</div>
                                        @endforelse
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Select Warehouse</label>
                                <select 
                                    class="form-control rounded" 
                                    wire:model.live="selectedWarehouse"
                                    @if($selectedItem) disabled @endif
                                >
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" 
                                            {{ $selectedWarehouse == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->warehouse_name }}
                                            @if(isset($unassignedCounts[$warehouse->id]) && $unassignedCounts[$warehouse->id] > 0)
                                                - ({{ $unassignedCounts[$warehouse->id] }} unassigned)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-9">
                            @if($unassignedLocations->count() > 0)
                                <div class="mb-4">
                                    <h6 class="fw-bold mb-3">
                                        <i class="fas fa-inbox me-2"></i>
                                        Unassigned Locations
                                        <span class="ms-2 badge bg-warning text-dark">
                                            {{ $unassignedLocations->count() }} locations
                                        </span>
                                        <i class="fas fa-info-circle ms-2" 
                                            data-bs-toggle="tooltip" 
                                            data-bs-placement="right" 
                                            title="These locations need to be placed on the map. Drag and drop them in edit mode."></i>
                                    </h6>
                                    <div class="d-flex flex-wrap gap-2 p-3 bg-light rounded">
                                        @foreach($unassignedLocations as $location)
                                            <div 
                                                class="location-block {{ $isEditMode ? 'draggable' : '' }}"
                                                id="location-{{ $location['id'] }}" 
                                                data-id="{{ $location['id'] }}"
                                            >
                                                <div class="location-name">
                                                    {{ $location['location_name'] }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="map-container mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="fas fa-map me-2"></i>Location Map
                                    @if($isEditMode)
                                        <span class="badge bg-warning text-dark ms-2">Edit Mode</span>
                                    @endif
                                </h6>
                                <div 
                                    id="map" 
                                    class="{{ $isEditMode ? 'edit-mode' : '' }}"
                                >

                                <div class="warehouse-watermark">
                                    @foreach($warehouses as $warehouse)
                                        @if($warehouse->id == $selectedWarehouse)
                                            {{ $warehouse->warehouse_name }}
                                        @endif
                                    @endforeach
                                </div>

                                    <div id="map-content">
                                        @foreach($locations->whereNotNull('position_x')->whereNotNull('position_y') as $location)
                                        <div 
                                            class="location-block {{ $isEditMode ? 'draggable' : '' }} {{ in_array($location->id, $itemLocations) ? 'highlighted' : '' }}"
                                            id="location-{{ $location->id }}" 
                                            style="
                                                top: {{ $location->position_y }}px; 
                                                left: {{ $location->position_x }}px;
                                            " 
                                            data-id="{{ $location->id }}"
                                        >
                                            @if(!$isEditMode)
                                                <a href="{{ route('locations-items', ['locationId' => $location->id]) }}" 
                                                    class="d-block text-decoration-none"
                                                    style="height: 100%; width: 100%; display: block;">
                                                    <div class="location-name d-flex justify-content-center align-items-center" 
                                                        style="height: 100%;">
                                                        {{ $location->location_name }}
                                                    </div>
                                                </a>
                                            @else
                                                <div class="location-name d-flex justify-content-center align-items-center" 
                                                    style="height: 100%;">
                                                    {{ $location->location_name }}
                                                </div>
                                            @endif
                                        </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Location List Table -->
                        <div class="col-lg-3">
                            <h6 class="fw-bold  mb-3">
                                <i class="fas fa-list me-2"></i>Location List
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                        <tr>
                                        <th>
                                            Location Name
                                            <span wire:click="sortBy('location_name')" style="cursor: pointer;">
                                            </span>
                                        </th>
                                          <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($locations as $location)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('locations-items', ['locationId' => $location->id]) }}" 
                                                       class="text-decoration-none">
                                                        {{ $location->location_name }}
                                                    </a>
                                                </td>
                                                <td class="text-end">
                                                    <button 
                                                        wire:confirm="Are you sure you want to delete this location?" 
                                                        wire:click="deleteLocation({{ $location->id }})" 
                                                        class="btn btn-danger btn-sm">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="2" class="text-center ">
                                                    No locations found
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-center">
                                {{ $locations->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/interactjs/dist/interact.min.js"></script>
<script>
const style = document.createElement('style');
style.textContent = `
#map {
    width: 100%;
    height: 500px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background: white;
    position: relative;
    overflow: auto; /* allow scroll instead of refitting on small screens/zooms */
}

#map-content {
    position: absolute;
    top: 0;
    left: 0;
    width: 1200px; /* logical canvas width */
    height: 800px; /* logical canvas height */
    transform-origin: 0 0;
}

.warehouse-watermark {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-30deg);
    font-size: 48px;
    font-weight: bold;
    color: rgba(0, 0, 0, 0.05);
    white-space: nowrap;
    pointer-events: none;
    user-select: none;
    z-index: 0;
}

#map.edit-mode {
    background-color: #f8f9fa;
    border: 2px dashed #6c757d;
}

/* Location Block Styles */
.location-block {
    width: 120px;
    height: 60px;
    background: #ffffff;
    border: 2px solid #007bff;
    border-radius: 8px;
    position: absolute;
    user-select: none;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    
}

/* Unassigned locations style */
div:not(#map) > .location-block {
    position: relative;
    border-color: #dc3545;
    display: inline-flex;
}

.location-block.draggable {
    cursor: grab;
    touch-action: none;
}

.location-block.draggable:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.location-block.dragging {
    cursor: grabbing;
    opacity: 0.8;
    z-index: 1000;
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}

.location-name {
    font-size: 0.875rem;
    font-weight: 500;
    color: #495057;
    text-align: center;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

/* Card and Container Styles */
.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0,0,0,0.075);
}

.card-header {
    border-bottom: 1px solid #e9ecef;
}



/* Responsive Adjustments */
@media (max-width: 768px) {
    #map {
        height: 400px;
    }
}

.location-block.highlighted {
    border: 3px solid #ffc107;
    box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
    }
    50% {
        box-shadow: 0 0 20px rgba(255, 193, 7, 0.8);
    }
    100% {
        box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
    }
}

.hover-bg-light:hover {
    background-color: #f8f9fa;
}

.cursor-pointer {
    cursor: pointer;
}

.z-1000 {
    z-index: 1000;
}
`;
document.head.appendChild(style);

function initializeDraggable() {
    // Only make blocks draggable if they have the draggable class
    interact('.location-block.draggable').draggable({
        modifiers: [
            interact.modifiers.restrictRect({
                restriction: document.getElementById('map-content'),
                endOnly: true
            })
        ],
        inertia: false,
        autoScroll: true,
        listeners: {
            start(event) {
                const target = event.target;
                const map = document.getElementById('map');
                const mapContent = document.getElementById('map-content');
                const rect = target.getBoundingClientRect();
                const parentRect = target.parentElement.getBoundingClientRect();
                
                let x = rect.left - parentRect.left;
                let y = rect.top - parentRect.top;
                
                // If dragging from outside the map, move it into the map and position under cursor
                if (target.parentElement !== mapContent) {
                    const mapRect = map.getBoundingClientRect();
                    const pointerX = (event.client && event.client.x) || event.clientX || rect.left;
                    const pointerY = (event.client && event.client.y) || event.clientY || rect.top;
                    const desiredX = pointerX - mapRect.left - rect.width / 2;
                    const desiredY = pointerY - mapRect.top - rect.height / 2;
                    const maxX = mapContent.offsetWidth - rect.width;
                    const maxY = mapContent.offsetHeight - rect.height;
                    x = Math.min(Math.max(0, desiredX), Math.max(0, maxX));
                    y = Math.min(Math.max(0, desiredY), Math.max(0, maxY));
                    mapContent.appendChild(target);
                    target.style.left = `${x}px`;
                    target.style.top = `${y}px`;
                }
                
                target.setAttribute('data-x', x);
                target.setAttribute('data-y', y);
                target.classList.add('dragging');
                
                document.body.style.cursor = 'grabbing';
            },
            move(event) {
                const target = event.target;
                const x = (parseFloat(target.getAttribute('data-x')) || 0) + event.dx;
                const y = (parseFloat(target.getAttribute('data-y')) || 0) + event.dy;
                
                // Get map boundaries
                const mapContent = document.getElementById('map-content');
                const maxX = mapContent.offsetWidth - target.offsetWidth;
                const maxY = mapContent.offsetHeight - target.offsetHeight;
                
                // Constrain movement within boundaries
                const constrainedX = Math.min(Math.max(0, x), maxX);
                const constrainedY = Math.min(Math.max(0, y), maxY);
                
                // Update element position
                target.style.left = `${constrainedX}px`;
                target.style.top = `${constrainedY}px`;
                
                target.setAttribute('data-x', constrainedX);
                target.setAttribute('data-y', constrainedY);
            },
            end(event) {
                const target = event.target;
                target.classList.remove('dragging');
                document.body.style.cursor = 'default';
                
                const x = parseFloat(target.getAttribute('data-x'));
                const y = parseFloat(target.getAttribute('data-y'));
                const id = target.getAttribute('data-id');
                
                target.style.left = `${x}px`;
                target.style.top = `${y}px`;
                
                // Move the block into the map if it's not already there
                const mapContent = document.getElementById('map-content');
                if (target.parentElement !== mapContent) {
                    mapContent.appendChild(target);
                }
                
                Livewire.dispatch('updateLocationPosition', { 
                    id: id, 
                    x: x, 
                    y: y 
                });
            }
        }
    });
}

// Keep a fixed 1:1 scale so zoom/resize does not change the logical view
function fitMapContentToContainer() {
    const content = document.getElementById('map-content');
    if (!content) return;
    content.style.transform = `scale(1)`;
}

function clampBlocksToMapBounds() {
    const mapContent = document.getElementById('map-content');
    if (!mapContent) return;
    const blocks = mapContent.querySelectorAll('.location-block');
    blocks.forEach((target) => {
        const left = parseFloat(target.style.left) || 0;
        const top = parseFloat(target.style.top) || 0;
        const maxX = mapContent.offsetWidth - target.offsetWidth;
        const maxY = mapContent.offsetHeight - target.offsetHeight;
        const clampedX = Math.min(Math.max(0, left), Math.max(0, maxX));
        const clampedY = Math.min(Math.max(0, top), Math.max(0, maxY));
        if (clampedX !== left || clampedY !== top) {
            target.style.left = `${clampedX}px`;
            target.style.top = `${clampedY}px`;
        }
        target.setAttribute('data-x', clampedX);
        target.setAttribute('data-y', clampedY);
    });
}

function removeLocationBlock(locationId) {
    const locationBlocks = document.querySelectorAll(`#location-${locationId}`);
    locationBlocks.forEach(block => {
        block.remove();
    });
}


// Initialize draggable when the page loads
document.addEventListener('DOMContentLoaded', () => { initializeDraggable(); fitMapContentToContainer(); clampBlocksToMapBounds(); });

// Re-initialize draggable when Livewire updates the DOM
document.addEventListener('livewire:navigated', () => { initializeDraggable(); fitMapContentToContainer(); clampBlocksToMapBounds(); });
document.addEventListener('livewire:initialized', () => { initializeDraggable(); fitMapContentToContainer(); clampBlocksToMapBounds(); });

// Do not refit on resize to prevent frame changes with zoom
// window.addEventListener('resize', () => { fitMapContentToContainer(); clampBlocksToMapBounds(); });

Livewire.on('locationDeleted', (data) => {
    removeLocationBlock(data.locationId);
});
</script>