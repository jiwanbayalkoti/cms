# Project-Wise Management Implementation Summary

## Overview
The system has been updated to support project-wise data management, similar to the existing company-wise management. All project-specific data is now automatically filtered based on the selected project.

## Implementation Details

### 1. Core Components Created

#### ProjectContext (`app/Support/ProjectContext.php`)
- Similar to `CompanyContext`
- Manages active project selection via session
- Methods:
  - `getActiveProjectId()` - Returns currently active project ID
  - `setActiveProjectId($projectId)` - Sets active project
  - `clearActiveProject()` - Clears project selection

#### ProjectScoped Trait (`app/Models/Traits/ProjectScoped.php`)
- Similar to `CompanyScoped` trait
- Automatically filters queries by `project_id` when project is selected
- Automatically sets `project_id` on model creation

### 2. Database Changes

#### Migration: `2025_12_07_100000_add_project_id_to_construction_materials_table.php`
- Added `project_id` column to `construction_materials` table
- Foreign key relationship to `projects` table
- Nullable (allows materials without specific project)

### 3. Models Updated

All models that have `project_id` now use `ProjectScoped` trait:

1. **ConstructionMaterial** - Added `project_id` and `ProjectScoped` trait
2. **Income** - Already had `project_id`, added `ProjectScoped` trait
3. **Expense** - Already had `project_id`, added `ProjectScoped` trait
4. **CompletedWork** - Already had `project_id`, added `ProjectScoped` trait
5. **VehicleRent** - Already had `project_id`, added `ProjectScoped` trait
6. **JournalEntry** - Already had `project_id`, added `ProjectScoped` trait
7. **SalesInvoice** - Already had `project_id`, added `ProjectScoped` trait
8. **PurchaseInvoice** - Already had `project_id`, added `ProjectScoped` trait
9. **BillModule** - Already had `project_id`, added `ProjectScoped` trait

### 4. Controllers Updated

#### ProjectController
- Added `switch()` method to handle project selection
- Validates project belongs to active company
- Updates session with selected project

### 5. Routes Added

```php
Route::post('projects/switch', [ProjectController::class, 'switch'])->name('projects.switch');
```

### 6. UI Updates

#### Layout (`resources/views/admin/layout.blade.php`)
- Added project selector dropdown in header
- Shows all active projects for current company
- "All Projects" option to clear project filter
- Automatically filters when project is selected

## How It Works

### Project Selection
1. User selects a project from dropdown in header
2. Selection is stored in session via `ProjectContext::setActiveProjectId()`
3. All queries are automatically filtered by selected project

### Automatic Filtering
- When a project is selected, all models using `ProjectScoped` trait automatically filter by `project_id`
- When "All Projects" is selected (null), no project filter is applied
- Data is still filtered by company (via `CompanyScoped`)

### Data Creation
- When creating new records, `project_id` is automatically set if a project is selected
- If no project is selected, `project_id` remains null (for company-wide data)

## Models That Should NOT Have Project ID

These models are company-wide and should NOT have project_id:
- Category, Subcategory (company-wide categories)
- Staff, Position (company-wide staff)
- Supplier, Customer (company-wide contacts)
- ChartOfAccount, BankAccount (company-wide accounts)
- MaterialCategory, MaterialName, MaterialUnit (company-wide master data)
- PaymentMode, WorkType, PurchasedBy (company-wide master data)
- BillCategory, BillSubcategory (company-wide bill categories)
- Company, Project (parent entities)

## Usage Examples

### In Controllers
```php
// No need to manually filter - ProjectScoped handles it automatically
$materials = ConstructionMaterial::all(); // Automatically filtered by project if selected

// To get all projects regardless of selection
$allMaterials = ConstructionMaterial::withoutGlobalScope('project')->get();
```

### In Views
```php
// Project selector is automatically shown in layout
// User can select project from dropdown
```

## Testing Checklist

- [x] Migration runs successfully
- [x] ProjectContext works correctly
- [x] ProjectScoped trait filters queries
- [x] Project selector appears in layout
- [x] Project switching works
- [x] Models automatically get project_id on creation
- [ ] Test with multiple projects
- [ ] Test with no project selected
- [ ] Verify data isolation between projects

## Next Steps (Optional Enhancements)

1. **Project Dashboard** - Show project-specific statistics
2. **Project Reports** - Generate reports filtered by project
3. **Project Permissions** - Restrict user access to specific projects
4. **Project Budget Tracking** - Track budget vs actual per project
5. **Project Timeline** - Visual timeline of project activities

## Notes

- Project filtering is optional - users can work with "All Projects" to see company-wide data
- Project selection persists in session until changed or cleared
- Project must belong to active company (enforced in switch method)
- All existing data without project_id will still work (nullable field)

