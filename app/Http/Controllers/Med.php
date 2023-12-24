<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Favourite;
use App\Models\Medicine;
use App\Models\Pharmacist;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class Med extends Controller
{
    public function add(Request $request)
    {
        $commercial_name = $request->commercial_name;
        $company = $request->company;
        $Meds = Medicine::where('company', $company)->get();
        if ($Meds->contains('commercial_name', $commercial_name)) {
            return \response()->json(['message' => 'Medicine already existed']);
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
            $category = Category::create(['name' => $request->category]);
        } else {
            $category = Category::where('name', $request->category)->first();
        }
        $medicine = Medicine::create(['scientific_name' => $request->scientific_name, 'commercial_name' => $request->commercial_name, 'company' => $request->company, 'description' => $request->description, 'quantity' => $request->quantity, 'price' => $request->price, 'expiration_date' => $request->expiration_date, 'category_id' => $category->id, 'warehouse_id' => 1]);
        return \response()->json(['message' => 'Medicine added successfully!']);
    }

    public function browseCategories()
    {
        $categories = Category::select('id', 'name')->get();
        return \response()->json($categories, 200);
    }

    public function browseMedsByCat(Request $request)
    {
        $category_id = $request->category_id;
        $meds = Medicine::where('category_id', $category_id)->get();
        return \response()->json($meds, 200);
    }

    public function search(Request $request)
    {
        $searchContent = strtolower($request->name);
        $med = Medicine::where('commercial_name', $searchContent)->first();
        if ($med) {
            return response()->json(['id' => $med->id, 'commercial_name' => $med->commercial_name, 'scientific_name' => $med->scientific_name, 'company' => $med->company, 'description' => $med->description, 'quantity' => $med->quantity, 'price' => $med->price, 'expiration_date' => $med->expiration_date, 'category' => $med->category->name, 'from' => 'medicine', 200]);
        } else {
            $cat = Category::where('name', $searchContent)->first();
            if ($cat) {
                return response()->json(['id' => $cat->id, 'name' => $cat->name, 'from' => 'category'], 200);
            }
            return response()->json(['message' => 'No medicine or category found'], 404);
        }
    }

    public function searchList(Request $request)
    {
        $searchContent = strtolower($request->name);
        $cats = $this->getSimilarCatsByName($searchContent);
        $meds = $this->getSimilarMedsByName($searchContent);
        if (count($meds) > 0 && count($cats) > 0) {
            return \response()->json([['categories' => $cats, 'medicines' => $meds], 200]);
        }
        if (count($meds) > 0) return \response()->json(['medicines' => $meds, 200]);
        if (count($cats) > 0) return \response()->json(['categories' => $cats, 200]);

        return response()->json(['message' => 'No medicine or category found'], 404);
    }

    public function getSimilarCatsByName($name)
    {
        return Category::where('name', 'like', '%' . $name . '%')->select('id', 'name')->get();
    }

    public function getSimilarMedsByName($name)
    {
        return Medicine::where('commercial_name', 'like', '%' . $name . '%')->select('id', 'commercial_name')->get();
    }

    public function showMedSpec(Request $request)
    {
        $id = $request->id;
        $med = Medicine::where('id', $id)->first();
        return \response()->json($med, 200);
    }


    public function changeFavStatus(Request $request)
    {
        $user_id = Auth::user()->id;
        $pharmacist_id = Pharmacist::where('user_id', $user_id)->first()->id;
        $medicine_id = $request->medicine_id;
        $fav = Favourite::where('pharmacist_id', $pharmacist_id)->where('medicine_id', $medicine_id)->first();
        if ($fav) {
            $fav->delete();
            return \response()->json([
                'message' => 'Removed from favourite',
                'status' => 'false'], 200
            );
        }
        Favourite::create(['pharmacist_id' => $pharmacist_id,
            'medicine_id' => $medicine_id
        ]);
        return \response()->json([
            'message' => 'Added to favourite',
            'status' => 'true'], 200);

    }

    public function browseFavourites()
    {
        $user_id = Auth::user()->id;
        $pharmacist_id = Pharmacist::where('user_id', $user_id)->first()->id;
        $favs = Favourite::where('pharmacist_id', $pharmacist_id)->get();
        $fav_meds_ids = [];
        if (count($favs) > 0) {
            foreach ($favs as $fav) {
                $fav_meds_ids[] = $fav['medicine_id'];
            }
            $fav_meds = Medicine::whereIn('id', $fav_meds_ids)->get();
            return \response()->json($fav_meds, 200);
        }
        return \response()->json(['message' => 'No medicines found'], 404);

    }

}

