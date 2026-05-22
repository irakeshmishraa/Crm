<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Department;
use App\Models\Lead;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Setting;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'is_system' => true],
            ['name' => 'Admin', 'slug' => 'admin', 'is_system' => true],
            ['name' => 'Manager', 'slug' => 'manager', 'is_system' => true],
            ['name' => 'Sales Executive', 'slug' => 'sales-executive', 'is_system' => true],
            ['name' => 'Telecaller', 'slug' => 'telecaller', 'is_system' => true],
            ['name' => 'Marketing Executive', 'slug' => 'marketing-executive', 'is_system' => true],
            ['name' => 'Support Executive', 'slug' => 'support-executive', 'is_system' => true],
            ['name' => 'Accountant', 'slug' => 'accountant', 'is_system' => true],
        ];
        foreach ($roles as $r) Role::create($r);

        $modules = ['leads','followups','clients','quotations','products','tasks','reports','users','settings','campaigns'];
        $actions = ['create','view','edit','delete','export','import','assign','manage'];
        foreach ($modules as $mod) foreach ($actions as $act) Permission::create(['name' => ucfirst($act).' '.ucfirst($mod), 'slug' => "{$mod}.{$act}", 'module' => $mod]);

        $superAdmin = Role::where('slug', 'super-admin')->first();
        $superAdmin->permissions()->sync(Permission::pluck('id'));


        foreach (['Sales', 'Marketing', 'Support', 'Operations', 'Finance'] as $d) Department::create(['name' => $d]);

        $admin = User::create(['name' => 'Admin User', 'email' => 'admin@smartleadcrm.com', 'username' => 'admin', 'password' => Hash::make('password'), 'email_verified_at' => now(), 'department_id' => 1, 'designation' => 'Administrator']);
        $admin->roles()->attach($superAdmin->id);

        $salesRole = Role::where('slug', 'sales-executive')->first();
        foreach ([['name' => 'Rahul Sharma', 'email' => 'rahul@smartleadcrm.com'], ['name' => 'Priya Patel', 'email' => 'priya@smartleadcrm.com'], ['name' => 'Amit Kumar', 'email' => 'amit@smartleadcrm.com']] as $u) {
            $user = User::create(array_merge($u, ['password' => Hash::make('password'), 'email_verified_at' => now(), 'department_id' => 1, 'username' => strtolower(explode(' ', $u['name'])[0])]));
            $user->roles()->attach($salesRole->id);
        }

        foreach ([['name'=>'Software Services','slug'=>'software'], ['name'=>'Web Development','slug'=>'web-dev'], ['name'=>'Digital Marketing','slug'=>'digital-marketing'], ['name'=>'Consulting','slug'=>'consulting']] as $c) ProductCategory::create($c);
        foreach ([['name'=>'Website Design','selling_price'=>50000,'type'=>'service','category_id'=>2,'unit'=>'project'], ['name'=>'Mobile App','selling_price'=>150000,'type'=>'service','category_id'=>1,'unit'=>'project'], ['name'=>'SEO Monthly','selling_price'=>15000,'type'=>'service','category_id'=>3,'unit'=>'month'], ['name'=>'CRM License','selling_price'=>25000,'type'=>'product','category_id'=>1,'unit'=>'license']] as $p) Product::create(array_merge($p, ['cost_price'=>$p['selling_price']*0.6, 'tax_percentage'=>18]));

        $userIds = User::pluck('id')->toArray();
        foreach ([['name'=>'Vikram Singh','email'=>'vikram@techcorp.in','phone'=>'9876543210','company_name'=>'TechCorp','source'=>'google_ads','status'=>'new','budget'=>100000], ['name'=>'Sneha Gupta','email'=>'sneha@startup.io','phone'=>'9876543211','company_name'=>'StartupIO','source'=>'referral','status'=>'interested','budget'=>200000], ['name'=>'Rajesh Kumar','email'=>'rajesh@globaltech.com','phone'=>'9876543212','company_name'=>'GlobalTech','source'=>'website','status'=>'proposal_sent','budget'=>500000], ['name'=>'Anita Desai','email'=>'anita@media.in','phone'=>'9876543213','company_name'=>'Media Solutions','source'=>'linkedin','status'=>'follow_up','budget'=>75000], ['name'=>'Mohammed Ali','email'=>'ali@build.com','phone'=>'9876543214','company_name'=>'BuildRight','source'=>'cold_call','status'=>'negotiation','budget'=>300000]] as $l) Lead::create(array_merge($l, ['assigned_to'=>$userIds[array_rand($userIds)], 'created_by'=>$admin->id, 'priority'=>['low','medium','high','urgent'][array_rand([0,1,2,3])], 'score'=>rand(20,90), 'city'=>'Mumbai', 'state'=>'Maharashtra', 'country'=>'India', 'deal_value'=>$l['budget'], 'win_probability'=>rand(10,80)]));

        foreach ([['key'=>'company_name','value'=>'SmartLead CRM Pro'], ['key'=>'company_email','value'=>'info@smartleadcrm.com'], ['key'=>'company_phone','value'=>'+91 9876543210'], ['key'=>'currency','value'=>'INR'], ['key'=>'timezone','value'=>'Asia/Kolkata']] as $s) Setting::create(array_merge($s, ['group'=>'general', 'type'=>'string']));
    }
}
