<?php

namespace App\Http\Controllers;

use App\Models\VariableCharge;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class VariableChargeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function list()
    {
        try{
            $data = VariableCharge::where('create_id', auth()->user()->id)->orderBy('designation', 'asc')->get();
            $nb = $data->count();
            return response()->json([
                'data' => $data,
                'nb_data' => $nb,
                'message' => 'Liste des charges variables',
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
        $designation = trim($request->designation);

        $validator = Validator::make($request->all(), [
            'designation' => 'required|unique:variable_charges|min:4|max:50'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Veullez entrer une désignation qui n\'existe pas encore et au minimum 4 caractères.',
                'status' => 515
            ]);
        }

        if (!$request->id) {
            try {
                $result = VariableCharge::where('designation', $designation)->first();
                if ($result != null) {
                    return response()->json([
                        'message' => 'Cette charge variable existe déjà dans le système',
                        'status' => 515
                    ]);
                } else {
                    $income = VariableCharge::create([
                        'designation' => strtoupper($designation),
                        'has_detail' => $request->has_detail,
                        'create_id' => auth()->user()->id
                    ]);

                    return response()->json([
                        'data' => $income,
                        'message' => 'Charge variable enregistée avec succès.',
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
                $data = VariableCharge::where('id', $id)->first();
                if (!$data) {
                    return response()->json([
                        'data' => $data,
                        'message' => 'Cette charge variable n\'existe plus dans le système.',
                        'status' => 515
                    ]);
                }

                $_data = VariableCharge::where('designation', $designation)->get();
                if ($_data->count() > 1) {
                    return response()->json([
                        'data' => $_data,
                        'message' => 'Une autre charge variable existe avec cette désignation.',
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
                'message' => 'La charge variable sélectionnée n\'existe plus dans le système.',
                'status' => 500
            ]);
        } else {
            $id = $request->id;
            try {
                $data = VariableCharge::where('id', $id)->first();
                if (!$data) {
                    return response()->json([
                        'data' => $data,
                        'message' => 'Cette charge variable n\'existe plus dans le système.',
                        'status' => 515
                    ]);
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
}
