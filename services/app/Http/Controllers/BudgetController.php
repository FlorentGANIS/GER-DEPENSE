<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Envelope;
use App\Models\Expense;
use App\Models\ExpenseBudget;
use App\Models\HistoryEnvelope;
use App\Models\Income;
use App\Models\IncomeBudget;
use App\Models\Month;
use App\Models\Repartition;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Mockery\Undefined;

class BudgetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function list(Request $request)
    {
        try {
            $year = $request->year_budget;
            if (!$year || $year == null || $year == 'Undefined') {
                $year = date('Y');
            }
            $data['months'] = Month::orderBy('month_number')->get();

            $data['budgets'] = Budget::with(['incomes', 'month'])->where('year_budget', $year)->where('create_id', getUserId())->get();
            $_data = [];
            foreach ($data['months'] as $month) {
                $child = [];
                // Nom du budget: Ex: Budget de Janvier
                $child['budget_name'] = $month->label;
                $child['month_id'] = $month->id;
                foreach ($data['budgets'] as $budget) {
                    if ($month->id == $budget['month_id']) {
                        $child['budget_id'] = $budget->id;
                        $child['status'] = $budget->status;
                        $child['is_closed'] = $budget->is_closed;
                        $child['global_amount'] = $budget->global_amount;
                        $child['remaining_amount'] = $budget->remaining_amount;
                        $child['total_incomes'] = $budget->total_incomes;
                        $child['total_expenses'] = $budget->total_expenses;
                        $child['balance'] = $budget->balance;
                        $child['month_id'] = $budget->month_id;
                        $child['year_budget'] = $budget->year_budget;
                    }
                }
                if (!isset($child['budget_id'])) {
                    $child['budget_id'] = null;
                }
                if (!isset($child['status'])) {
                    $child['status'] = 0;
                }
                if (!isset($child['global_amount'])) {
                    $child['global_amount'] = 0;
                }
                if (!isset($child['remaining_amount'])) {
                    $child['remaining_amount'] = 0;
                }
                if (!isset($child['total_incomes'])) {
                    $child['total_incomes'] = 0;
                }
                if (!isset($child['total_expenses'])) {
                    $child['total_expenses'] = 0;
                }
                if (!isset($child['balance'])) {
                    $child['balance'] = 0;
                }
                if (!isset($child['month_id'])) {
                    $child['month_id'] = null;
                }
                if (!isset($child['year_budget'])) {
                    $child['year_budget'] = null;
                }
                $_data[] = $child;
            }

            $index_page = $this->getIndexCaroussel();

            $true_current_month = Month::where('month_number', date('n'))->first('id');

            return response()->json([
                'data' => $_data,
                'index_page' => $index_page,
                'true_current_month' => $true_current_month,
                'message' => 'Liste des budgets',
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

    public function getIndexCaroussel()
    {
        $current_month = Month::where('month_number', date('n'))->first('month_number');
        switch ($current_month->month_number) {
            case 1:
            case 2:
            case 3:
                return 0;
                break;
            case 4:
            case 5:
            case 6:
                return 1;
                break;
            case 7:
            case 8:
            case 9:
                return 2;
                break;
            case 10:
            case 11:
            case 12:
                return 3;
                break;
            default:
                return 0;
        }
    }


    public function create(Request $request)
    {
        $global_amount = 0;
        $validator = Validator::make($request->form, [
            'incomes' => 'required'
        ]);

        if ($validator->fails() || !$request->month) {
            return response()->json([
                'message' => 'Saisissez le budget et entrez les revenus.',
                'status' => 515
            ]);
        }

        if (!$request->id) {
            try {
                $array_to_count = $request->form['incomes'];

                foreach ($request->form['incomes'] as $income) {
                    $cpt = 0;
                    foreach ($array_to_count as $income_copy) {
                        if ($income['income_id'] == $income_copy['income_id']) {
                            $cpt++;
                            if ($cpt > 1) {
                                return response()->json([
                                    'data' => [],
                                    'message' => 'Veuillez choisir une seule source de revenus.
                                    Deux sources ou plusieurs sources identiques ont été sélectionnées',
                                    'status' => 500
                                ]);
                            }
                        }
                    }
                }

                DB::beginTransaction();
                foreach ($request->form['incomes'] as $income) {
                    $global_amount += $income['income_amount'];
                }
                $budget = Budget::create([
                    'id' => generateDBTableId(8, "App\Models\Budget"),
                    'month_id' => $request->month,
                    'year_budget' => date('Y'),
                    'global_amount' => $global_amount,
                    'remaining_amount' => $global_amount,
                    'total_incomes' => 0,
                    'total_expenses' => 0,
                    'balance' => 0,
                    'create_id' => getUserId()
                ]);

                if ($budget) {
                    foreach ($request->form['incomes'] as $value) {
                        IncomeBudget::create([
                            'ib_amount' => $value['income_amount'],
                            'budget_id' => $budget->id,
                            'income_id' => $value['income_id'],
                            'create_id' => getUserId()

                        ]);
                    }
                    DB::commit();
                    return response()->json([
                        'data' => $budget,
                        'message' => 'Budget enregisté avec succès.',
                        'status' => 200
                    ]);
                }
            } catch (Exception $e) {
                Db::rollBack();
                Log::error($e->getMessage());
                return response()->json([
                    'data' => [],
                    'message' => 'Une erreur interne est survenue',
                    'status' => 500
                ]);
            }
        }
      
    }

    public function  edit(Request $request)
    {
        $data['budget'] = Budget::where('id', $request->id)->where('create_id', getUserId())->first();
        $data['incomes'] = IncomeBudget::where('budget_id', $request->id)->where('create_id', getUserId())->get();
        $data['repartitions'] = Repartition::with(['category'])->where('budget_id', $request->id)->get();
        return response()->json([
            'data' => $data,
            'message' => 'Détails',
            'status' => 200
        ]);
    }

    public function addIncomeToBudget(Request $request)
    {
        $budget_incomes = IncomeBudget::with(['income', 'budget'])->where('budget_id', $request->budget_id)->where('create_id', getUserId())->get();
        foreach ($budget_incomes as $income) {
            if ($income->income_id == $request->form['income_id']) {
                return response()->json([
                    'data' => [],
                    'message' => 'Cette source de revenu existe déjà pour ce budget',
                    'status' => 500
                ]);
            }
        }

        try {
            DB::beginTransaction();

            $income_bud = IncomeBudget::create([
                'ib_amount' => $request->form['ib_amount'],
                'budget_id' => $request->budget_id,
                'income_id' => $request->form['income_id'],
                'create_id' => getUserId()

            ]);

            if (!$income_bud) {
                return response()->json([
                    'data' => [],
                    'message' => 'Une erreur est survenue lors de l\'enregistrement. Veuillez réessayer.',
                    'status' => 500
                ]);
            }

            $budget = Budget::find($request->budget_id);
            $budget->global_amount += $request->form['ib_amount'];
            $budget->remaining_amount += $request->form['ib_amount'];
            $budget->save();

            DB::commit();

            return response()->json([
                'data' => '',
                'message' => 'Source de revenu ajoutée avec succès.',
                'status' => 200
            ]);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }


    public function detailToShareOut(Request $request)
    {
        $id = $request->id;
        try {
            $data['budget'] = Budget::with(['month'])->where('id', $id)->where('create_id', getUserId())->first();

            if (!$data['budget']) {
                return response()->json([
                    'message' => 'Ce budget n\'existe pas dans le système.',
                    'status' => 515
                ]);
            }

            $data['fixed_categories'] = Category::where('type', 'FIXE')->where('status', true)->where('create_id', getUserId())->get();

            if ($data['budget']['is_shared']) {
                $data['repartitions'] = Repartition::with(['category'])->where('budget_id', $id)->where('create_id', getUserId())->get();

                $data['repartitions_amount'] = 0;

                foreach ($data['repartitions'] as $rep) {
                    $data['repartitions_amount'] += $rep['rep_amount'];
                }
            }

            $total = 0;
            foreach ($data['fixed_categories'] as $value) {
                $total += $value->fixed_amount;
            }
            $data['total'] = $total;
            return response()->json([
                'data' => $data,
                'message' => 'Détails',
                'status' => 200
            ]);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }

    public function show(Request $request)
    {
        try {
            $id = $request->id;
            $data['budget'] = Budget::with('month')->where('id', $id)->where('create_id', getUserId())->first();

            if (!$data['budget']) {
                return response()->json([
                    'data' => $data,
                    'message' => 'Ce budget n\'existe pas dans le système.',
                    'status' => 515
                ]);
            }
            $data['incomes'] = IncomeBudget::with(['income', 'budget'])->where('budget_id', $id)->where('create_id', getUserId())->get();
            //$repartitions = Repartition::with(['category'])->where('budget_id', $id)->where('create_id', getUserId())->get();
            $expenses = Expense::with(['repartition', 'management_unit'])->where('create_id', getUserId())->orderBy('id', 'desc')->get();

            $exp_budget = ExpenseBudget::with(['category'])->where('budget_id', $id)->where('create_id', getUserId())->get();

            // // Somme des revenus de ce budget
            $data['total_sum_incomes_recup'] = 0;
            // // Somme des charges fixes de ce budget
            $data['total_sum_cf_recup'] = 0;
            // // Somme des charges variables de ce budget
            $data['total_sum_cv_recup'] = 0;
            // // Somme des épargnes de ce budget
            $data['total_sum_saving_recup'] = 0;
            // // Montant total utilisé par répartition
            $data['total_fixed_sum_used_by_rep'] = 0;
            $data['total_variable_sum_used_by_rep'] = 0;
            $data['total_saving_sum_used_by_rep'] = 0;

            $data['others_data'] = [];
            //$data['total_fixed_charges'] = 0;
            $data['categories'] = [];
            $exps = [];
            $exps_by_rep = [];
            $_data = [];
            $data['total_amount_used'] = 0;

            foreach ($data['incomes'] as $income_for_bud) {
                $data['total_sum_incomes_recup'] += $income_for_bud->ib_amount;
            }

            
            // Original



            foreach ($exp_budget as $rep) {
                if ($rep->category->type == 'FIXE') {
                    $_data['type_charge'] = 'FIXE';
                }
                if ($rep->category->type == 'VARIABLE') {
                    $_data['type_charge'] = 'VARIABLE';
                }
                if ($rep->category->type == 'EPARGNE') {
                    $_data['type_charge'] = 'EPARGNE';
                }
                $_data['rep_designation'] = $rep->category->designation;
                $_data['repartition_id'] = $rep->repartition_id;
                $_data['category_id'] = $rep->category->id;
                $_data['prevision'] = $rep->prevision;
                $_data['envelope_help'] = $rep->envelope_help;
                $data['categories'][] = $rep->category->designation;
                $_data['amount_used'] = $rep->amount_used;
                foreach ($expenses as $exp) {
                    if ($exp->repartition_id === $rep->repartition_id) {
                        $exps['expense_id'] = $exp->id;
                        $exps['expense_date'] = $exp->expense_date;
                        $exps['exp_amount'] = $exp->exp_amount;
                        $exps['unit_price'] = $exp->unit_price;
                        $exps['quantity'] = $exp->quantity;
                        $exps['rep_balance'] = $exp->rep_balance;
                        $exps['repartition_id'] = $exp->repartition_id;
                        if ($exp->management_unit_id == null || !isset($exp->management_unit_id)) {
                            $exps['management_unit_id'] = null;
                        } else {
                            $exps['management_unit_id'] = $exp->management_unit->label;
                        }
                        $exps['comment'] = ($exp->comment == NULL || $exp->comment == '') ? "" : $exp->comment;
                        // Montant total utilisé par répartition
                        //$_data['sum_amount_used'] += $exp->exp_amount;
                        if ($exp->invoice_path == null || !isset($exp->invoice_path)) {
                            $exps['invoice_path'] = null;
                        } else {
                            $exps['invoice_path'] = $exp->invoice_path;
                        }
                        $exps_by_rep[] = $exps;
                    }
                }

                $_data["expenses"] = $exps_by_rep;
                //$data['total_amount_used'] += $_data['sum_amount_used'];
                $data['others_data'][] = $_data;
                //Log::info( $data['others_data']);
                $exps_by_rep = [];
                $_data = [];
            }

            foreach ($data['others_data'] as $other) {
                if ($other['type_charge'] == 'FIXE') {
                    $data['total_fixed_sum_used_by_rep'] += $other['amount_used'];
                }
                if ($other['type_charge'] == 'VARIABLE') {
                    $data['total_variable_sum_used_by_rep'] += $other['amount_used'];
                }
                if ($other['type_charge'] == 'EPARGNE') {
                    $data['total_saving_sum_used_by_rep'] += $other['amount_used'];
                }
            }

            $data['total_fixed_balance_by_rep'] = $data['total_sum_cf_recup'] - $data['total_fixed_sum_used_by_rep'];
            $data['total_variable_balance_by_rep'] = $data['total_sum_cv_recup'] - $data['total_variable_sum_used_by_rep'];
            $data['total_saving_balance_by_rep'] = $data['total_sum_saving_recup'] - $data['total_saving_sum_used_by_rep'];
            $data['exp_budget'] = $exp_budget;
            //$data['exp_budget']['all_expenses'] = $data['others_data'];
            return response()->json([
                'data' => $data,
                'message' => 'Détails du budget',
                'status' => 200
            ]);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }

    public function delete(Request $request)
    {
        if (!$request->id) {
            return response()->json([
                'data' => [],
                'message' => 'La source de revenus sélectionnée n\'existe plus dans le système.',
                'status' => 500
            ]);
        } else {
            $id = $request->id;
            $data = Budget::where('id', $id)->where('create_id', getUserId())->first();
            if (!$data) {
                return response()->json([
                    'data' => $data,
                    'message' => 'Ce budget n\'existe plus dans le système.',
                    'status' => 515
                ]);
            }
            try {
                $repartitions = Repartition::with(['category'])->where('budget_id', $request->id)->where('create_id', getUserId())->get();
                if (count($repartitions) > 0) {
                    return response()->json([
                        'data' => '',
                        'message' => 'Ce budget a été réparti. Impossible de le supprimer.',
                        'status' => 515
                    ]);
                }

                $incomes = IncomeBudget::where('budget_id', $request->id)->where('create_id', getUserId())->get();

                foreach ($incomes as $income) {
                    $income->delete();
                }

                $data->delete($request->all());

                return response()->json([
                    'data' => $data,
                    'message' => 'Suppression effectuée avec succès.',
                    'status' => 200
                ]);
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

    public function shareOut(Request $request)
    {
        $budget_id = $request->distribution_form['budget_id'];
        $budget = Budget::where('id', $budget_id)->where('create_id', getUserId())->first();
        $repartitions = Repartition::with(['category'])->where('budget_id', $budget_id)->where('create_id', getUserId())->get();

        try {
            if (!$budget->is_shared) {
                DB::beginTransaction();
                foreach ($request->items as $item) {
                    $current_rep = Repartition::create([
                        'id' => generateDBTableId(20, "App\Models\Repartition"),
                        'budget_id' => $budget_id,
                        'category_id' => $item['category_id'],
                        'rep_amount' => $item['rep_amount'],
                        'create_id' => getUserId()
                    ]);

                    ExpenseBudget::create([
                        'budget_id' => $budget_id,
                        'repartition_id' => $current_rep->id,
                        'prevision' => $item['rep_amount'],
                        'amount_used' => 0,
                        'type' => 'TEST',
                        'category_id' => $item['category_id'],
                        'envelope_help' => 0,
                        'create_id' => getUserId()
                    ]);
                }

                $budget->remaining_amount = $request->distribution_form['remaining_amount'];
                $budget->is_shared = 1;
                $budget->total_incomes = $budget->global_amount - $request->distribution_form['remaining_amount'];
                $budget->balance = $budget->global_amount - $request->distribution_form['remaining_amount'];
                $budget->is_active = 1;
                $res = $budget->update();

                if ($res) {
                    DB::commit();
                    return response()->json([
                        'message' => 'Budget réparti avec succès',
                        'status' => 200
                    ]);
                }
            }

            if ($budget->is_shared) {
                DB::beginTransaction();
                // Montant global à ajouter
                $amount_to_update_remaining = $budget->remaining_amount;
                // Compter le nombre de changement
                $compteur = 0;

                // Cas où une le montant d'une catégorie existante a été mise à jour
                foreach ($request->items as $item) {
                    foreach ($repartitions as $rep) {
                        // Si la catégorie reçu du formulaire est égale à la catégorie de la répartition du budget et 
                        // que le montant est différent
                        if ($item['category_id'] == $rep['category_id'] && $item['rep_amount'] != $rep['rep_amount']) {
                            // Ajout du montant de la répartition au montant provisioire du montant restant à répartir
                            $amount_to_update_remaining += $rep['rep_amount'];
                            // Affectation du montant à changer au montant à remplacer
                            $rep['rep_amount'] = $item['rep_amount'];
                            // Enregistrer la répartition
                            $rep->save();
                            // Soustraire le nouveau montant du montant global à répartir
                            $amount_to_update_remaining -= $rep['rep_amount'];
                            // Incrémenter
                            $compteur++;

                            $exp_bud = ExpenseBudget::where('repartition_id', $rep->id)->where('create_id', getUserId())->first();
                            $exp_bud->prevision = $item['rep_amount'];
                            $exp_bud->save();
                        }
                    }
                }

                // Cas où une nouvelle catégorie a été ajoutée
                foreach ($request->items as $item) {
                    // Compter le nombre de nouvelles catégories ajoutées
                    $compteur2 = 0;
                    foreach ($repartitions as $rep) {
                        if ($item['category_id'] == $rep['category_id']) {
                            $compteur2++;
                        }
                    }
                    if ($compteur2 == 0) {
                        $new_rep = Repartition::create([
                            'id' => generateDBTableId(20, "App\Models\Repartition"),
                            'budget_id' => $budget_id,
                            'category_id' => $item['category_id'],
                            'rep_amount' => $item['rep_amount'],
                            'create_id' => getUserId()
                        ]);

                        $ex_b = ExpenseBudget::create([
                            'budget_id' => $budget_id,
                            'repartition_id' => $current_rep->id,
                            'prevision' => $item['rep_amount'],
                            'amount_used' => 0,
                            'type' => 'TEST',
                            'category_id' => $item['category_id'],
                            'envelope_help' => 0,
                            'create_id' => getUserId()
                        ]);
                        if (!$new_rep || $ex_b) {
                            return response()->json([
                                'data' => [],
                                'message' => 'Une erreur interne est survenue. Veuillez réessayer.',
                                'status' => 500
                            ]);
                        } else {
                            $amount_to_update_remaining -= $new_rep->rep_amount;
                        }
                    }
                }

                // Cas où une catégorie existante a été supprimée
                foreach ($repartitions as $rep) {
                    // Compter le nombre de catégories supprimées
                    $compteur3 = 0;
                    foreach ($request->items as $item) {
                        if ($rep['category_id'] == $item['category_id']) {
                            $compteur3++;
                        }
                    }
                    if ($compteur3 == 0) {
                        // Vérifier si une dépense n'a pas été ajoutée à la répartition
                        $expenses_verified = Expense::where('create_id', auth()->user()->id)->where('repartition_id', $rep['category_id'])->get();
                        if (count($expenses_verified) > 0) {
                            return response()->json([
                                'data' => [],
                                'message' => 'Imposible de supprimer cette catégorie. Une dépense au moins a été ajoutée à cette dernière.',
                                'status' => 500
                            ]);
                        }

                        $exp_bud2 = ExpenseBudget::where('repartition_id', $rep->id)->where('create_id', getUserId())->first();
                        $exp_bud2->delete();

                        // Récupération temporaire du montant de la catégorie à supprimer
                        $temp_amount_for_rep_to_delete = $rep['rep_amount'];
                        // On supprime la répartition
                        $rep_deleted = $rep->delete();
                        if (!$rep_deleted || !$exp_bud2) {
                            return response()->json([
                                'data' => [],
                                'message' => 'Une erreur interne est survenue. Veuillez réessayer.',
                                'status' => 500
                            ]);
                        } else {
                            $amount_to_update_remaining += $temp_amount_for_rep_to_delete;
                        }
                    }
                }


                $budget->remaining_amount = $amount_to_update_remaining;
                $budget->total_incomes = $budget->global_amount - $amount_to_update_remaining;
                $budget->balance = $budget->global_amount - $amount_to_update_remaining - $budget->total_expenses;
                $resp = $budget->save();

                if ($resp) {
                    DB::commit();
                    return response()->json([
                        'data' => '',
                        'message' => 'Budget réparti avec succès',
                        'status' => 200
                    ]);
                }
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

    public function listDistinctCharges()
    {
        try {
            $data['fixed_charges'] = Category::where('create_id', getUserId())->where('status', true)->where('type', 'FIXE')->orderBy('designation', 'asc')->get();
            $data['variable_charges'] = Category::where('create_id', getUserId())->where('status', true)->where('type', 'VARIABLE')->orderBy('designation', 'asc')->get();
            $data['saving_charges'] = Category::where('create_id', getUserId())->where('status', true)->where('type', 'EPARGNE')->orderBy('designation', 'asc')->get();
            return response()->json([
                'data' => $data,
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

    public function countAppEntities()
    {
        try {
            $data['budgets'] = Budget::where('create_id', getUserId())->get();
            $data['incomes'] = Income::where('create_id', getUserId())->get();
            $data['fixed_charges'] = Category::where('type', 'FIXE')->where('create_id', getUserId())->get();
            $data['variable_charges'] = Category::where('type', 'VARIABLE')->where('create_id', getUserId())->get();

            $current_month = Month::where('month_number', date('n'))->first('id');

            $data['current_month_data'] = Budget::with(['month'])->where('year_budget', date('Y'))->where('month_id', $current_month->id)->where('create_id', getUserId())->first();

            return response()->json([
                'data' => $data,
                'message' => 'Nombre des entités',
                'status' => 200
            ]);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }

    public function getActifBudgetDetail()
    {
        try {
            $data['budget'] = Budget::with(['month'])->where('status', true)->where('create_id', getUserId())->first();
            $repartitions = Repartition::with(['category'])->where('budget_id', $data['budget']['id'])->where('create_id', getUserId())->get();
            $expenses = Expense::with('repartition')->where('create_id', getUserId())->get();
            $data['category_data'] = [];
            $data['sum_total_used'] = 0;
            foreach ($repartitions as $rep) {
                $_data['id'] = $rep->id;
                $_data['rep_amount'] = $rep->rep_amount;
                $_data['rep_designation'] = $rep->category->designation;
                $_data['sum_amount_used'] = 0;

                foreach ($expenses as $exp) {
                    if ($exp->repartition_id === $rep->id) {
                        $_data['sum_amount_used'] += $exp->exp_amount;
                    }
                }
                $data['sum_total_used'] += $_data['sum_amount_used'];
                $data['category_data'][] = $_data;
                $_data = [];
            }
            return response()->json([
                'data' => $data,
                'message' => 'Détails',
                'status' => 200
            ]);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }

    public function getDistinctYears()
    {
        try {
            $years = Budget::distinct()->orderBy('year_budget', 'desc')->where('create_id', getUserId())->get('year_budget');
            return response()->json([
                'data' => $years,
                'status' => 200
            ]);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }

    public function verifyIfRepartitionsExist($budget_id)
    {
        $repartitions = Repartition::with('category')->where('budget_id', $budget_id)->where('create_id', getUserId())->get();
        //Log::info($repartitions);
        return $repartitions;
    }


    public function getRecapOfYear(Request $request)
    {
        try {
            $data = [];
            // Tous les revenus du système
            $all_incomes = Income::all();
            // Toutes les catégories
            $all_categories = Category::all();
            // $all_repartitions = Repartition::all();

            $data['months'] = Month::orderBy('month_number')->get();

            $data['budgets'] = Budget::with(['incomes', 'month'])->where('year_budget', $request->year_budget)->where('create_id', getUserId())->get();

            $data['incomes_recup'] = IncomeBudget::with(['income', 'budget'])->where('create_id', getUserId())->get();

            $data['expenses'] = Expense::with(['repartition', 'management_unit'])->where('create_id', getUserId())->orderBy('id', 'desc')->get();

            // Récap des revenus
            // Pour récupérer tout ce qui concerne les revenus
            foreach ($data['months'] as $month) {
                $child = [];
                $child['budget_name'] = $month->label;
                $child['month_id'] = $month->id;
                foreach ($data['budgets'] as $budget) {
                    if ($month->id == $budget['month_id']) {
                        $child['budget_id'] = $budget->id;
                        $budget_incomes = [];
                        // Si le budget a des revenus
                        if ($budget['incomes']) {
                            // On boucle sur la liste des revenus du système
                            foreach ($all_incomes as $parent_income) {
                                $budget_inc = [];
                                // On boucle sur la liste des revenus du budget
                                foreach ($budget['incomes'] as $inc_bud) {
                                    //  On vérifie si le revenu existe dans le budget
                                    if ($parent_income->id == $inc_bud->id) {
                                        // On boucle sur répartition pour prendre les informations
                                        foreach ($data['incomes_recup'] as $recup) {
                                            if ($recup->income_id == $parent_income->id && $budget->id == $recup->budget_id) {
                                                $budget_inc['id'] = $inc_bud->id;
                                                $budget_inc['income_designation'] = $inc_bud->label;
                                                $budget_inc['income_amount'] = $recup->ib_amount;
                                                $budget_incomes[] = $budget_inc;
                                                break;
                                            }
                                        }
                                    }
                                }

                                //Si le système ne trouve aucune information sur ce revenu
                                if (count($budget_inc) <= 0) {
                                    $budget_inc['id'] = $parent_income->id;
                                    $budget_inc['income_designation'] = $parent_income->label;
                                    $budget_inc['income_amount'] = 0;
                                    $budget_incomes[] = $budget_inc;
                                }
                            }
                        }
                    }
                }

                // Si le budget n'a pas encore été créé, on met tous les revenus du système à 0
                if (!isset($child['budget_id'])) {
                    $child['budget_id'] = null;
                    $budget_incomes = [];
                    foreach ($all_incomes as $parent_income) {
                        $budget_inc2 = [];
                        $budget_inc2['id'] = $parent_income->id;
                        $budget_inc2['income_designation'] = $parent_income->label;
                        $budget_inc2['income_amount'] = 0;
                        $budget_incomes[] = $budget_inc2;
                    }
                }
                $child['budget_incomes'] = $budget_incomes;


                $_data[] = $child;
            }


            // Tableau général de collecte des montants de chaque dépense avant la line de total dépenses
            $parent_recap = [];

            // Pour chaque budget dans l'ordre
            foreach ($all_incomes as $income) {
                $recap = [];
                // Id du revenu
                $recap['income_id'] = $income->id;
                // Nom du revenu
                $recap['income_label'] = $income->label;
                // Total du revenu de toute l'année
                $total_incomes_by_year = 0;
                // On boucle sur chaque budget
                foreach ($_data as $budget) {
                    $month = [];
                    // Id du mois
                    $month['id'] = $budget['month_id'];
                    // Nom du mois
                    $month['month_name'] = $budget['budget_name'];
                    // On boucle sur chaque revenus du budget
                    foreach ($budget['budget_incomes'] as $inc) {
                        // Si le revenu du budget correspond à l'actuel revenu 
                        if ($income->id == $inc['id']) {
                            // Montant du revenu
                            $month['income_amount'] = $inc['income_amount'];
                            $total_incomes_by_year += $inc['income_amount'];
                            $recap['months'][] = $month;
                            // Quitter la boucle
                            break;
                        }
                    }
                }
                $recap['total_incomes'] = $total_incomes_by_year;
                $parent_recap[] = $recap;
            }


            // // La somme totale par mois de tous les revenus
            $global_total_incomes = 0;
            $snd_data = [];
            // Calcul de la somme totale
            for ($cpt = 0; $cpt < count($parent_recap); $cpt++) {
                $global_total_incomes += $parent_recap[$cpt]['total_incomes'];
            }
            // Deux premières colonnes 
            $snd_data['general_info'] = [
                'label' => 'Revenus',
                'total_incomes_amount' => $global_total_incomes
            ];

            // On boucle sur chaque mois par ordre
            foreach ($data['months'] as $month) {
                // Total des revenus par mois
                $total_incomes_by_month = 0;
                // Tableau tampon des informations
                $details_income_by_month = [];
                // Nom du budget en cours
                $details_income_by_month['month_label'] = $month->label;
                // On boucle sur chaque line du grand tableau recap des montants par revenu et par mois
                for ($i = 0; $i < count($parent_recap); $i++) {
                    // On boucle sur les mois de chaque revenu
                    for ($j = 0; $j < count($parent_recap[$i]['months']); $j++) {
                        // On vérifie si l'id du mois correspond à l'id du mois sur lequel la boucle passe
                        if ($month->id == $parent_recap[$i]['months'][$j]['id']) {
                            // Si c'est le cas, on ajoute au total. Ex: Janvier pour Salaire + Janvier pour Allocations + Janvier pour Vente
                            $total_incomes_by_month += $parent_recap[$i]['months'][$j]['income_amount'];
                            // Quand on trouve l'information, on sort de la boucle
                            break;
                        }
                    }
                }
                // On crée un tableau pour chaque mois avec le total obtenu
                $details_income_by_month['total_incomes_by_month'] = $total_incomes_by_month;
                // Ajouter au tableau de la ligne des totaux, les infos de chaque mois obtenu
                $snd_data['details'][] = $details_income_by_month;
            }


            // Fin du recap des revenus



            // Pour récupérer tout ce qui concerne les dépenses
            foreach ($data['months'] as $month) {
                $child2 = [];
                $child2['budget_name'] = $month->label;
                $child2['month_id'] = $month->id;
                foreach ($data['budgets'] as $budget) {
                    if ($month->id == $budget['month_id']) {
                        $budget_expenses = [];
                        $child2['budget_id'] = $budget->id;
                        // Si le budget a des répartitions
                        if ($reps = $this->verifyIfRepartitionsExist($budget->id)) {
                            foreach ($all_categories as $category) {
                                $budget_exp = [];
                                foreach ($reps as $rep) {

                                    if ($category->id == $rep->category_id) {

                                        $expenses = Expense::with(['repartition', 'management_unit'])->where('repartition_id', $rep->id)->where('create_id', getUserId())->orderBy('id', 'desc')->get();

                                        $total_rep_amount_used = 0;
                                        foreach ($expenses as $expense) {
                                            $total_rep_amount_used += $expense->exp_amount;
                                        }
                                        $budget_exp['category_id'] = $rep->category_id;
                                        $budget_exp['category_designation'] = $category->designation;
                                        $budget_exp['expense_amount'] = $total_rep_amount_used;
                                        $budget_expenses[] = $budget_exp;
                                    }
                                }

                                //Si le système ne trouve aucune information sur les dépenses cette répartition

                                if (count($budget_exp) <= 0) {
                                    $budget_exp['category_id'] = $category->id;
                                    $budget_exp['category_designation'] = $category->designation;
                                    $budget_exp['expense_amount'] = 0;
                                    $budget_expenses[] = $budget_exp;
                                }
                            }
                        }
                    }
                }

                // Si le budget n'a pas encore été créé, on met tous les revenus du système à 0
                if (!isset($child2['budget_id'])) {
                    $child2['budget_id'] = null;
                    $budget_expenses = [];
                    foreach ($all_categories as $parent_cat) {
                        $budget_exp2 = [];
                        $budget_exp2['category_id'] = $parent_cat->id;
                        $budget_exp2['repartition_designation'] = $parent_cat->designation;
                        $budget_exp2['expense_amount'] = 0;
                        $budget_expenses[] = $budget_exp2;
                    }
                }
                $child2['budget_all_repartitions'] = $budget_expenses;


                $_data2[] = $child2;
            }


            // Tableau général de collecte des montants de chaque dépense avant la line de total dépense
            $parent_recap2 = [];

            // Pour chaque budget dans l'ordre
            foreach ($all_categories as $category) {
                $recap2 = [];
                // Id du revenu
                $recap2['category_id'] = $category->id;
                // Nom du revenu
                $recap2['category_designation'] = $category->designation;
                // Total de la dépense de toute l'année
                $total_expenses_by_year = 0;
                // On boucle sur chaque budget
                foreach ($_data2 as $budget2) {
                    $month = [];
                    // Id du mois
                    $month['id'] = $budget2['month_id'];
                    // Nom du mois
                    $month['month_name'] = $budget2['budget_name'];
                    // On boucle sur chaque dépense du budget
                    foreach ($budget2['budget_all_repartitions'] as $rep) {
                        // Si le revenu du budget correspond à l'actuel revenu 
                        if ($category->id == $rep['category_id']) {
                            // Montant de la dépense
                            $month['expense_amount'] = $rep['expense_amount'];
                            $total_expenses_by_year += $rep['expense_amount'];
                            $recap2['months'][] = $month;
                            // Quitter la boucle
                            break;
                        }
                    }
                }
                $recap2['total_expenses'] = $total_expenses_by_year;
                $parent_recap2[] = $recap2;
            }

            // Tableau général de collecte des montants de chaque dépense avant la line de total dépenses


            // // La somme total par mois de tous les revenus
            $global_total_expenses = 0;
            $snd_data2 = [];
            // Calcul de la somme totale
            for ($cpt = 0; $cpt < count($parent_recap2); $cpt++) {
                $global_total_expenses += $parent_recap2[$cpt]['total_expenses'];
            }
            // Deux premières colonnes 
            $snd_data2['general_info2'] = [
                'label' => 'Dépenses',
                'total_expenses_amount' => $global_total_expenses
            ];

            // On boucle sur chaque mois par ordre
            foreach ($data['months'] as $month) {
                // Total des dépenses par mois
                $total_expenses_by_month = 0;
                // Tableau tampon des informations
                $details_expense_by_month = [];
                // Nom du budget en cours
                $details_expense_by_month['month_label'] = $month->label;
                // On boucle sur chaque line du grand tableau recap des montants par revenu et par mois
                for ($i = 0; $i < count($parent_recap2); $i++) {
                    // On boucle sur les mois de chaque revenu
                    for ($j = 0; $j < count($parent_recap2[$i]['months']); $j++) {
                        // On vérifie si l'id du mois correspond à l'id du mois sur lequel la boucle passe
                        if ($month->id == $parent_recap2[$i]['months'][$j]['id']) {
                            // Si c'est le cas, on ajoute au total. Ex: Janvier pour Salaire + Janvier pour Allocations + Janvier pour Vente
                            $total_expenses_by_month += $parent_recap2[$i]['months'][$j]['expense_amount'];
                            // Quand on trouve l'information, on sort de la boucle
                            break;
                        }
                    }
                }
                // On crée un tableau pour chaque mois avec le total obtenu
                $details_expense_by_month['total_expense_by_month'] = $total_expenses_by_month;
                // Ajouter au tableau de la ligne des totaux, les infos de chaque mois obtenu
                $snd_data2['details'][] = $details_expense_by_month;
            }

            return response()->json([
                // Infos sur revenus
                'data' => $parent_recap,
                // Ligne de total des revenus
                'total_line_1' => $snd_data,
                // Infos sur dépenses
                'data_expenses' => $parent_recap2,
                //Ligne de total des dépenses
                'total_line_2' => $snd_data2,
                'message' => 'Récap',
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

    public function deleteIncomeFromBudget(Request $request)
    {
        Log::info('azertyui oui oui');
        try {
            $budget_id = $request->budget_id;
            $id = $request->id;
            $budget = Budget::where('id', $budget_id)->where('create_id', getUserId())->first();
            $income_budget = IncomeBudget::where('budget_id', $budget_id)->where('id', $id)->where('create_id', getUserId())->first();


            if ($income_budget->ib_amount > $budget->remaining_amount) {
                return response()->json([
                    'message' => 'Cette source de revenus ne peut pas être supprimée car son montant est inclu 
                    dans la budgétisation.',
                    'status' => 515
                ]);
            }
            DB::beginTransaction();

            // Ajouter au reste à répartir, le montant de la source de revenu à supprimer
            $budget->global_amount -= $income_budget->ib_amount;
            $budget->remaining_amount = $budget->global_amount - $budget->total_incomes;

            $budget->save();

            // Supprimer la source de revenu
            $res = $income_budget->delete();

            if ($res) {
                DB::commit();
                return response()->json([
                    'message' => 'Source de revenue supprimée effectuée avec succès.',
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


    public function dashboardRecap(Request $request)
    {
        try {
            $year = $request->year;
            if (!$year || $year == null || $year == 'Undefined') {
                $year = date('Y');
            }
            $data['months'] = Month::orderBy('month_number')->get();

            $data['budgets'] = Budget::where('year_budget', $year)->where('create_id', getUserId())->get();
            $_data = [];
            foreach ($data['months'] as $month) {
                $child = [];
                // Nom du budget: Ex: Budget de Janvier
                $child['budget_name'] = $month->label;
                $child['month_id'] = $month->id;
                foreach ($data['budgets'] as $budget) {
                    if ($month->id == $budget['month_id']) {
                        $child['total_incomes'] = $budget->total_incomes;
                        $child['total_expenses'] = $budget->total_expenses;
                    }
                }
                if (!isset($child['total_incomes'])) {
                    $child['total_incomes'] = 0;
                }
                if (!isset($child['total_expenses'])) {
                    $child['total_expenses'] = 0;
                }

                $_data[] = $child;
            }
            return response()->json([
                'data' => $_data,
                'message' => 'Récap des budgets sur le dashboard',
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

    public function getOthersYears()
    {
        try {
            $years = Budget::distinct()->whereNotIn('year_budget', [date('Y')])->orderBy('year_budget', 'desc')->where('create_id', getUserId())->get('year_budget');
            return response()->json([
                'data' => $years,
                'status' => 200
            ]);
        } catch (Exception $ex) {
            Log::error($ex->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }

    public function closeBudget(Request $request)
    {
        try {
            $budget_repartitions = $request->budget_repartitions;
            Log::info($budget_repartitions);
            // Log::info('-------------');
            // Log::info($request->budget_id);
            $budget = Budget::where('id', $request->budget_id)->where('create_id', getUserId())->first();

            if (!$budget) {
                return response()->json([
                    'data' => [],
                    'message' => 'Aucun budget trouvé.',
                    'status' => 300
                ]);
            }

            if ($budget) {
                DB::beginTransaction();
                //$all_categories_in_envelope = Envelope::where('create_id', getUserId())->get('category_id');
                //$all_repartitions_for_budgets = Repartition::where('budget_id', $request->budget_id)->where('create_id', getUserId())->get('category_id');

                //Log::info($all_repartitions_for_budgets);
                $budget->is_closed = 1;
                $budget->update();

                foreach ($budget_repartitions as $rep) {
                    $rest = $rep['prevision'] - $rep['amount_used'];
                    if ($rest > 0) {
                        $envelope_categ = Envelope::where('category_id', $rep['category_id'])->where('create_id', getUserId())->first();
                        if ($envelope_categ) {
                            $envelope_categ->envelope_amount += $rest;
                            $envelope_categ->save();
                        }

                        if (!$envelope_categ) {
                            Envelope::create([
                                'category_id' => $rep['category_id'],
                                'create_id' => getUserId(),
                                'envelope_amount' => $rest
                            ]);
                        }

                        HistoryEnvelope::create([
                            'type' => 'Entrée',
                            'create_id' => getUserId(),
                            'category_id' => $rep['category_id'],
                            'env_amount' => $rest,
                            'from_budget' => $budget->id,
                            'to_budget' => null,
                        ]);
                    }
                }

                DB::commit();
                return response()->json([
                    'message' => 'Budget clôturé avec succès',
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
