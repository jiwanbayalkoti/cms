# Construction Final Bill / Estimate Module

## Overview

This module provides comprehensive functionality for managing construction final bills and estimates (BOQ - Bill of Quantities). It includes automatic calculations for wastage, taxes, overhead, and contingency, along with measurement tracking and approval workflows.

## Features

- ✅ Create and manage bill modules with multiple items
- ✅ Automatic calculation of amounts, wastage, taxes, and aggregates
- ✅ Support for multiple work categories (Earthwork, RCC, Masonry, etc.)
- ✅ Measurement tracking with photos and MB references
- ✅ Approval workflow (Draft → Submitted → Approved/Rejected)
- ✅ Excel and PDF export
- ✅ Audit trail/history
- ✅ Company-scoped multi-tenancy
- ✅ REST API endpoints
- ✅ Role-based access control

## Database Structure

### Tables Created

1. **bill_modules** - Main bill/estimate records
2. **bill_items** - Individual line items in each bill
3. **bill_aggregates** - Calculated totals (subtotal, tax, overhead, contingency, grand total)
4. **measurements** - Measurement records linked to items
5. **bill_history** - Audit trail of all actions
6. **bill_settings** - Company-specific default settings

## Installation & Setup

### 1. Run Migrations

```bash
php artisan migrate
```

**Note:** If you encounter "Table already exists" error, you may need to:
- Drop the tables manually, OR
- Mark migrations as run: `php artisan migrate:status` and manually insert into `migrations` table

### 2. Seed Sample Data (Optional)

```bash
php artisan db:seed --class=BillModuleSeeder
```

This creates:
- Default bill settings for your company
- A sample bill module with 8 items across different categories

### 3. Environment Configuration

No additional environment variables required. The module uses existing:
- Database connection
- Storage configuration (for attachments)
- Authentication system

## Usage Guide

### Web Interface

#### Access the Module

Navigate to: **Admin Panel → Final Bills / Estimates**

Or directly: `http://your-domain/admin/bill-modules`

#### Create a New Bill

1. Click "Create Bill"
2. Select a project
3. Enter bill title, MB number, dates
4. Add items using the inline table editor:
   - Category (required)
   - Description (required)
   - Unit of Measurement (required)
   - Quantity (required)
   - Unit Rate (required)
   - Wastage % (optional, default 0)
   - Tax % (optional, default 13%)
   - Remarks (optional)
5. Set overhead and contingency percentages
6. Click "Create Bill"

#### Edit a Bill

- Only draft or rejected bills can be edited
- Approved bills require creating a new version

#### Submit for Approval

- From the bill detail page, click "Submit for Approval"
- Status changes from "Draft" to "Submitted"

#### Approve/Reject

- Only users with admin/approver role can approve
- From bill detail page, use "Approve" or "Reject" buttons
- Add optional comment

#### View Report

- Click "View Report" to see printable BOQ format
- Suitable for NCB/DUDBC submissions
- Includes all items, totals, and signature spaces

#### Export

- **Excel Export**: Click "Export Excel" to download itemized BOQ with totals
- **PDF Export**: Click "Export PDF" (currently returns printable view, can be enhanced with dompdf)

### API Endpoints

All API endpoints require authentication via Sanctum.

#### List Bills

```
GET /api/projects/{projectId}/bills
GET /api/bills?project_id={id}&status={status}
```

#### Create Bill

```
POST /api/projects/{projectId}/bills
Content-Type: application/json

{
  "title": "Building Foundation Bill",
  "version": "1.0",
  "mb_number": "MB-2024-001",
  "mb_date": "2024-01-15",
  "notes": "Foundation work",
  "items": [
    {
      "category": "Earthwork",
      "description": "Excavation",
      "uom": "Cum",
      "quantity": 150.5,
      "unit_rate": 450.00,
      "wastage_percent": 5,
      "tax_percent": 13
    }
  ],
  "overhead_percent": 10,
  "contingency_percent": 5
}
```

#### Get Bill Details

```
GET /api/bills/{id}
```

#### Update Bill

```
PUT /api/bills/{id}
```

#### Approve/Reject Bill

```
POST /api/bills/{id}/approve
{
  "action": "approve",  // or "reject"
  "comment": "Approved as per specifications"
}
```

#### Add Measurement

```
POST /api/bills/{billId}/items/{itemId}/measure
{
  "measured_quantity": 145.5,
  "measure_date": "2024-01-20",
  "note": "Site measurement",
  "photo_urls": ["url1", "url2"],
  "mb_reference": "MB-PG-001"
}
```

#### Export Excel

```
GET /api/bills/{id}/export/excel
```

#### Export PDF

```
GET /api/bills/{id}/export/pdf
```

#### Get History

```
GET /api/bills/{id}/history
```

## Business Logic

### Calculations

All calculations are handled by `BillCalculatorService`:

1. **Item Amount**: `quantity × unit_rate`
2. **Effective Quantity**: `quantity × (1 + wastage_percent/100)`
3. **Total Amount**: `effective_quantity × unit_rate`
4. **Net Amount**: `total_amount × (1 + tax_percent/100)`
5. **Aggregate Subtotal**: `SUM(total_amount)` of all items
6. **Tax Total**: `SUM(total_amount × tax_percent/100)`
7. **Overhead Amount**: `subtotal × overhead_percent/100`
8. **Contingency Amount**: `subtotal × contingency_percent/100`
9. **Grand Total**: `subtotal + tax_total + overhead_amount + contingency_amount`

All amounts are rounded to 2 decimal places.

### Work Categories

Default categories (configurable in `bill_settings`):
- Earthwork
- RCC
- Masonry
- Plaster
- Flooring
- Doors/Windows
- Electrical
- Plumbing
- Finishing
- Structural Steel
- Carpentry
- Miscellaneous

## Permissions & Roles

- **Contractor/Editor**: Create and edit draft bills, upload measurements
- **Engineer/Reviewer**: Submit bills for approval, add comments
- **Approver/Client**: Approve or reject final bills
- **Admin/Super Admin**: Full access

## File Attachments

- Supported types: JPG, JPEG, PNG, PDF
- Stored in: `storage/app/public/materials/`
- Max size: Configurable (default 5MB)
- Attachments are stored as JSON array of URLs in `bill_items.attachments`

## Testing

### Unit Tests

```bash
php artisan test --filter BillCalculatorServiceTest
```

### API Tests

```bash
php artisan test --filter BillModuleApiTest
```

## Troubleshooting

### Migration Errors

If you see "Table already exists":
```bash
# Option 1: Drop tables manually
php artisan tinker
>>> Schema::dropIfExists('bill_history');
>>> Schema::dropIfExists('measurements');
>>> Schema::dropIfExists('bill_aggregates');
>>> Schema::dropIfExists('bill_items');
>>> Schema::dropIfExists('bill_modules');
>>> Schema::dropIfExists('bill_settings');

# Option 2: Mark as migrated
# Insert records into migrations table manually
```

### Calculation Issues

- Ensure `BillCalculatorService` is properly injected
- Check that all items have valid quantity and unit_rate
- Verify tax_percent is between 0-100

### Permission Issues

- Check user role in `users` table
- Verify middleware is applied: `admin` for web, `auth:sanctum` for API

## Future Enhancements

- [ ] PDF generation using dompdf/tcpdf
- [ ] Excel import template for bulk item entry
- [ ] Versioning system (clone approved bills)
- [ ] Measurement certificate PDF per item
- [ ] Rate analysis breakdown (material/labor/equipment)
- [ ] Multi-currency support
- [ ] Email notifications on status changes

## Support

For issues or questions, refer to:
- Model files: `app/Models/Bill*.php`
- Controllers: `app/Http/Controllers/Admin/BillModuleController.php`
- Service: `app/Services/BillCalculatorService.php`
- Views: `resources/views/admin/bill_modules/`

## License

Same as main project license.

