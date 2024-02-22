<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\Repartition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StatisticsController extends Controller
{
    public function getBudgetStatistic(Request $request)
    {
        if (!$request->month_id || !$request->year_budget) {
            return response()->json([
                'message' => 'Ce budget n\'existe pas dans le système.',
                'status' => 500
            ]);
        }

        // $repartitions = Repartition::query()
        //     ->select('repartitions.*')
        //     ->where(DB::raw('(select id from budgets where month_id = 
        //     '. $request->month_id.' and year_budget = '.$request->year_budget.')'), '=', 'repartitions.budget_id')
        //     ->get();

        $budget = Budget::where('month_id', $request->month_id)->where('year_budget', $request->year_budget)->where('create_id', getUserId())->first('id');
        if(!$budget){
            return response()->json([
                'message' => 'Ce budget n\'existe pas dans le système.',
                'status' => 515
            ]);
        }
        $reps = Repartition::with('category')->where('budget_id', $budget->id)->where('create_id', getUserId())->get();

        $data = [];
        $temp = [];
        foreach ($reps as $rep) {
            $temp['name'] = $rep->category->designation;
            $temp['value'] = $rep->rep_amount;

            $data[] = $temp;
        }
        return response()->json([
            'data' => $data,
            'message' => 'Stat.',
            'status' => 200
        ]);
    }


    public function getBudgetStatisticParExpenses(Request $request)
    {
        if (!$request->month_id || !$request->year_budget) {
            return response()->json([
                'message' => 'Ce budget n\'existe pas dans le système.',
                'status' => 500
            ]);
        }

        $budget = Budget::where('month_id', $request->month_id)->where('year_budget', $request->year_budget)->where('create_id', getUserId())->first('id');
        if(!$budget){
            return response()->json([
                'message' => 'Ce budget n\'existe pas dans le système.',
                'status' => 515
            ]);
        }

        $reps = Repartition::with('category')->where('budget_id', $budget->id)->where('create_id', getUserId())->get();
       
        $expenses = Expense::where('create_id', getUserId())->orderBy('id', 'desc')->get();

        $data = [];

        foreach ($reps as $rep) {
            $temp = [];
            $temp['name'] = $rep->category->designation;
            $temp['value'] = 0;
            foreach ($expenses as $exp) {
                if ($exp->repartition_id === $rep->id) {
                    // Montant total utilisé par répartition
                    $temp['value'] += $exp->exp_amount;
                }
            }
            $data[] = $temp;
        }

        return response()->json([
            'data' => $data,
            'message' => 'Stat.',
            'status' => 200
        ]);
    }
}
