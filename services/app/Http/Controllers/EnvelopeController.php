<?php

namespace App\Http\Controllers;

use App\Models\Envelope;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EnvelopeController extends Controller
{
    public function list()
    {
        try{
            $data = Envelope::with('category')->where('create_id', auth()->user()->id)->get();
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
        
    }

}
