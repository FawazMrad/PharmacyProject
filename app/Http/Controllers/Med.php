<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Medicine;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class Med extends Controller
{
    public function add(Request $request)
    {
        $commercial_name = $request->commercial_name;
        $company = $request->company;
        $Meds = Medicine::where('company', $company)->get();
        if ($Meds->contains('commercial_name', $commercial_name)) {
            return \response()->json([
                'message' => 'Medicine already existed'
            ]);
        }
//        $validator = Validator::make($request->all(), [

//            'commercial_name' => 'unique:medicines',
//            'quantity' => 'numeric|gt:0',
//            'price' => 'numeric|gt:0',
//
//        ]);
//        if ($validator->fails()) {
//            return response()->json([
//                'message' => $validator->messages()
//            ]);
        //       } else
        //    {
        if (!(Category::where('name', $request->category))->first()) {
            $category = Category::create([
                'name' => $request->category
            ]);
        } else {
            $category = Category::where('name', $request->category)->first();
        }


        $medicine = Medicine::create([
            'scientific_name' => $request->scientific_name,
            'commercial_name' => $request->commercial_name,
            'company' => $request->company,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'price' => $request->price,
            'expiration_date' => $request->expiration_date,
            'category_id' => $category->id,
            'warehouse_id' => 1
        ]);
        return \response()->json([
            'message' => 'Medicine added successfully!'
        ]);
    }
}
