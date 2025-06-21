<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\RoleUser;
use App\Models\RoleUserDetail;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected function getAccessibleMenus(): array
    {
        $user = User::find(Auth::id());
        $roles = RoleUser::where('group_id', $user->group_id)->get();

        $menu_detail = collect();

        foreach ($roles as $role) {
            $role_details = RoleUserDetail::where('role_user_id', $role->id)->get();
            foreach ($role_details as $detail) {
                $menu_detail->push($detail->menu);
            }
        }

        $parentIds = $menu_detail->pluck('parent_id')->unique()->filter();
        $menu_header = Menu::whereIn('id', $parentIds)->get();

        return [
            'menu_detail' => $menu_detail,
            'menu_header' => $menu_header,
        ];
    }
    
    protected function cekAccessMenu(Request $request)
    {

        $user = User::find(Auth::id());
        $url = $request->url;
        $kode = $request->kode;
        
        $menu = Menu::where('slug', $url)->first();
        
        if (!$menu) {
            abort(403, 'Menu tidak ditemukan');
        }
        $role = RoleUser::where('group_id', $user->group_id)->first();
        $role_det = RoleUserDetail::where('menu_id', $menu->id)->where('role_user_id', $role->id)->first();

        if (!$role_det) {
            abort(403, 'Akses role tidak ditemukan');
        }
        
        if ($kode === 'create' && $role_det->can_create !== 1) {
            throw new \Exception('Anda tidak memiliki akses untuk menambahkan data');
        }

        if ($kode === 'view' && $role_det->can_view != 1) {
            abort(403, 'Anda tidak memiliki akses untuk view data');
        }

        if ($kode === 'edit' && $role_det->can_update != 1) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah data');
        }
        
        if ($kode === 'delete' && $role_det->can_delete != 1) {
            abort(403, 'Anda tidak memiliki akses untuk hapus data');
        }
        

        return true;
    }
}
