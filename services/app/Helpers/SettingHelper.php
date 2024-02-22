<?php

use App\Mail\VerificationMail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;


// Generate id for each table in database
function generateDBTableId($length, $model_name): string
{
    $random_id = Str::random($length); //Generate random string
    $exists = $model_name::where('id', $random_id)->get(['id']);

    if (isset($exists[0]->id)) { //id exists in table
        return generateDBTableId($length, $model_name); //Retry with another generated id
    }
    return $random_id; //Return the generated id as it does not exist in the DB
}

function getUserId(){
    return auth()->user()->id;
}

if(!function_exists('sendMail')){
    //Send Mail
    
    function sendMail($to, $data, $template, $subject, $name, $message) { 
        if($to && $template && $subject && $name && $message)
         {
            Mail::to($to)->send(new VerificationMail($data, $template, $subject, $name, $message));
         }
    }
}

  
    