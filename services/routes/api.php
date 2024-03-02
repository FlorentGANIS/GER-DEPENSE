<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EnvelopeController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FixedChargeController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\SavingController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\VariableChargeController;
use App\Models\Expense;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Code verification to make school inscription request
Route::post('code/confirmation', [AuthController::class, 'getCodeOfVerification']);
Route::post('new/code/confirmation', [AuthController::class, 'getNewCodeOfVerification']);
Route::post('verify/code', [AuthController::class, 'codeVerification']);


Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function ($router) {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);    
});

//Incomes
Route::group(['prefix' => 'income'], function(){
    Route::get('list', [IncomeController::class, 'list']);
    Route::get('all-incomes', [IncomeController::class, 'allIncomes']);
    Route::post('create', [IncomeController::class, 'create']);
    Route::post('delete', [IncomeController::class, 'delete']);
    Route::post('change-status', [IncomeController::class, 'changeStatus']);
});

//Categories
Route::group(['prefix' => 'category'], function(){
    Route::get('all', [CategoryController::class, 'allCategories']);
    Route::get('without-fixed', [CategoryController::class, 'categoriesWithoutFixed']);
    Route::get('fixed/list', [CategoryController::class, 'fixedChargeList']);
    Route::get('variable/list', [CategoryController::class, 'variableChargeList']);
    Route::get('saving/list', [CategoryController::class, 'savingList']);
    Route::post('create', [CategoryController::class, 'create']);
    Route::post('delete', [CategoryController::class, 'delete']);
    Route::post('change-status', [CategoryController::class, 'changeStatus']);

});

//Budget
Route::group(['prefix' => 'budget'], function(){
    Route::get('all-infos-app', [BudgetController::class, 'countAppEntities']);
    Route::get('actif-budget-detail', [BudgetController::class, 'getActifBudgetDetail']);
    Route::post('list', [BudgetController::class, 'list']);
    // Route::get('distinct-charge', [BudgetController::class, 'listDistinctCharges']);
    Route::get('list/years', [BudgetController::class, 'getDistinctYears']);
    Route::get('other/years', [BudgetController::class, 'getOthersYears']);
    Route::post('create', [BudgetController::class, 'create']);
    Route::post('show', [BudgetController::class, 'show']);
    Route::post('edit', [BudgetController::class, 'edit']);
    Route::post('detail-before-share', [BudgetController::class, 'detailToShareOut']);
    Route::post('share-out', [BudgetController::class, 'shareOut']);
    Route::post('delete', [BudgetController::class, 'delete']);
    Route::post('statistic', [StatisticsController::class, 'getBudgetStatistic']);
    Route::post('statistic-by-expense', [StatisticsController::class, 'getBudgetStatisticParExpenses']);
    Route::post('recap', [BudgetController::class, 'getRecapOfYear']);
    Route::post('add-income', [BudgetController::class, 'addIncomeToBudget']);
    Route::post('delete-income-budget', [BudgetController::class, 'deleteIncomeFromBudget']);
    Route::post('dashboard-recap', [BudgetController::class, 'dashboardRecap']);
    Route::post('close', [BudgetController::class, 'closeBudget']);
    
});

//Expenses
Route::group(['prefix' => 'expense'], function(){
    Route::get('unit/list', [ExpenseController::class, 'listUnitsMangement']);
    Route::post('list', [ExpenseController::class, 'list']);
    Route::post('create', [ExpenseController::class, 'create']);
    Route::post('delete', [ExpenseController::class, 'delete']);
    Route::post('detail', [ExpenseController::class, 'detail']);
});

//Envelopes
Route::group(['prefix' => 'envelope'], function(){
    Route::get('list', [EnvelopeController::class, 'list']);
    Route::post('history', [EnvelopeController::class, 'allHistories']);
});

// Frame
Route::get('expense/get-file-path/{id}', function ($id) {
    $expense = Expense::where('id', $id)->first();
    $file_path = 'storage/'.$expense->invoice_path;
    return response()->file(public_path($file_path));
    
});
