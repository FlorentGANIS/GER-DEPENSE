<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Repartition;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function allCategories(){
        try{
            $data = Category::where('create_id', getUserId())->where('status', true)->orderBy('designation', 'asc')->get();
            $nb = $data->count();
            return response()->json([
                'data' => $data,
                'nb_data' => $nb,
                'message' => 'Liste de toutes les charges',
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
    public function categoriesWithoutFixed(){
        try{
            $categories = Category::where('create_id', getUserId())->whereNotIn('type', ['FIXE'])->where('status', true)->orderBy('designation', 'asc')->get();
            $data = [];
            foreach($categories as $category){
                $_data = [];
                $_data['category_id'] = $category->id;
                $_data['rep_amount'] = 0;
                $_data['rep_name'] = $category->designation;
                $_data['rep_type'] = $category->type;

                $data[] = $_data;
            }

            return response()->json([
                'data' => $data,
                'message' => 'Liste',
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
    /**
     * Display a listing of the resource.
     */
    public function fixedChargeList()
    {
        try{
            $data = Category::where('create_id', getUserId())->where('type', 'FIXE')->orderBy('designation', 'asc')->get();
            $nb = $data->count();
            return response()->json([
                'data' => $data,
                'nb_data' => $nb,
                'message' => 'Liste des charges fixes',
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

    public function variableChargeList()
    {
        try{
            $data = Category::where('create_id', getUserId())->where('type', 'VARIABLE')->orderBy('designation', 'asc')->get();
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

    public function savingList()
    {
        try{
            $data = Category::where('create_id', getUserId())->where('type', 'EPARGNE')->orderBy('designation', 'asc')->get();
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
        $designation = trim($request->designation);
        $type = $request->type;
        $fixed_amount = $request->fixed_amount;

       
        if ($request->id == null) {
            try {
                $result = Category::where('designation', $designation)->where('type', $type)->where('create_id', getUserId())->first();
                if ($result) {
                    return response()->json([
                        'message' => 'Cette catégorie existe déjà dans le système',
                        'status' => 515
                    ]);
                } else {
                    $charge = Category::create([
                        'id' => generateDBTableId(10, "App\Models\Category"),
                        'designation' => strtoupper($designation),
                        'type' => $type,
                        'fixed_amount' => $fixed_amount,
                        'create_id' => getUserId()
                    ]);

                    return response()->json([
                        'data' => $charge,
                        'message' => 'catégorie enregistée avec succès.',
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
                $data = Category::where('id', $id)->where('create_id', getUserId())->first();
                if (!$data) {
                    return response()->json([
                        'data' => $data,
                        'message' => 'Cette catégorie n\'existe plus dans le système.',
                        'status' => 515
                    ]);
                }

                $_data = Category::where('designation', $designation)->where('create_id', getUserId())->get();
                if (count($_data) >= 1) {
                    return response()->json([
                        'data' => $_data,
                        'message' => 'Une autre catégorie existe avec cette désignation.',
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

    public function changeStatus(Request $request)
    {
        if (!$request->id) {
            return response()->json([
                'data' => [],
                'message' => 'La catégorie sélectionnée n\'existe plus dans le système.',
                'status' => 500
            ]);
        } else {
            $id = $request->id;
            try {
                $data = Category::where('id', $id)->where('create_id', getUserId())->first();
                if (!$data) {
                    return response()->json([
                        'data' => $data,
                        'message' => 'Cette catégorie n\'existe plus dans le système.',
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

    public function delete(Request $request)
    {
        if (!$request->id) {
            return response()->json([
                'data' => [],
                'message' => 'La catégorie sélectionnée n\'existe plus dans le système.',
                'status' => 500
            ]);
        } else {
            $id = $request->id;
            try {
                $data = Category::where('id', $id)->where('create_id', getUserId())->first();
                if (!$data) {
                    return response()->json([
                        'data' => $data,
                        'message' => 'Cette catégorie n\'existe plus dans le système.',
                        'status' => 515
                    ]);
                }

                $repartitions = Repartition::where('create_id', getUserId())->get();
                
                // Compter si on trouve que la catégorie a été utilisée dans une répartition d'un budget
                $cpt = 0;
                foreach($repartitions as $rep){
                    if($rep->category_id == $data->id){
                        $cpt++;
                        break;
                    }
                }

               if($cpt >= 1){
                return response()->json([
                    'data' => $data,
                    'message' => 'Cette catégorie ne peut être supprimée car
                    elle est associée à un budget.',
                    'status' => 515
                ]);
               }

                $data->delete();

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
