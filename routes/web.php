<?php
Route::get('/', 'Newsfeed@show');
Route::get('tambahlangsung', 'Newsfeed@xenforoinsert');
Route::get('beberes', 'Visitor@beberes');
Route::get('login', 'Visitor@login');
Route::get('upgrademember', 'Visitor@upgrademember');
Route::post('upgrademember/pembayaran', 'Visitor@viewpembayaran');
Route::get('yjb', 'Yjb@index');
Route::get('lang/{parameter}', 'GlobalFunction@getLang');
Route::get('test','Visitor@test');
Route::get('yangbaru','Visitor@komukuinfo');