<?php

use App\Helpers\LogHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\JobController;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BahanController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ProjekController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\StokRndController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DataFeedController;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\BahanJadiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\ProjekRndController;
use App\Http\Controllers\BahanReturController;
use App\Http\Controllers\BahanRusakController;
use App\Http\Controllers\JenisBahanController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\BahanKeluarController;
use App\Http\Controllers\JobPositionController;
use App\Http\Controllers\LogActivityController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\StokProduksiController;
use App\Http\Controllers\ProdukProduksiController;
use App\Http\Controllers\BahanSetengahjadiController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login');
});
Route::get('/register', function () {
    abort(404);
});

Route::get('/forgot-password', function () {
    abort(404);
});

Route::post('/login', [AuthController::class, 'login']);
Route::get('/notif-transaksi', [PurchaseController::class, 'notifTransaksi']);




Route::middleware(['auth:sanctum', 'verified', 'isAdmin'])->group(function () {

    Route::resource('log-activities', LogActivityController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('roles', RoleController::class);
    Route::get('roles/{roleId}/give-permissions', [RoleController::class, 'addPermissionToRole'])->name('roles.add-permissions');
    Route::put('roles/{roleId}/give-permissions', [RoleController::class, 'givePermissionToRole'])->name('roles.give-permissions');

    Route::resource('users', UserController::class);
    // Route::get('users/{userId}/delete', [UserController::class, 'destroy']);
    // Route for the getting the data feed
    Route::get('/json-data-feed', [DataFeedController::class, 'getDataFeed'])->name('json_data_feed');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Route::resource('bahan', BahanController::class);

    Route::get('/bahan/edit-multiple', [BahanController::class, 'editMultiple'])->name('bahan.editmultiple');
    Route::put('/bahan/update-multiple', [BahanController::class, 'updateMultiple'])->name('bahan.update.multiple');

    Route::get('/bahan', [BahanController::class, 'index'])->name('bahan.index');
    Route::get('/bahan/create', [BahanController::class, 'create'])->name('bahan.create');
    Route::post('/bahan/store', [BahanController::class, 'store'])->name('bahan.store');
    Route::get('/bahan/{id}/edit', [BahanController::class, 'edit'])->name('bahan.edit');
    Route::put('/bahan/{id}', [BahanController::class, 'update'])->name('bahan.update');
    Route::delete('/bahan/{id}', [BahanController::class, 'destroy'])->name('bahan.destroy');
    Route::get('bahan-export', [BahanController::class, 'export'])->name('bahan.export');






    Route::resource('supplier', SupplierController::class);
    Route::get('supplier-export', [SupplierController::class, 'export'])->name('supplier.export');
    Route::resource('jenis-bahan', JenisBahanController::class);
    Route::get('jenisbahan-expot', [JenisBahanController::class, 'export'])->name('jenisbahan-expot.export');
    Route::resource('unit', UnitController::class);
    Route::get('unit-export', [UnitController::class, 'export'])->name('unit.export');
    Route::resource('purchases', PurchaseController::class);
    Route::get('purchases-export', [PurchaseController::class, 'export'])->name('purchases-export.export');

    Route::resource('organization', OrganizationController::class);
    Route::resource('job-position', JobPositionController::class);


    Route::get('/bahan-keluars/pdf/{id}', [BahanKeluarController::class, 'downloadPdf'])->name('bahan-keluars.downloadPdf');

    Route::resource('bahan-keluars', BahanKeluarController::class);
    Route::put('bahan-keluars/{id}/updatepengambilan', [BahanKeluarController::class, 'updatepengambilan'])->name('bahan-keluars.updatepengambilan');



    Route::resource('pengajuans', PengajuanController::class);
    Route::put('pengajuans/{pengajuan}/selesai', [PengajuanController::class, 'updateStatus'])->name('pengajuans.updateStatus');

    Route::resource('produksis', ProduksiController::class);
    Route::put('produksis/{produksi}/selesai', [ProduksiController::class, 'updateStatus'])->name('produksis.updateStatus');
    Route::get('produksis-export/{produksi_id}', [ProduksiController::class, 'export'])->name('produksis.export');

    Route::resource('projeks', ProjekController::class);
    Route::put('projeks/{projek}/selesai', [ProjekController::class, 'updateStatus'])->name('projeks.updateStatus');
    Route::get('projeks-export/{projek_id}', [ProjekController::class, 'export'])->name('projeks.export');

    Route::resource('bahan-rusaks', BahanRusakController::class);
    Route::resource('bahan-setengahjadis', BahanSetengahjadiController::class);
    Route::resource('bahan-jadis', BahanJadiController::class);
    Route::resource('produk-produksis', ProdukProduksiController::class);
    Route::resource('bahan-returs', BahanReturController::class);

    Route::resource('projek-rnd', ProjekRndController::class);
    Route::put('projek-rnd/{projek}/selesai', [ProjekRndController::class, 'updateStatus'])->name('projek-rnd.updateStatus');
    Route::get('projek-rnd-export/{projek_rnd_id}', [ProjekRndController::class, 'export'])->name('projek-rnd.export');



    Route::get('/dashboard/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    Route::get('/dashboard/fintech', [DashboardController::class, 'fintech'])->name('fintech');

    Route::get('/job/job-post', function () {
        return view('pages/job/job-post');
    })->name('job-post');
    Route::get('/job/company-profile', function () {
        return view('pages/job/company-profile');
    })->name('company-profile');
    Route::get('/messages', function () {
        return view('pages/messages');
    })->name('messages');
    Route::get('/tasks/kanban', function () {
        return view('pages/tasks/tasks-kanban');
    })->name('tasks-kanban');
    Route::get('/tasks/list', function () {
        return view('pages/tasks/tasks-list');
    })->name('tasks-list');
    Route::get('/inbox', function () {
        return view('pages/inbox');
    })->name('inbox');
    Route::get('/calendar', function () {
        return view('pages/calendar');
    })->name('calendar');
    Route::get('/settings/account', function () {
        return view('pages/settings/account');
    })->name('account');
    Route::get('/settings/notifications', function () {
        return view('pages/settings/notifications');
    })->name('notifications');
    Route::get('/settings/apps', function () {
        return view('pages/settings/apps');
    })->name('apps');
    Route::get('/settings/plans', function () {
        return view('pages/settings/plans');
    })->name('plans');
    Route::get('/settings/billing', function () {
        return view('pages/settings/billing');
    })->name('billing');
    Route::get('/settings/feedback', function () {
        return view('pages/settings/feedback');
    })->name('feedback');
    Route::get('/utility/changelog', function () {
        return view('pages/utility/changelog');
    })->name('changelog');
    Route::get('/utility/roadmap', function () {
        return view('pages/utility/roadmap');
    })->name('roadmap');
    Route::get('/utility/faqs', function () {
        return view('pages/utility/faqs');
    })->name('faqs');
    Route::get('/utility/empty-state', function () {
        return view('pages/utility/empty-state');
    })->name('empty-state');
    Route::get('/utility/404', function () {
        return view('pages/utility/404');
    })->name('404');
    Route::get('/utility/knowledge-base', function () {
        return view('pages/utility/knowledge-base');
    })->name('knowledge-base');
    Route::get('/onboarding-01', function () {
        return view('pages/onboarding-01');
    })->name('onboarding-01');
    Route::get('/onboarding-02', function () {
        return view('pages/onboarding-02');
    })->name('onboarding-02');
    Route::get('/onboarding-03', function () {
        return view('pages/onboarding-03');
    })->name('onboarding-03');
    Route::get('/onboarding-04', function () {
        return view('pages/onboarding-04');
    })->name('onboarding-04');
    Route::get('/component/button', function () {
        return view('pages/component/button-page');
    })->name('button-page');
    Route::get('/component/form', function () {
        return view('pages/component/form-page');
    })->name('form-page');
    Route::get('/component/dropdown', function () {
        return view('pages/component/dropdown-page');
    })->name('dropdown-page');
    Route::get('/component/alert', function () {
        return view('pages/component/alert-page');
    })->name('alert-page');
    Route::get('/component/modal', function () {
        return view('pages/component/modal-page');
    })->name('modal-page');
    Route::get('/component/pagination', function () {
        return view('pages/component/pagination-page');
    })->name('pagination-page');
    Route::get('/component/tabs', function () {
        return view('pages/component/tabs-page');
    })->name('tabs-page');
    Route::get('/component/breadcrumb', function () {
        return view('pages/component/breadcrumb-page');
    })->name('breadcrumb-page');
    Route::get('/component/badge', function () {
        return view('pages/component/badge-page');
    })->name('badge-page');
    Route::get('/component/avatar', function () {
        return view('pages/component/avatar-page');
    })->name('avatar-page');
    Route::get('/component/tooltip', function () {
        return view('pages/component/tooltip-page');
    })->name('tooltip-page');
    Route::get('/component/accordion', function () {
        return view('pages/component/accordion-page');
    })->name('accordion-page');
    Route::get('/component/icons', function () {
        return view('pages/component/icons-page');
    })->name('icons-page');
    Route::fallback(function() {
        return view('pages/utility/404');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});


