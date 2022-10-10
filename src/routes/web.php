<?php
Route::group(['namespace'=>'Yarm\Adminkeywords\Http\Controllers','prefix'=> strtolower(config('yarm.sys_name')),'middleware'=>['web']], function (){
    Route::get('/keywords', 'KeywordAdminController@index')
        ->name('keywords');
    Route::get('/keywords/edit/{id}', 'KeywordAdminController@edit')
        ->name('edit_keyword');
    Route::put('/keywords/update', 'KeywordAdminController@update')
        ->name('update_keyword');
    Route::delete('/keywords/delete/{id}', 'KeywordAdminController@destroy')
        ->name('delete_keyword');
    Route::get('/keywords/{id}/editAjax', 'KeywordAdminController@editAjax')
        ->name('keywords.edit-ajax');
    Route::put('/keywords/updateAjax/{id}', 'KeywordAdminController@updateAjax')
        ->name('keywords.update-ajax');
    Route::get('/keywordFetch_data', 'KeywordAdminController@keywordFetch_Data')
        ->name('keywordFetch-ajax');
});
