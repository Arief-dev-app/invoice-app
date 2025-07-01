<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    public function data(){
        return [
            'url' => '/customer',
            'title' => 'Customer',
            'kode' => 'MENU01-2'
        ];
    }
    public function index(Request $request)
    {
        $search   = $request->query('search');
 
        


        $menus = $this->getAccessibleMenus();
        $default = $this->data();
        
        return view('admin.dashboard', array_merge($default, [
            'search'       => $search, 
            'menu_header'  => $menus['menu_header'],
            'menu_detail'  => $menus['menu_detail'],
        ]));
    }
}
