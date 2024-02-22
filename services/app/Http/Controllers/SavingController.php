<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SavingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function list()
    {
        try{
            $data = Saving::where('create_id', auth()->user()->id)->orderBy('label', 'asc')->get();
            $nb = $data->count();
            return response()->json([
                'data' => $data,
                'nb_data' => $nb,
                'message' => 'Liste des épargnes',
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

        $validator = Validator::make($request->all(), [
            'label' => 'required|string|unique:savings|min:4|max:50'
        ]);

        if($validator->fails()){
            return response()->json([
                'message' => 'Veullez entrer une désignation qui n\'existe pas encore et au minimum 4 caractères.',
                'status' => 515
            ]);
        }

        if (!$request->id) {
            try {
                $result = Saving::where('label', $label)->first();
                if ($result != null) {
                    return response()->json([
                        'message' => 'Cette épargne existe déjà dans le système',
                        'status' => 515
                    ]);
                } else {
                    $saving = Saving::create([
                        'label' => strtoupper($label),
                        'create_id' => auth()->user()->id
                    ]);

                    return response()->json([
                        'data' => $saving,
                        'message' => 'Epargne enregistée avec succès.',
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
                $data = Saving::where('id', $id)->first();
                if (!$data) {
                    return response()->json([
                        'data' => $data,
                        'message' => 'Cette épargne n\'existe plus dans le système.',
                        'status' => 515
                    ]);
                }

                $_data = Saving::where('label', $label)->get();
                if ($_data->count() > 1) {
                    return response()->json([
                        'data' => $_data,
                        'message' => 'Une autre épargne existe avec cette désignation.',
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
                'message' => 'La épargne sélectionnée n\'existe plus dans le système.',
                'status' => 500
            ]);
        } else {
            $id = $request->id;
            try {
                $data = Saving::where('id', $id)->first();
                if (!$data) {
                    return response()->json([
                        'data' => $data,
                        'message' => 'Cette épargne n\'existe plus dans le système.',
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
