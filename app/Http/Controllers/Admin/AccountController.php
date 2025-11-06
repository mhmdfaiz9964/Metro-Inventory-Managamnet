<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    // Account Menus
    public function accounts()
    {
        return view('admin.menus.account');
    }

    // Product & BOM Menus
    public function products()
    {
        return view('admin.menus.products');
    }

    // User & Supplier Management
    public function users()
    {
        return view('admin.menus.users');
    }

    //Inventory Management
    public function inventory()
    {
        return view('admin.menus.inventory');
    }

    //Purchase and Sales
    public function purchase()
    {
        return view('admin.menus.purchase');
    }

    public function sales()
    {
        return view('admin.menus.sales');
    }

    public function bom()
    {
        return view('admin.menus.bom');
    }

    public function customer()
    {
        return view('admin.menus.customers');
    }

    public function supplier()
    {
        return view('admin.menus.suppliers');
    }
    public function tog()
    {
        return view('admin.menus.tog');
    }
    //Reports
    public function reports()
    {
        return view('admin.menus.reports');
    }
    public function brands()
    {
        return view('admin.menus.brands');
    }
}
