<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//Request
Route::group(["prefix" => "request", "namespace" => "Request"], function () {
    Route::get("/", "UsersController@login")->name("r.home");
    Route::get("/login", "UsersController@login")->name("r.login");
    Route::post("/login", "UsersController@login");
    Route::get("/logout", "UsersController@logout")->name("r.logout");
    Route::group(["middleware" => "auth"], function () {
        Route::get("/history", "CollectController@history")->name("r.history");
        Route::post("/history", "CollectController@history");
        Route::get("/detail/{id}", "CollectController@detail")->name("r.detail");
        Route::get("/client", "CollectController@client")->name("r.client");
        Route::post("/client", "CollectController@client");
        Route::post("/detail_store", "CollectController@detail_store")->name("r.detail_store");
    });
});

//Admin
Route::group(["prefix" => "admin", "namespace" => "Admin"], function () {
    Route::get("/", "UsersController@login")->name("a.home");
    Route::get("/login", "UsersController@login")->name("a.login");
    Route::post("/login", "UsersController@login");
    Route::get("/logout", "UsersController@logout")->name("a.logout");
    Route::group(["middleware" => "admin"], function () {
        Route::get("/dispatch-calender", "DispatchController@calender")->name("a.dcalender");
        Route::post("/dispatch-calender", "DispatchController@calender");
        Route::get("/dispatch-edit/{id}", "DispatchController@edit")->name("a.dedit");
        Route::post("/dispatch-edit/{id}", "DispatchController@edit");
        Route::get("/dispatch-edit", "DispatchController@edit")->name("a.dedit2");
        Route::post("/dispatch-update", "DispatchController@update")->name("a.dupdate");
        Route::post("/dispatch-movereplace", "DispatchController@updateMoveReplace")->name("a.dmovereplace");
        Route::post("/dispatch-dvalimoverep", "DispatchController@validateMoveReplace");
        Route::get("/dispatch-dvalimoverep", "DispatchController@validateMoveReplace")->name("a.dvalimoverep");

        //テスト時／本番時切り替え用 ($prefix)
        //テスト用
        // $prefix = "test";
        //本番時
        $prefix = "";
        Route::get("/request-setting" . $prefix, "Search" . ucfirst($prefix) . "Controller@setting")->name("a.rsetting");
        Route::post("/request-setting" . $prefix, "Search" . ucfirst($prefix) . "Controller@setting");
        Route::post("/request-get-setting" . $prefix, "Search" . ucfirst($prefix) . "Controller@getUserSetting")->name("a.rgetsetting");
        Route::get("/request-update-lock-user" . $prefix, "Search" . ucfirst($prefix) . "Controller@updateLockUser")->name("a.rlockuser");
        Route::post("/request-update-lock-user" . $prefix, "Search" . ucfirst($prefix) . "Controller@updateLockUser");
        Route::post("/request-search" . $prefix, "Search" . ucfirst($prefix) . "Controller@search");
        Route::get("/request-edit" . $prefix . "/{id}", "Search" . ucfirst($prefix) . "Controller@edit")->name("a.redit");
        Route::post("/request-edit" . $prefix . "/{id}", "Search" . ucfirst($prefix) . "Controller@edit");
        Route::post("/redit_store" . $prefix, "Search" . ucfirst($prefix) . "Controller@redit_store")->name("a.redit_store");
        Route::post("/redit_export" . $prefix, "Search" . ucfirst($prefix) . "Controller@redit_export")->name("a.redit_export");
        Route::post("/redit_ajax" . $prefix, "Search" . ucfirst($prefix) . "Controller@redit_ajax")->name("a.redit_ajax");
        // 物流会社一覧取得
        Route::post("/getListSmst" . $prefix, "Search" . ucfirst($prefix) . "Controller@getListSmst")->name("a.getListSmst");
        // 車両No：運転手取得+車両種別一覧取得
        Route::post("/getListCarTypeDetail" . $prefix, "Search" . ucfirst($prefix) . "Controller@getListCarTypeDetail")->name("a.getListCarTypeDetail");

        Route::get("/request-search" . $prefix, "Search" . ucfirst($prefix) . "Controller@search")->name("a.rsearch");
        Route::get("/request-edit" . $prefix, "Search" . ucfirst($prefix) . "Controller@edit")->name("a.redit2");

        // 依頼作成画面
        Route::get("/request-create" . $prefix . "/{id}", "Search" . ucfirst($prefix) . "Controller@create")->name("a.rcreate");
        Route::post("/request-createstore" . $prefix, "Search" . ucfirst($prefix) . "Controller@createstore")->name("a.rcreatestore");
        Route::post("/getCUSTCDInfo" . $prefix, "Search" . ucfirst($prefix) . "Controller@getCUSTCDInfo")->name("a.getCUSTCDInfo");

        // マスタ関連
        // 台貫マスタ取込
        Route::get("/data-import", "DataController@import")->name("a.dimport");
        Route::post("/data-import", "DataController@import");

        // アカウント管理画面
        Route::get("/ulist", "UsersController@list")->name("a.ulist");
        Route::post("/ulist", "UsersController@list");
        Route::get("/uedit/{id}", "UsersController@edit")->name("a.uedit");
        Route::post("/uedit/{id}", "UsersController@edit");
        // お取引先担当者一覧取得
        Route::post("/getContactHtml", "UsersController@getContactHtml")->name("a.getContactHtml");
        // お取引先担当者情報取得
        Route::post("/getContactValue", "UsersController@getContactValue")->name("a.getContactValue");
        // ユーザーID確認
        Route::post("/checkUserID", "UsersController@checkUserID")->name("a.checkUserID");
        
        // マスタ画面
        $listRoute = [
            "divisions", // 原料区分マスタ画面
            "smst", // 物流費マスタ画面
            "mmst", // 取引先マスタ画面
            "tmst", // 取引先担当者マスタ画面
            "kmst", // 取引先単価マスタ画面
            "fmst", // 商品マスタ画面 
            "vmst", // 運転手マスタ画面 
            "rmst", // 車両マスタ画面 
        ];
        foreach ($listRoute as $r) {
            Route::get("/" . $r, ucfirst($r) . "Controller@index")->name("a." . $r);
            Route::post("/" . $r, ucfirst($r) . "Controller@index");
        }
    });
});
