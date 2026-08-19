<?php

return [

    /*
    |--------------------------------------------------------------------------
    | ERP modules for role-based access
    |--------------------------------------------------------------------------
    | route_prefixes are matched with Str::is() against the current route name.
    */

    'groups' => [
        'projects' => 'Projects',
        'procurement' => 'Materials & Procurement',
        'billing' => 'Billing & Estimates',
        'hr' => 'Staff & HR',
        'finance' => 'Finance',
        'fleet' => 'Vehicles & Equipment',
        'admin' => 'Administration',
    ],

    'modules' => [
        'projects' => [
            'label' => 'Projects',
            'group' => 'projects',
            'routes' => ['admin.projects.*'],
        ],
        'milestones' => [
            'label' => 'Milestones & Tasks',
            'group' => 'projects',
            'routes' => ['admin.milestones.*', 'admin.tasks.*'],
        ],
        'tenders' => [
            'label' => 'Tender Management',
            'group' => 'projects',
            'routes' => ['admin.tenders.*'],
        ],
        'materials' => [
            'label' => 'Construction Materials',
            'group' => 'procurement',
            'routes' => [
                'admin.construction-materials.*',
                'admin.materials-report.*',
                'admin.material-categories.*',
                'admin.material-names.*',
                'admin.material-units.*',
                'admin.material-calculator.*',
                'admin.payment-modes.*',
                'admin.purchased-bies.*',
            ],
        ],
        'suppliers' => [
            'label' => 'Suppliers',
            'group' => 'procurement',
            'routes' => ['admin.suppliers.*'],
        ],
        'requisitions' => [
            'label' => 'Material Requisitions',
            'group' => 'procurement',
            'routes' => ['admin.requisitions.*'],
        ],
        'purchase_orders' => [
            'label' => 'Purchase Orders',
            'group' => 'procurement',
            'routes' => ['admin.purchase-orders.*'],
        ],
        'billing' => [
            'label' => 'Billing, BoQ & Tax Invoice',
            'group' => 'billing',
            'routes' => [
                'admin.boq.*',
                'admin.completed-work.*',
                'admin.boq-measurement-books.*',
                'admin.boq-bill-statements.*',
                'admin.tax-invoices.*',
                'admin.measurement-books.*',
                'admin.running-bills.*',
                'admin.bill-*',
            ],
        ],
        'staff' => [
            'label' => 'Staff',
            'group' => 'hr',
            'routes' => ['admin.staff.*'],
        ],
        'positions' => [
            'label' => 'Positions',
            'group' => 'hr',
            'routes' => ['admin.positions.*'],
        ],
        'departments' => [
            'label' => 'Departments',
            'group' => 'hr',
            'routes' => ['admin.departments.*'],
        ],
        'attendance' => [
            'label' => 'Attendance',
            'group' => 'hr',
            'routes' => ['admin.attendance.*'],
        ],
        'leaves' => [
            'label' => 'Leave Management',
            'group' => 'hr',
            'routes' => ['admin.leaves.*', 'admin.leave-types.*'],
        ],
        'payroll' => [
            'label' => 'Payroll',
            'group' => 'hr',
            'routes' => ['admin.payroll.*'],
        ],
        'subcontractors' => [
            'label' => 'Sub-contractors',
            'group' => 'hr',
            'routes' => ['admin.subcontractors.*'],
        ],
        'users' => [
            'label' => 'Users',
            'group' => 'admin',
            'routes' => ['admin.users.*', 'admin.user-activities.*'],
        ],
        'incomes' => [
            'label' => 'Income',
            'group' => 'finance',
            'routes' => ['admin.incomes.*'],
        ],
        'expenses' => [
            'label' => 'Expenses',
            'group' => 'finance',
            'routes' => ['admin.expenses.*'],
        ],
        'loans' => [
            'label' => 'Loans',
            'group' => 'finance',
            'routes' => ['admin.loans.*'],
        ],
        'reports' => [
            'label' => 'Reports',
            'group' => 'finance',
            'routes' => ['admin.reports.*'],
        ],
        'finance_setup' => [
            'label' => 'Categories & Payment Types',
            'group' => 'finance',
            'routes' => [
                'admin.categories.*',
                'admin.subcategories.*',
                'admin.expense-types.*',
                'admin.payment-types.*',
            ],
        ],
        'salary_payments' => [
            'label' => 'Salary Payments',
            'group' => 'finance',
            'routes' => ['admin.salary-payments.*'],
        ],
        'accounting' => [
            'label' => 'Accounting',
            'group' => 'finance',
            'routes' => [
                'admin.chart-of-accounts.*',
                'admin.journal-entries.*',
                'admin.bank-accounts.*',
                'admin.purchase-invoices.*',
                'admin.sales-invoices.*',
                'admin.customers.*',
            ],
        ],
        'bank_guarantees' => [
            'label' => 'Bank Guarantees',
            'group' => 'finance',
            'routes' => ['admin.bank-guarantees.*'],
        ],
        'advance_payments' => [
            'label' => 'Advance Payments',
            'group' => 'finance',
            'routes' => ['admin.advance-payments.*'],
        ],
        'vehicles' => [
            'label' => 'Vehicles & Vehicle Rent',
            'group' => 'fleet',
            'routes' => ['admin.vehicles.*', 'admin.vehicle-rents.*'],
        ],
        'equipment' => [
            'label' => 'Equipment',
            'group' => 'fleet',
            'routes' => ['admin.equipment.*'],
        ],
        'dashboard' => [
            'label' => 'Admin Dashboard',
            'group' => 'admin',
            'routes' => ['admin.dashboard'],
        ],
    ],

    'actions' => [
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Edit',
        'delete' => 'Delete',
    ],

    /*
    | Default access when a user has no custom permission role assigned.
    | Mirrors the previous hardcoded sidebar/route behaviour.
    */
    'defaults' => [
        'admin' => [
            'dashboard' => ['view', 'create', 'update', 'delete'],
            'users' => ['view', 'create', 'update', 'delete'],
            'projects' => ['view', 'create', 'update', 'delete'],
            'milestones' => ['view', 'create', 'update', 'delete'],
            'materials' => ['view', 'create', 'update', 'delete'],
            'suppliers' => ['view', 'create', 'update', 'delete'],
            'billing' => ['view', 'create', 'update', 'delete'],
            'staff' => ['view', 'create', 'update', 'delete'],
            'positions' => ['view', 'create', 'update', 'delete'],
            'subcontractors' => ['view', 'create', 'update', 'delete'],
            'incomes' => ['view', 'create', 'update', 'delete'],
            'expenses' => ['view', 'create', 'update', 'delete'],
            'finance_setup' => ['view', 'create', 'update', 'delete'],
            'reports' => ['view', 'create', 'update', 'delete'],
            'salary_payments' => ['view', 'create', 'update', 'delete'],
            'vehicles' => ['view', 'create', 'update', 'delete'],
        ],
        'user' => [
            'projects' => ['view', 'create', 'update', 'delete'],
            'materials' => ['view', 'create', 'update', 'delete'],
            'suppliers' => ['view', 'create', 'update', 'delete'],
            'expenses' => ['view', 'create', 'update', 'delete'],
            'finance_setup' => ['view', 'create', 'update', 'delete'],
            'vehicles' => ['view', 'create', 'update', 'delete'],
        ],
        'site_engineer' => [
            'projects' => ['view', 'update'],
        ],
    ],

    /* These modules can never be granted to non-admin users. */
    'admin_locked' => [
        'roles',
    ],
];
