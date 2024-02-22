<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Expense;
use App\Models\ManagementUnit;
use App\Models\Repartition;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function list(Request $request)
    {
        try {

            $param = [];
            if ($request->exp_amount) {
                $param[] = ['exp_amount', '=', $request->exp_amount];
            }
            if ($request->quantity) {
                $param[] = ['quantity', '=', $request->quantity];
            }
            if ($request->expense_date) {
                $param[] = ['expense_date', '=', $request->expense_date];
            }
            $id_reps = [];
            if ($request->category_id) {
                $reps = Repartition::where('category_id', $request->category_id)->where('create_id', getUserId())->get(['id']);
                foreach ($reps as $rep) {
                    $id_reps[] = $rep->id;
                }
            }
            if ($request->management_unit_id) {
                $param[] = ['management_unit_id', '=', $request->management_unit_id];
            }
            // if ($request->year_budget) {
            //     $param[] = ['year_budget', '=', $request->year_budget];
            // }     

            if($request->category_id){
                $data = Expense::with(['management_unit', 'repartition.budget.month', 'repartition.category'])
            ->where('create_id', getUserId())->whereIn('repartition_id', $id_reps)->where($param)
            ->orderBy('id', 'desc')->get();
            }else{
                $data = Expense::with(['management_unit', 'repartition.budget.month', 'repartition.category'])
            ->where('create_id', getUserId())->where($param)
            ->orderBy('id', 'desc')->get();
            }

            $total_expenses = 0;
            foreach ($data as $expense) {
                $total_expenses += $expense->exp_amount;
            }
            return response()->json([
                'data' => $data,
                'total_expenses' => $total_expenses,
                'message' => 'Liste des dépenses',
                'status' => 200
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }

    public function listUnitsMangement()
    {
        try {
            $data = ManagementUnit::orderBy('label', 'asc')->get();
            return response()->json([
                'data' => $data,
                'message' => 'Liste des unités de gestion',
                'status' => 200
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }

    public function verifyValueNumber($value)
    {
        if ($value == 'null') {
            $res = null;
        } else {
            $res = (int)$value;
        }
        return $res;
    }

    public function verifyValueString($value)
    {
        if ($value == 'null') {
            $res = null;
        } else {
            $res = $value;
        }
        return $res;
    }

    public function verifyValueDate($value)
    {
        if ($value == 'null') {
            $res = null;
        } else {
            $res = Carbon::parse($value)->addHour()->format('Y-m-d');
        }
        return $res;
    }

    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'budget_id' => 'required',
            'exp_amount' => 'required',
            'expense_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Certaines valeurs sont manquantes. Veuillez entrer toutes les données.',
                'status' => 515
            ]);
        }

        if (!$request->id) {
            try {
                DB::beginTransaction();
                $invoice = $request->invoice_path;
                if ($invoice != 'undefined' || $invoice == '') {
                    $invoice_path = $invoice->storeAs('documents', date('His') . '_' . $invoice->getClientOriginalName(), 'public');
                } else {
                    $invoice_path = null;
                }
                $exp_amount = $this->verifyValueNumber($request->exp_amount);

                $expense = Expense::create(array_merge([
                    'repartition_id' => $request->repartition_id,
                    'quantity' => $this->verifyValueNumber($request->quantity),
                    'unit_price' => $this->verifyValueNumber($request->unit_price),
                    'management_unit_id' => $this->verifyValueString($request->management_unit_id),
                    'exp_amount' => $exp_amount,
                    'expense_date' => $this->verifyValueDate($request->expense_date),
                    'create_id' => auth()->user()->id,
                    'invoice_path' => $invoice_path,
                    'comment' => $request->comment
                ]));

                if ($expense) {
                    $budget = Budget::find($request->budget_id);
                    if ($budget) {
                        $budget->total_expenses += $expense->exp_amount;
                        $budget->balance = $budget->total_incomes - $budget->total_expenses;
                        $response = $budget->update();
                        if ($response) {
                            DB::commit();
                            return response()->json([
                                'data' => $expense,
                                'message' => 'Dépense enregistée avec succès.',
                                'status' => 200
                            ]);
                        }
                    } else {
                        DB::rollBack();
                        return response()->json([
                            'data' => [],
                            'message' => 'Un problème de connexion est survenu. Veuillez reprendre le formulaire
                            d\'enregistrement de la dépense.',
                            'status' => 500
                        ]);
                    }
                }
            } catch (Exception $e) {
                Log::error($e->getMessage());
                return response()->json([
                    'data' => [],
                    'message' => 'Une erreur interne est survenue',
                    'status' => 500
                ]);
            }
        } else {
            $id = $request->id;
            try {
                $data = Expense::where('id', $id)->first();
                if (!$data) {
                    return response()->json([
                        'data' => $data,
                        'message' => 'Cette Expense n\'existe plus dans le système.',
                        'status' => 515
                    ]);
                }

                $data->update($request->all());

                return response()->json([
                    'data' => $data,
                    'message' => 'Mise à jour effectuée avec succès.',
                    'status' => 200
                ]);
            } catch (Exception $e) {
                Log::error($e->getMessage());
                return response()->json([
                    'data' => [],
                    'message' => 'Une erreur interne est survenue',
                    'status' => 500
                ]);
            }
        }
    }

    public function detail(Request $request)
    {
        try {
            $data = Expense::with(['management_unit', 'repartition.budget.month', 'repartition.category'])->where('create_id', auth()->user()->id)->where('id', $request->expense_id)->first();

            return response()->json([
                'data' => $data,
                'message' => 'Détail dépense',
                'status' => 200
            ]);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }


    public function delete(Request $request)
    {
        Log::info($request->id);
        if (!$request->id) {
            return response()->json([
                'data' => [],
                'message' => 'La source de revenus sélectionnée n\'existe plus dans le système.',
                'status' => 500
            ]);
        } else {
            try {
                $id = $request->id;
                $budget_id = $request->budget_id;

                $data = Expense::where('id', $id)->where('create_id', auth()->user()->id)->first();
                $budget = Budget::where('id', $budget_id)->where('create_id', getUserId())->first();

                if (!$data) {
                    return response()->json([
                        'data' => $data,
                        'message' => 'Cette source de revenus n\'existe plus dans le système.',
                        'status' => 515
                    ]);
                }

                if (!$budget) {
                    return response()->json([
                        'data' => $data,
                        'message' => 'Le dubget associé à cette dépense n\'existe pas dans le système.',
                        'status' => 515
                    ]);
                }

                DB::beginTransaction();

                $budget->total_expenses -= $data->exp_amount;
                $budget->balance += $data->exp_amount;
                $budget->save();

                $res = $data->delete();

                if ($data->invoice_path) {
                    if (file_exists(public_path('storage/' . $data->invoice_path))) {
                        unlink(public_path('storage/' . $data->invoice_path));
                    }
                }
                if ($res) {
                    DB::commit();
                    return response()->json([
                        'data' => $data,
                        'message' => 'Suppression effectuée avec succès.',
                        'status' => 200
                    ]);
                }
            } catch (Exception $ex) {
                Log::error($ex->getMessage());
                return response()->json([
                    'data' => [],
                    'message' => 'Une erreur interne est survenue',
                    'status' => 500
                ]);
            }
        }
    }
}
