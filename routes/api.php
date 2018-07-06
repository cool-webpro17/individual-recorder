<?php

Route::group([
        'prefix' => '/v1',
        'namespace' => 'Api\V1',
        'middleware' => ['cors'],
        'as' => 'api.'
    ], function () {
    Route::resource('companies', 'CompaniesController', ['except' => ['create', 'edit']]);
});

Route::group([
        'prefix' => '/v1',
        'namespace' => 'Api\V1',
        'middleware' => ['cors'],
        'as' => 'api.'
    ], function () {
    Route::post('log',                          ['as' => 'log',                         'uses' => 'HomeController@log']);
    Route::get('activity_log',                  ['as' => 'activity_log',                'uses' => 'HomeController@activity_log']);


    Route::post('meta-log',                ['as' => 'meta_log',                    'uses' => 'HomeController@saveMetaLog']);
    Route::get('meta-log/{characterId}',    ['as' => 'activity_log',                'uses' => 'HomeController@getMetaLog']);
    Route::group([
        'prefix' => '/character',
        'as' => 'character.'
    ], function () {
        Route::get('all/{userId}',              ['as' => 'all',                         'uses' => 'HomeController@all']);
        Route::get('name/{userId}',             ['as' => 'name',                        'uses' => 'HomeController@getName']);
        Route::get('history/{characterId}',     ['as' => 'history',                     'uses' => 'HomeController@history']);
        Route::get('usage/{characterId}',       ['as' => 'usage',                       'uses' => 'HomeController@usage']);
        Route::get('/{id}',                     ['as' => 'get-character',               'uses' => 'HomeController@getCharacter']);
        Route::post('create',                   ['as' => 'create',                      'uses' => 'HomeController@store']);
        Route::post('add-header/{userId}',      ['as' => 'add-header',                  'uses' => 'HomeController@addHeader']);
        Route::post('update',                   ['as' => 'update',                      'uses' => 'HomeController@update']);
        Route::post('delete/{userId}',          ['as' => 'delete',                      'uses' => 'HomeController@delete']);

    });
});
