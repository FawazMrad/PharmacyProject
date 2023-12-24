<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderMedicine;
use App\Models\Pharmacist;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $user_id = Auth::user()->id;
        $pharmacist_id = Pharmacist::where('user_id',$user_id)->first()->id;
        $order = Order::create(['warehouse_id' => 1, 'pharmacist_id' => $pharmacist_id, 'date_ordered' => today(), 'total_price' => 0, 'status' => 'UNDER PREPARATION', 'payment_status' => 'UNPAID']);
        $orderedMeds = $request->all();   //id , quantity
        $unAvailableMedsCount = 0;
        $unAvailableMeds = [];
        foreach ($orderedMeds as $orderedMed) {
            $currentMed = Medicine::where('id', $orderedMed['id'])->first();
            $currentMedAvailableQuantity = $currentMed->quantity;
            if ($currentMedAvailableQuantity >= $orderedMed['quantity']) {
                $orderMed = OrderMedicine::create(['order_id' => $order->id, 'medicine_id' => $orderedMed['id'], 'quantity' => $orderedMed['quantity']]);
                $order->update(['total_price' => $order->total_price += ($currentMed->price) * $orderedMed['quantity']]);

            } else {
                $unAvailableMedsCount++;
                $unAvailableMeds[] = [$currentMed->commercial_name, $currentMed->quantity];
            }
        }
        if ($unAvailableMedsCount === 0) {
            return response()->json(['message' => 'Order sent successfully!'], 200);
        } else {
            if (count($orderedMeds) === $unAvailableMedsCount) {
                $order->delete();
                return response()->json(['message' => 'Order failed,Warehouse lacks of medicines you want', 'unavailable_meds' => json_encode($unAvailableMeds)], 404);
            }
            return response()->json(['message' => 'Some of the medicines you ordered are not available', 'unavailable_meds' => json_encode($unAvailableMeds)], 206);
        }
    }

    public function browsePharOrders()
    {
        $user_id = Auth::user()->id;
        $pharmacist_id = Pharmacist::where('user_id', $user_id)->first()->id;
        $orders = Order::where('pharmacist_id', $pharmacist_id)->get();
        if (count($orders) > 0) {

            return response()->json([$orders], 200);
        }
        return response()->json(['message' => 'You have no orders yet'], 404);

    }

    public function browseAdminOrders()
    {
        $orders = Order::all();
        if (count($orders) > 0) {
            return response()->json([$orders], 200);
        }
        return response()->json(['message' => 'You have no orders yet'], 404);
    }

    public function changeOrderStatus(Request $request)
    {
        $status = $request->status;
        $order_id = $request->order_id;
        $order = Order::where('id', $order_id)->first();
        if ($order->status != 'SENT') {
            $order->update(['status' => $status]);
            if ($order->status === 'SENT') {
                $orderMeds = OrderMedicine::where('order_id', $order->id)->get();
                foreach ($orderMeds as $orderMed) {
                    $med_id = $orderMed->medicine_id;
                    $order_med_quantity = $orderMed->quantity;
                    $med = Medicine::where('id', $med_id)->first();
                    $med_quantity = $med->quantity;
                    $med->update(['quantity' => ($med_quantity - $order_med_quantity)]);
                }
                return response()->json(['message' => 'Status updated successfully!'], 200);

            }
            return response()->json(['message' => 'Status updated successfully!'], 200);
        }
        return Response()->json(['message'=>'Action denied'],400);
    }


    public function changeOrderPaymentStatus(Request $request)
    {
        $order_id = $request->order_id;
        $order = Order::where('id', $order_id)->first();
        if ($order->payment_status === "UNPAID") {
            $order->update(['payment_status' => 'PAID']);
            return response()->json(['message' => 'Payment status updated successfully!'], 200);
        }
        return response()->json(['message' => 'Action denied'], 400);
    }
    public function history(Request $request)
    {
       $starting_date = $request->starting_date;
        $ending_date = $request->ending_date;
        $orders = Order::with('orderMedicine')->whereIn('date_ordered', [$starting_date, $ending_date])->get();

        if (count($orders) > 0) {
            $medsIds = [];

            $meds = [];
            $medsInOrder = [];

            foreach ($orders as $order) { // for the order
                foreach($order['order_medicines'] as $order_medicine) // order medicine
                {

                }
            }
    }
    }
        }
