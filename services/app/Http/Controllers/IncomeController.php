<?php

namespace App\Http\Controllers;

use App\Models\Income;
use App\Models\IncomeBudget;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class IncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function list()
    {
        try{
            $data = Income::where('create_id', auth()->user()->id)->orderBy('label', 'asc')->get();
            return response()->json([
                'data' => $data,
                'message' => 'Liste des sources de revenus',
                'status' => 200
            ]);            
        }catch(Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }

    public function allIncomes()
    {
        try{
            $data = Income::where('create_id', auth()->user()->id)->orderBy('label', 'asc')->get();
            $nb = $data->count();
            return response()->json([
                'data' => $data,
                'nb_data' => $nb,
                'message' => 'Liste des sources de revenus',
                'status' => 200
            ]);            
        }catch(Exception $e){
            Log::error($e->getMessage());
            return response()->json([
                'data' => [],
                'message' => 'Une erreur interne est survenue',
                'status' => 500
            ]);
        }
    }

    public function create(Request $request)
    {
        $label = trim($request->label);

        if (!$request->id) {
            try {
                $result = Income::where('label', $label)->where('create_id', getUserId())->first();
                if ($result != null) {
                    return response()->json([
                        'message' => 'Cette source de revenus existe déjà dans le système',
                        'status' => 515
                    ]);
                } else {
                    $income = Income::create([
                        'id' => generateDBTableId(10, "App\Models\Income"),
                        'label' => strtoupper($label),
                        'create_id' => auth()->user()->id
                    ]);

                    return response()->json([
                        'data' => $income,
                        'message' => 'Source de revenus enregistée avec succès.',
                        'status' => 200
                    ]);
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
                $data = Income::where('id', $id)->where('create_id', getUserId())->first();
                if (!$data) {
                    return response()->json([
                        'data' => $data,
                        'message' => 'Cette Income n\'existe plus dans le système.',
                        'status' => 515
                    ]);
                }

                $_data = Income::where('label', $label)->where('create_id', getUserId())->get();
                if ($_data->count() > 1) {
                    return response()->json([
                        'data' => $_data,
                        'message' => 'Une autre source de revenus existe avec ce libellé.',
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
            try {
                $data = Income::where('id', $id)->where('create_id', getUserId())->first();
                if (!$data) {
                    return response()->json([
                        'data' => $data,
                        'message' => 'Cette source de revenus n\'existe plus dans le système.',
                        'status' => 515
                    ]);
                }

                $incomes_budget = IncomeBudget::where('create_id', getUserId())->get();

                // Compter si on trouve que le revenu a été utilisé dans un budget au moins
                $cpt = 0;
                foreach($incomes_budget as $inc_bud){
                    if($inc_bud->income_id == $data->id){
                        $cpt++;
                        break;
                    }
                }

               if($cpt >= 1){
                return response()->json([
                    'data' => $data,
                    'message' => 'Cette source de revenu ne peut être supprimée car
                    elle est associée à un budget.',
                    'status' => 515
                ]);
               }
                $data->delete();

                return response()->json([
                    'data' => $data,
                    'message' => 'Source de revenus supprimée avec succès.',
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


    public function changeStatus(Request $request)
    {
        if (!$request->id) {
            return response()->json([
                'data' => [],
                'message' => 'La source de revenus sélectionnée n\'existe plus dans le système.',
                'status' => 500
            ]);
        } else {
            $id = $request->id;
            try {
                $data = Income::where('id', $id)->where('create_id', getUserId())->first();
                if (!$data) {
                    return response()->json([
                        'data' => $data,
                        'message' => 'Cette source de revenus n\'existe plus dans le système.',
                        'status' => 515
                    ]);
                }

                $data->status = !$data->status;
                $data->save();

                return response()->json([
                    'data' => $data,
                    'message' => 'Statut changé avec succès.',
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


}
