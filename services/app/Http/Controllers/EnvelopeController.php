<?php

namespace App\Http\Controllers;

use App\Models\Envelope;
use App\Models\HistoryEnvelope;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnvelopeController extends Controller
{
    public function list()
    {
        try{
            $data = Envelope::with('category')->where('create_id', getUserId())->get();
            return response()->json([
                'data' => $data,
                'message' => 'Liste des enveloppes',
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

    public function allHistories(Request $request){
        try{
            $data = HistoryEnvelope::with(['category', 'from_budget', 'to_budget', 'from_budget.month', 'to_budget.month'])->where('create_id', getUserId())->get();
            
            return response()->json([
                'data' => $data,
                'message' => 'Liste des enveloppes',
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

}
