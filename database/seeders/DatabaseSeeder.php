<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Menu;
use App\Models\GroupUser;
use App\Models\RoleUser;
use App\Models\RoleUserDetail;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Penjualan;
use App\Models\PenjualanDetail;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // $groups = [
        //     [
        //         'name' => 'Admin',
        //         'description' => 'Admin',
        //         'flag_active' => true,
        //     ],
        //     [
        //         'name' => 'Kasir',
        //         'description' => 'Kasir',
        //         'flag_active' => true,
        //     ],
        // ];
    
        // foreach ($groups as $group) {
        //     GroupUser::create($group);
        // }

        $menus = [
            [
                'name' => 'Master',
                'seq' => '1',
                'code' => 'MENU01',
                'slug' => '#',
                'header_id' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Produk',
                'seq' => '11',
                'code' => 'MENU01-1',
                'slug' => '/product',
                'header_id' => false,
                'parent_id' => 1,
            ],
            [
                'name' => 'Customer',
                'seq' => '12',
                'code' => 'MENU01-2',
                'slug' => '/customer',
                'header_id' => false,
                'parent_id' => 1,
            ],
            // [
            //     'name' => 'Supplier',
            //     'seq' => '13',
            //     'code' => 'MENU01-3',
            //     'slug' => '/supplier',
            //     'header_id' => false,
            //     'parent_id' => 1,
            // ],
            // [
            //     'name' => 'Jasa',
            //     'seq' => '14',
            //     'code' => 'MENU01-4',
            //     'slug' => '/jasa',
            //     'header_id' => false,
            //     'parent_id' => 1,
            // ],
            // [
            //     'name' => 'Kategori',
            //     'seq' => '15',
            //     'code' => 'MENU01-5',
            //     'slug' => '/kategori',
            //     'header_id' => false,
            //     'parent_id' => 1,
            // ],
            [
                'name' => 'Transaksi',
                'seq' => '2',
                'code' => 'MENU02',
                'slug' => '#',
                'header_id' => true,
                'parent_id' => null,
            ],
            // [
            //     'name' => 'Purchase-Order',
            //     'seq' => '21',
            //     'code' => 'MENU02-1',
            //     'slug' => '/purchase-order',
            //     'header_id' => false,
            //     'parent_id' => 7,
            // ],
            // [
            //     'name' => 'Transaksi Pembelian',
            //     'seq' => '22',
            //     'code' => 'MENU02-2',
            //     'slug' => '/transaksi-pembelian',
            //     'header_id' => false,
            //     'parent_id' => 7,
            // ],
            // [
            //     'name' => 'Transaksi Retur Pembelian',
            //     'seq' => '23',
            //     'code' => 'MENU02-3',
            //     'slug' => '/retur-pembelian',
            //     'header_id' => false,
            //     'parent_id' => 7,
            // ],
            [
                'name' => 'Transaksi Penjualan',
                'seq' => '21',
                'code' => 'MENU02-4',
                'slug' => '/transaksi-penjualan',
                'header_id' => false,
                'parent_id' => 4,
            ],
            // [
            //     'name' => 'Transaksi Retur Penjualan',
            //     'seq' => '25',
            //     'code' => 'MENU02-5',
            //     'slug' => '/transaksi-retur-penjualan',
            //     'header_id' => false,
            //     'parent_id' => 7,
            // ],
            [
                'name' => 'Laporan',
                'seq' => '3',
                'code' => 'MENU03',
                'slug' => '#',
                'header_id' => true,
                'parent_id' => null,
            ],
            [
                'name' => 'Laporan penjualan',
                'seq' => '31',
                'code' => 'MENU03-1',
                'slug' => '/laporan-penjualan',
                'header_id' => false,
                'parent_id' => 6,
            ],
        ];
    
        foreach ($menus as $menu) {
            Menu::create($menu);
        }

        $allMenus = Menu::whereNotNull('parent_id')->get();

        
        
        $roles = [
            ['name' => 'Admin'],
            ['name' => 'Kasir'],
        ];

        foreach ($roles as $roleUser) {
            $role = RoleUser::create($roleUser);
            
            foreach ($allMenus as $menu) {
                RoleUserDetail::create([
                    'role_user_id' => $role->id,
                    'menu_id' => $menu->id,
                    'can_view' => 1,
                    'can_create' => 1,
                    'can_update' => 1,
                    'can_delete' => 1,
                ]);
            }
        } 


        $users = [
            [
                'name' => 'Arief',
                'email' => 'ariieff.dev@gmail.com',
                'password' => Hash::make('gkQpA9qbRsaX!2N'),
                'is_admin' => 1,
                'role_id' => 1,
            ],
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin123'),
                'is_admin' => 1,
                'role_id' => 1,
            ],
            [
                'name' => 'Kasir',
                'email' => 'kasir@gmail.com',
                'password' => Hash::make('kasir123'),
                'is_admin' => 0,
                'role_id' => 2,
            ],
        ];
    
        foreach ($users as $user) {
            User::create($user);
        }        

        // Tambahkan produk "Produk A" sampai "Produk J"
        foreach (range('1', '150') as $angka) {
            $harga_beli = rand(10, 20) * 1000; 
            $harga_jual = $harga_beli + (rand(3, 40) * 1000);  

            Product::create([
                'nama' => 'Produk ' . $angka,
                'harga_beli' => $harga_beli,
                'harga_jual' => $harga_jual,
                'stock' => 100,
                'user_id' => 1,
            ]);
        }

        $customers = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@example.com',
                'phone' => '081234567890',
                'address' => 'Jl. Merdeka No.1, Jakarta',
                'user_id' => 1,
            ],
            [
                'name' => 'Siti Aminah',
                'email' => 'siti@example.com',
                'phone' => '081298765432',
                'address' => 'Jl. Kenanga No.23, Bandung',
                'user_id' => 1,
            ],
            [
                'name' => 'Agus Salim',
                'email' => 'agus@example.com',
                'phone' => '081345678901',
                'address' => 'Jl. Pahlawan No.5, Surabaya',
                'user_id' => 1,
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@example.com',
                'phone' => '082112345678',
                'address' => 'Jl. Melati No.8, Yogyakarta',
                'user_id' => 1,
            ],
            [
                'name' => 'Andi Wijaya',
                'email' => 'andi@example.com',
                'phone' => '081276543210',
                'address' => 'Jl. Cemara No.10, Medan',
                'user_id' => 1,
            ],
        ];

        foreach ($customers as $customer) {
            Customer::create($customer);
        }
        
        $suppliers = [
            [
                'name' => 'Rina Kusuma',
                'email' => 'rina.kusuma@example.com',
                'phone' => '081222334455',
                'address' => 'Jl. Anggrek No.12, Semarang',
                'user_id' => 1,
            ],
            [
                'name' => 'Fajar Prasetyo',
                'email' => 'fajar.prasetyo@example.com',
                'phone' => '081333445566',
                'address' => 'Jl. Mawar No.9, Surakarta',
                'user_id' => 1,
            ],
            [
                'name' => 'Nina Wulandari',
                'email' => 'nina.wulandari@example.com',
                'phone' => '082144556677',
                'address' => 'Jl. Cendana No.14, Malang',
                'user_id' => 1,
            ],
            [
                'name' => 'Dedi Gunawan',
                'email' => 'dedi.gunawan@example.com',
                'phone' => '081299887766',
                'address' => 'Jl. Jati No.3, Balikpapan',
                'user_id' => 1,
            ],
            [
                'name' => 'Lia Apriyani',
                'email' => 'lia.apriyani@example.com',
                'phone' => '081355667788',
                'address' => 'Jl. Pinang No.18, Palembang',
                'user_id' => 1,
            ],
        ];
        

        foreach ($suppliers as $supplier) {
            Supplier::create($supplier);
        }

        $now = Carbon::now();
        $yearMonth = $now->format('y') . $now->format('m');
        $today = $now->format('Y-m-d');

        for ($i = 1; $i <= 10; $i++) {
            $purchaseNo = 'PO/' . $yearMonth . '/' . str_pad($i, 5, '0', STR_PAD_LEFT);

            $poId = PurchaseOrder::insertGetId([
                'purchase_no'      => $purchaseNo,
                'transaction_date' => $today,
                'supplier_id'      => 1,
                'po_status'        => rand(1, 3),
                'total'            => 0,
                'user_id'          => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $total = 0;
            for ($j = 1; $j <= 2; $j++) {
                $qty = rand(10, 50);
                $total += $qty;

                PurchaseOrderDetail::insert([
                    'po_id'       => $poId,
                    'prd_id'      => $j,
                    'qty'         => $qty,
                    'user_id'     => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            PurchaseOrder::where('id', $poId)->update([
                'total' => $total
            ]);
        }
        
        for ($i = 1; $i <= 10; $i++) {
            $purchaseNo = 'PRC/' . $yearMonth . '/' . str_pad($i, 5, '0', STR_PAD_LEFT);
            $po = rand(1, 10);

            $poId = Purchase::insertGetId([
                'trans_no'      => $purchaseNo,
                'transaction_date' => $today,
                'po_id'            => $po,
                'supplier_id'      => 1,
                'trans_status'     => rand(1, 3),
                'total'            => 0,
                'user_id'          => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $total = 0;
            for ($j = 1; $j <= 2; $j++) {
                $qty = rand(10, 50);
                $harga = rand(10000, 50000);
                $total += $qty;

                PurchaseDetail::insert([
                    'trans_id'       => $poId,
                    'prd_id'      => $j,
                    'harga'       => $harga,
                    'qty'         => $qty,
                    'user_id'     => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            Purchase::where('id', $poId)->update([
                'total' => $total
            ]);
        }
        
        for ($i = 1; $i <= 10; $i++) {
            $purchaseNo = 'TRS/' . $yearMonth . '/' . str_pad($i, 5, '0', STR_PAD_LEFT);
            $po = rand(1, 10);

            $poId = Penjualan::insertGetId([
                'trans_no'      => $purchaseNo,
                'transaction_date' => $today,
                'customer_id'      => 1,
                'trans_status'     => rand(1, 3),
                'total'            => 0,
                'user_id'          => 1,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);

            $total = 0;
            for ($j = 1; $j <= 2; $j++) {
                $qty = rand(10, 50);
                $harga = rand(10000, 50000);
                $total += $harga;

                PenjualanDetail::insert([
                    'trans_id'    => $poId,
                    'prd_id'      => $j,
                    'harga'  => $harga,
                    'qty'         => $qty,
                    'user_id'     => 1,
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }

            Penjualan::where('id', $poId)->update([
                'total' => $total
            ]);
        }
       
    }
}
