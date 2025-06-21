<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $menu_header = Menu::where('header_id', 1)->get();
        $menu_detail = Menu::whereNotNull('parent_id')->get();
        return view('admin.dashboard', compact('menu_header','menu_detail'));
    }
}
