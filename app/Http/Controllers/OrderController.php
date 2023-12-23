<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderMedicine;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $user_id = $request->user_id;
        $order = Order::create([
            'warehouse_id' => 1,
            'pharmacist_id' => $user_id,
            'date_ordered' => today(),
            'total_price' => 0,
            'status' => 'Under preparation',
            'payment_status' => 'Unpaid'
        ]);
        $meds = $request->meds;
        $unAvailableMedsCount = 0;
        $unAvailableMeds = [];
        foreach ($meds as $med) {
            $currentMed = Medicine::where('id', $med['id'])->first();
            $currentMedAvailableQuantity = $currentMed->quantity;
            if ($currentMedAvailableQuantity >= $med['quantity']) {
                $orderMed = OrderMedicine::create([
                    'order_id' => $order->id,
                    'medicine_id' => $med['id'],
                    'quantity' => $med['quantity']
                ]);
                $order->total_price += ($currentMed->price) * $med['quantity'];
                $order->save();
            } else {
                $unAvailableMedsCount++;
                $unAvailableMeds[] = [$currentMed->commercial_name, $currentMed->quantity];
            }
        }
            if ($unAvailableMedsCount === 0) {
                return response()->json([
                    'message' => 'Order sent successfully!'
                ],
                    200);
            } else {
                if (count($meds) === $unAvailableMedsCount){
                    $order->delete();
                    return response()->json([
                        'message' => 'Order failed,Warehouse lacks of medicines you want'
                    ],404);
                }
                return response()->json([
                    'message' => 'Some of the medicines you orderd are not available',
                    'unavailable_meds' => json_encode($unAvailableMeds)
                ],
                    206);
            }
        }


}
