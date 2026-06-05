from pathlib import Path

ROOT = Path(__file__).resolve().parent.parent
VIEWS = ROOT / "resources" / "views" / "livewire"

base = (VIEWS / "category-list.blade.php").read_text(encoding="utf-8")

configs = [
    (
        "family-list.blade.php",
        [
            ("Manage Category", "Manage Family"),
            ("categorySearchTerm", "familySearchTerm"),
            ("Search category", "Search family"),
            ("addCategory", "addFamily"),
            ("cat_name", "family_name"),
            ("Category Name", "Family Name"),
            ("Add Category", "Add Family"),
            ("sortBy('cat_name')", "sortBy('family_name')"),
            ("$sortColumn === 'cat_name'", "$sortColumn === 'family_name'"),
            ("$categories", "$families"),
            ("$category", "$family"),
            ("deleteCategory", "deleteFamily"),
            ("No categories found", "No families found"),
        ],
    ),
    (
        "group-list.blade.php",
        [
            ("Manage Category", "Manage Group"),
            ("categorySearchTerm", "groupSearchTerm"),
            ("Search category", "Search group"),
            ("addCategory", "addGroup"),
            ("cat_name", "group_name"),
            ("Category Name", "Group Name"),
            ("Add Category", "Add Group"),
            ("sortBy('cat_name')", "sortBy('group_name')"),
            ("$sortColumn === 'cat_name'", "$sortColumn === 'group_name'"),
            ("$categories", "$groups"),
            ("$category", "$group"),
            ("deleteCategory", "deleteGroup"),
            ("No categories found", "No groups found"),
        ],
    ),
]

for filename, pairs in configs:
    text = base
    for old, new in pairs:
        text = text.replace(old, new)
    (VIEWS / filename).write_text(text, encoding="utf-8")
    print("wrote", filename)
