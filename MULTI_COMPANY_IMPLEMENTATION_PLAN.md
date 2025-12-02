# Multi-Company Implementation Plan
## Income & Expense Calculator System

---

## **📋 Overview**
Transform the single-company system into a multi-company/multi-tenant system where users can manage multiple companies with complete data isolation.

---

## **🎯 Core Requirements**

### **Business Goals:**
1. **Multiple Companies**: Users can create and manage multiple companies
2. **User Roles**: Company-specific roles (Owner, Admin, Manager, Staff)
3. **Data Isolation**: Complete separation of data between companies
4. **Company Switching**: Users can switch between companies they have access to
5. **Separate Reporting**: Each company has its own reports and analytics

---

## **📊 Database Architecture**

### **Phase 1: New Tables**

#### **1. Companies Table**
```sql
- id (primary key)
- name (company name)
- slug (unique URL-friendly identifier)
- logo (file path)
- email (company email)
- phone (company phone)
- address (text)
- tax_id (optional)
- is_active (boolean)
- created_by (foreign key to users)
- timestamps
```

#### **2. User-Company Relationship Table**
```sql
- id (primary key)
- user_id (foreign key)
- company_id (foreign key)
- role (enum: 'owner', 'admin', 'manager', 'staff')
- is_active (boolean)
- timestamps
- unique constraint (user_id, company_id)
```

#### **3. Company Settings Table (Optional)**
```sql
- id (primary key)
- company_id (foreign key)
- currency (default: USD)
- date_format (default: Y-m-d)
- fiscal_year_start (date)
- timezone (default: UTC)
- timestamps
```

---

### **Phase 2: Update Existing Tables**

#### **Add `company_id` Foreign Key to:**
- ✅ `categories` table
- ✅ `subcategories` table
- ✅ `positions` table
- ✅ `staff` table
- ✅ `incomes` table
- ✅ `expenses` table

#### **Update Unique Constraints:**
- Remove global unique constraints (like positions.name, staff.email)
- Add composite unique constraints with company_id

---

## **🏗️ Application Architecture**

### **1. Middleware Stack**

#### **CompanyMiddleware** (New)
- Ensures user has access to the company
- Stores current company in session
- Redirects to company selection if not set

#### **CompanyAccessMiddleware** (New)
- Checks user's role for company
- Differentiate permissions based on role

#### **Update AdminMiddleware**
- Keep for system-level admin access
- Company-specific admins will use CompanyAccessMiddleware

---

### **2. Models & Relationships**

#### **Company Model** (New)
```php
Relationships:
- belongsTo(User::class, 'created_by') // Company creator
- hasMany(UserCompany::class) // User-company relationships
- hasMany(Category::class)
- hasMany(Position::class)
- hasMany(Staff::class)
- hasMany(Income::class)
- hasMany(Expense::class)
```

#### **UserCompany Model** (New) - Pivot Table
```php
Relationships:
- belongsTo(User::class)
- belongsTo(Company::class)
```

#### **Update User Model**
```php
Add Relationships:
- belongsToMany(Company::class)->withPivot('role', 'is_active')
- hasOne('ownedCompanies', Company::class, 'created_by')
```

#### **Update All Resource Models**
- Add `company_id` to fillable
- Add `belongsTo(Company::class)` relationship
- Add global scope for company filtering

---

### **3. Scoping System**

#### **Global Scope for Data Isolation**
```php
// Automatic filtering by company_id
class CompanyScope implements Scope {
    public function apply(Builder $builder, Model $model) {
        if (auth()->check() && session('current_company_id')) {
            $builder->where('company_id', session('current_company_id'));
        }
    }
}
```

Apply to: Category, Subcategory, Position, Staff, Income, Expense

---

### **4. Controllers**

#### **CompanyController** (New)
- CRUD for companies
- User-company association
- Company switching
- Company settings management

#### **Update All Resource Controllers**
- Add company context to all queries
- Filter by company_id in all operations
- Update validation rules

---

### **5. Routes**

#### **Company Routes**
```php
/admin/companies (CRUD)
/admin/companies/switch (POST) // Switch active company
/admin/companies/{id}/members (Manage user access)
/admin/companies/{id}/settings (Company settings)
```

#### **Update Existing Routes**
- All admin routes will be scoped to current company
- Reports will be company-specific

---

## **🎨 User Interface**

### **1. Company Switcher** (New Component)
- Dropdown in top navigation
- Show current company name/logo
- List accessible companies
- Quick switch functionality

### **2. Company Management Pages**
- List all companies (for owners/admins)
- Create new company
- Edit company details
- Manage company members
- Company settings

### **3. User Invitation System** (Future)
- Invite users to company via email
- Role assignment during invitation
- Invitation acceptance flow

---

## **👥 User Roles & Permissions**

### **System-Level Roles:**
- **Super Admin**: Access to all companies and system settings

### **Company-Level Roles:**
1. **Owner** (Company Creator)
   - Full control: CRUD all data
   - Manage company members
   - Company settings
   - Cannot be removed

2. **Admin**
   - Full CRUD for categories, staff, positions
   - Full CRUD for income/expenses
   - View all reports
   - Cannot manage company members

3. **Manager**
   - View all data
   - Create/edit income/expenses
   - View reports
   - No access to categories/staff management

4. **Staff**
   - View own data only
   - Limited report access

---

## **📝 Implementation Steps**

### **Phase 1: Foundation (Priority 1)**
1. ✅ Create `companies` table migration
2. ✅ Create `user_company` pivot table migration
3. ✅ Add `company_id` to all resource tables
4. ✅ Create Company model with relationships
5. ✅ Create UserCompany pivot model
6. ✅ Update User model relationships
7. ✅ Create CompanyController
8. ✅ Add company management routes

### **Phase 2: Data Isolation (Priority 1)**
9. ✅ Create CompanyMiddleware
10. ✅ Create CompanyScope (global scope)
11. ✅ Apply scope to all models
12. ✅ Update all controllers to use company context
13. ✅ Add company_id validation to all forms

### **Phase 3: UI Implementation (Priority 2)**
14. ✅ Create company switcher component
15. ✅ Add company management views
16. ✅ Update layout to include company switcher
17. ✅ Update all forms with company_id
18. ✅ Create company list/index view

### **Phase 4: Access Control (Priority 2)**
19. ✅ Create role-based access control
20. ✅ Create CompanyAccessMiddleware
21. ✅ Add role checks to controllers
22. ✅ Update views based on roles
23. ✅ Create permission helpers

### **Phase 5: Enhanced Features (Priority 3)**
24. ✅ Company settings page
25. ✅ User invitation system
26. ✅ Company analytics dashboard
27. ✅ Data export per company
28. ✅ Company deletion (with data backup)

---

## **🔐 Security Considerations**

1. **Data Isolation**: Strict company_id filtering on all queries
2. **SQL Injection**: Use Eloquent ORM, parameterized queries
3. **Authorization**: Check company access on every request
4. **CSRF Protection**: All forms protected
5. **File Uploads**: Separate storage by company_id
6. **Session Management**: Secure company switching
7. **Audit Logging**: Track company data changes

---

## **📊 Migration Strategy**

### **For Existing Data:**
1. Create default "Main Company"
2. Migrate existing data to this company
3. Assign all users as company owners
4. User can create additional companies after migration

### **Rollback Plan:**
- Keep backup of single-company version
- Migration down scripts available
- Data preservation during rollback

---

## **🧪 Testing Requirements**

1. **Unit Tests**: Model relationships and scopes
2. **Feature Tests**: CRUD operations with company isolation
3. **Integration Tests**: Multi-company workflows
4. **Security Tests**: Unauthorized access attempts
5. **Performance Tests**: Large datasets per company

---

## **📈 Future Enhancements**

1. **Subscription Management**: Billing per company
2. **API Access**: Company-specific API keys
3. **White Labeling**: Custom branding per company
4. **Data Import/Export**: Bulk operations
5. **Advanced Analytics**: Cross-company comparisons
6. **Mobile App**: Company-aware mobile application
7. **Multi-Currency**: Different currencies per company
8. **Fiscal Year Settings**: Custom fiscal periods

---

## **📊 Estimated Timeline**

- **Phase 1**: 2-3 days
- **Phase 2**: 2-3 days
- **Phase 3**: 2-3 days
- **Phase 4**: 2-3 days
- **Phase 5**: 3-5 days

**Total: 11-17 days** (depending on complexity and testing)

---

## **✅ Acceptance Criteria**

1. ✅ Users can create and manage multiple companies
2. ✅ Data is completely isolated between companies
3. ✅ Users can switch between accessible companies
4. ✅ Role-based access control works correctly
5. ✅ All existing features work with company context
6. ✅ Reports are company-specific
7. ✅ File uploads are organized by company
8. ✅ No data leakage between companies
9. ✅ Migration preserves existing data
10. ✅ UI is intuitive and responsive

---

## **🚀 Getting Started**

Would you like me to start implementing this? I can begin with Phase 1 immediately.

**Start with:** Creating the company table and basic models.


