<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\RoleUser;
use App\Models\RoleUserDetail;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;

class BaseController extends Controller
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function getAccessibleMenus(): array
    {
        $user = User::find(Auth::id());
        // $roles = RoleUserDetail::where('role_id', $user->group_id)->get();

        $menu_detail = collect();

        // foreach ($roles as $role) {
            $role_details = RoleUserDetail::where('role_user_id', $user->role_id)->get();
            foreach ($role_details as $detail) {
                $menu_detail->push($detail->menu);
            }
        // }

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
        // $role = RoleUser::where('group_id', $user->group_id)->first();
        $role_det = RoleUserDetail::where('menu_id', $menu->id)->where('role_user_id', $user->role_id)->first();

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


    protected function generateAutoNumber($model, $column, $prefix = 'TRS')
    {
        $now = Carbon::now();
        $year = $now->format('y'); // 2 digit tahun
        $month = $now->format('m'); // 2 digit bulan

        $basePrefix = "$prefix/$year$month/";

        $lastRecord = $model::where($column, 'like', "$basePrefix%")
            ->orderByDesc($column)
            ->first();

        if ($lastRecord) {
            $lastNumber = (int) Str::afterLast($lastRecord->$column, '/');
            $newNumber = str_pad($lastNumber + 1, 5, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '00001';
        }

        return "$basePrefix$newNumber";
    }
}
