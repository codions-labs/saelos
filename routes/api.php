<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group([
    'prefix' => '/v1',
    'as' => 'api.'
], function () {
    Route::prefix('auth')->group(base_path('routes/api/auth.php'));

    Route::group(['middleware' => 'auth:api'], function () {
        Route::post('/contacts/{id}/email', 'PersonController@email');

        Route::get('/tasks', function () {
            $people = \App\Contact::where('user_id', Auth::user()->id)
                ->with(\App\Http\Controllers\ContactController::INDEX_WITH)
                ->orderBy('updated_at')
                ->get();

            return \Illuminate\Http\JsonResponse::create($people);
        });

        Route::get('/reports/{id}/export', 'ReportExportController@export');

        Route::get('/contexts/{model}', 'ContextController@show')
            ->where('model', '[a-zA-Z/]+');

        Route::resource('contacts.notes', 'ContactNoteController');
        Route::resource('companies.notes', 'CompanyNoteController');
        Route::resource('opportunities.notes', 'OpportunityNoteController');

        Route::resource('contacts', 'ContactController');
        Route::resource('opportunities', 'OpportunityController');
        Route::resource('companies', 'CompanyController');
        Route::resource('stages', 'StageController');
        Route::resource('teams', 'TeamController');
        Route::resource('activities', 'ActivityController');
        Route::resource('users', 'UserController');
        Route::resource('reports', 'ReportController');
        Route::resource('workflows', 'WorkflowController');
    });
});