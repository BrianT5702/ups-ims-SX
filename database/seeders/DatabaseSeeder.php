<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Clear cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        // Resolve current connection (ups/urs/ucs)
        $connection = config('database.default');
        // Inserting customers (idempotent)
        foreach ([
            [
                'account' => 'CUST001',
                'cust_name' => 'Customer A',
                'address_line1' => '123 Main St',
                'address_line2' => 'Suite 1',
                'address_line3' => null,
                'address_line4' => null,
                'phone_num' => '123-456-7890',
                'fax_num' => null,
                'email' => 'customer_a@example.com',
                'area' => 'North',
                'term' => 'C.O.D',
                'business_registration_no' => 'BRN123456',
                'gst_registration_no' => 'GST123456',
                'currency' => 'RM',
                // pricing_tier removed (per-item in DO)
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account' => 'CUST002',
                'cust_name' => 'Customer B',
                'address_line1' => '456 Elm St',
                'address_line2' => 'Apt 2',
                'address_line3' => null,
                'address_line4' => null,
                'phone_num' => '234-567-8901',
                'fax_num' => null,
                'email' => 'customer_b@example.com',
                'area' => 'South',
                'term' => '30 DAYS',
                'business_registration_no' => 'BRN654321',
                'gst_registration_no' => 'GST654321',
                'currency' => 'RM',
                // pricing_tier removed (per-item in DO)
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account' => 'CUST003',
                'cust_name' => 'Customer C',
                'address_line1' => '789 Oak St',
                'address_line2' => 'Building 3',
                'address_line3' => null,
                'address_line4' => null,
                'phone_num' => '345-678-9012',
                'fax_num' => null,
                'email' => 'customer_c@example.com',
                'area' => 'West',
                'term' => 'CASH',
                'business_registration_no' => 'BRN789123',
                'gst_registration_no' => 'GST789123',
                'currency' => 'RM',
                // pricing_tier removed (per-item in DO)
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ] as $customer) {
            DB::connection($connection)->table('customers')->updateOrInsert(
                ['account' => $customer['account']],
                $customer
            );
        }

        // Inserting suppliers (idempotent)
        foreach ([
            [
                'account' => 'SUP001',
                'sup_name' => 'Supplier A',
                'address_line1' => '321 Pine St',
                'address_line2' => 'Warehouse 1',
                'address_line3' => null,
                'address_line4' => null,
                'phone_num' => '456-789-0123',
                'fax_num' => null,
                'email' => 'supplier_a@example.com',
                'area' => 'East',
                'term' => '30 DAYS',
                'business_registration_no' => 'BRNS001',
                'gst_registration_no' => 'GSTS001',
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account' => 'SUP002',
                'sup_name' => 'Supplier B',
                'address_line1' => '654 Maple St',
                'address_line2' => 'Office 4',
                'address_line3' => null,
                'address_line4' => null,
                'phone_num' => '567-890-1234',
                'fax_num' => null,
                'email' => 'supplier_b@example.com',
                'area' => 'Central',
                'term' => '60 DAYS',
                'business_registration_no' => 'BRNS002',
                'gst_registration_no' => 'GSTS002',
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'account' => 'SUP003',
                'sup_name' => 'Supplier C',
                'address_line1' => '987 Cedar St',
                'address_line2' => 'Lab 2',
                'address_line3' => null,
                'address_line4' => null,
                'phone_num' => '678-901-2345',
                'fax_num' => null,
                'email' => 'supplier_c@example.com',
                'area' => 'West',
                'term' => 'CASH',
                'business_registration_no' => 'BRNS003',
                'gst_registration_no' => 'GSTS003',
                'currency' => 'RM',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ] as $supplier) {
            DB::connection($connection)->table('suppliers')->updateOrInsert(
                ['account' => $supplier['account']],
                $supplier
            );
        }
        
        // Inserting categories
        $categories = [
            'ACCUMULATOR', 'ADAP-KOOL', 'BALL VALVE', 'BLOWER', 'CAPACITOR', 'CAPILLARY',
            'CHECK VALVE', 'COMPRESSOR', 'CONDENG.UNIT', 'CONDENSER', 'COPPER ELBOW',
            'COPPER PIPE', 'COPPER RED.', 'COPPER RED.T', 'COPPER TEE', 'COPPER TUBE', 'COUPLING',
            'DOOR', 'EXP. VALVE', 'FILTER', 'FLARE NUT', 'FLOOR HEATER', 'GAS', 'GLOBE VALVE', 'GOMAX','HAND VALVE',
            'HEATER', 'ICE FLAKER', 'ICE MACHINE', 'MOTOR', 'OIL', 'ORIFICE', 'PRESSURE CTR', 'RECEIVER',
            'S/CURTAIN', 'S/VALVE', 'SAFETY VALVE', 'SCOTSMAN', 'SCROLL COMP', 'SEPARATOR',
            'SIGHT GLASS', 'SOLENOID VAL', 'STRAINER', 'SUPERLON', 'TAIWAN GOODS', 'THERMOSTAT', 'UNION', 'VIBRATION', 'UNDEFINED'
        ];

        foreach ($categories as $category) {
            DB::connection($connection)->table('categories')->updateOrInsert(
                ['cat_name' => $category],
                ['cat_name' => $category]
            );
        }

        // Inserting brands
        $brands = [
            'AC&R', 'AIRMENDER', 'CHINA', 'EMERSON', 'HENRY', 'O & F', 'DANFOSS',
            'CASTEL', 'AIR-TECH', 'CONTARDO', 'CONTARDO - F', 'CONTARDO- CS', 'DOLUYO', 'GUENTNER', 'MULLER',
            'BITZER', 'KULTHORN', 'MANEUROP', 'GUNTNER', 'CHH2', 'CH 1', 'COPELAND',
            'KOREA', 'HUB', 'INTERTECNICA', 'ALCO', 'EMBRACO', 'CALORFLEX',
            'FLEXELEC', 'GRANT ICE', 'ICE SNOW', 'EBM', 'ZIEHL-ABEGG', 'SAGINOMIYA',
            'SCOTSMAN', 'INVOTECH', 'HELDON', 'DIXELL', 'PACKLESS', 'UNDEFINED'
        ];

        foreach ($brands as $brand) {
            DB::connection($connection)->table('brands')->updateOrInsert(
                ['brand_name' => $brand],
                ['brand_name' => $brand]
            );
        }

        DB::connection($connection)->table('warehouses')->updateOrInsert(
            ['warehouse_name' => 'Default Warehouse'],
            [
                'warehouse_name' => 'Default Warehouse',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        
        // Get the ID of the Default Warehouse
        $defaultWarehouseId = DB::connection($connection)->table('warehouses')
            ->where('warehouse_name', 'Default Warehouse')
            ->first()
            ->id;
        
        DB::connection($connection)->table('locations')->updateOrInsert(
            ['location_name' => 'Default Location'],
            [
                'location_name' => 'Default Location',
                'warehouse_id' => $defaultWarehouseId,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Creating admin user idempotently
        $admin = User::on($connection)->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'phone_num' => '0123456789',
                'username' => 'admin',
                'password' => Hash::make('admin12345'),
            ]
        );

        // Creating a regular user idempotently
        $user = User::on($connection)->updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'User',
                'phone_num' => '0321456789',
                'username' => 'user',
                'password' => Hash::make('user12345'),
            ]
        );

        // Define permissions
        $permissions = [
            'Manage User',
            'Manage Brand',
            'Manage Category',
            'Manage Customer',
            'Manage Inventory',
            'Manage Location',
            'Manage Supplier',
            'Manage Restock List',
            'View Transaction Log',
            'Manage DO',
            'Manage PO',
            'Approve PO',
            'Edit Company Profile',
            'View Report',
            'Manage Warehouse',
            'Manage Location',
            'View Batch List',
            'View Consumption Form',
            'Manage Stock Movement (Picking List)'
        ];

        // Create permissions if they don't exist already
        foreach ($permissions as $permission) {
            Permission::on($connection)->firstOrCreate(['name' => $permission]);
        }

        // Get all permission models
        $allPermissions = Permission::on($connection)->get();

        // Define roles and their permissions
        $adminPermissions = $allPermissions->pluck('name')->reject(function ($permission) {
            return $permission === 'Manage Stock Movement (Picking List)';
        })->toArray();
        
        $roles = [
            'Admin' => $adminPermissions,
            'User' => [], // No default permissions; assign per user as needed
            'Salesperson' => [],
        ];

        // Create roles and assign permissions
        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::on($connection)->firstOrCreate(['name' => $roleName]);
            // Sync permissions idempotently
            $role->syncPermissions($rolePermissions);
        }

        // Assign the 'Admin' role to the admin user
        $adminRole = Role::on($connection)->where('name', 'Admin')->first();
        if ($adminRole && !$admin->hasRole('Admin')) {
            $admin->assignRole($adminRole);
        }

        // Assign the 'User' role to the user
        $userRole = Role::on($connection)->where('name', 'User')->first();
        if ($userRole && !$user->hasRole('User')) {
            $user->assignRole($userRole);
        }

        // Create three salesperson users
        $salespersonRole = Role::on($connection)->where('name', 'Salesperson')->first();
        $salesman1 = User::on($connection)->updateOrCreate([
            'email' => 'salesman1@example.com'
        ], [
            'name' => 'Salesman 1',
            'phone_num' => '0112233441',
            'username' => 'salesman1',
            'password' => Hash::make('salesman12345'),
        ]);
        $salesman2 = User::on($connection)->updateOrCreate([
            'email' => 'salesman2@example.com'
        ], [
            'name' => 'Salesman 2',
            'phone_num' => '0112233442',
            'username' => 'salesman2',
            'password' => Hash::make('salesman12345'),
        ]);
        $salesman3 = User::on($connection)->updateOrCreate([
            'email' => 'salesman3@example.com'
        ], [
            'name' => 'Salesman 3',
            'phone_num' => '0112233443',
            'username' => 'salesman3',
            'password' => Hash::make('salesman12345'),
        ]);
        if ($salespersonRole) {
            if (!$salesman1->hasRole($salespersonRole)) $salesman1->assignRole($salespersonRole);
            if (!$salesman2->hasRole($salespersonRole)) $salesman2->assignRole($salespersonRole);
            if (!$salesman3->hasRole($salespersonRole)) $salesman3->assignRole($salespersonRole);
        }

        // Assign default salesmen to seeded customers
        DB::connection($connection)->table('customers')->where('account', 'CUST001')->update(['salesman_id' => $salesman1->id]);
        DB::connection($connection)->table('customers')->where('account', 'CUST002')->update(['salesman_id' => $salesman2->id]);
        DB::connection($connection)->table('customers')->where('account', 'CUST003')->update(['salesman_id' => $salesman3->id]);

        //Seed data for company profile (varies per DB)
        $conn = $connection;
        $company = [
            'ups' => [
                'company_name' => 'UNITED PANEL-SYSTEM (M) SDN. BHD.',
                'company_no' => '772009-A',
            ],
            'urs' => [
                'company_name' => 'UNITED REFRIGERATION-SYSTEM (M) SDN. BHD.',
                'company_no' => '772011-D',
            ],
            'ucs' => [
                'company_name' => 'UNITED COLD-SYSTEM (M) SDN. BHD.',
                'company_no' => '748674-K',
            ],
        ][$conn] ?? [
            'company_name' => 'UNITED REFRIGERATION SYSTEM (M) SDN BHD',
            'company_no' => '772011-D',
        ];

        DB::connection($connection)->table('company_profiles')->updateOrInsert([
            'company_name'  => $company['company_name'],
            'company_no' => $company['company_no'],
        ], [
            'company_name'  => $company['company_name'],
            'company_no' => $company['company_no'],
            'gst_no' => '000537624576',
            'address_line1' => 'PTD 124299, JALAN KEMPAS LAMA',
            'address_line2' => 'KAMPUNG SEELONG JAYA',
            'address_line3' => 'SKUDAI, 81300 JOHOR BAHRU, JOHOR',
            'address_line4' => '',
            'phone_num1'     => '+607 5951588',
            'phone_num2'     => '+607 5951288',
            'fax_num'       => '+607 5951177 / 5951122',
            'email'         => 'united@ur.com.my',
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        DB::connection($connection)->table('purchase_orders')->updateOrInsert(
            ['po_num' => 'PO0000000000'],
            [
                'ref_num' => 'PO0000000000',
                'po_num' => 'PO0000000000',
                'sup_id' => 1,
                'user_id' => 1,
                'date' => now()->subDays(10),
                'remark' => 'First Purchase Order',
                'status' => 'Pending',
                'final_total_price' => 1000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
