<?php

namespace App\Http\Controllers;

use App\Models\Medicine;
use App\Models\Order;
use App\Models\OrderMedicine;
use App\Models\Pharmacist;
use Couchbase\IndexFailureException;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class OrderController extends Controller
{
    public function createOrder(Request $request)
    {
        $user_id = Auth::user()->id;
        $pharmacist_id = Pharmacist::where('user_id', $user_id)->first()->id;
        $order = Order::create(['warehouse_id' => 1, 'pharmacist_id' => $pharmacist_id, 'date_ordered' => today(), 'total_price' => 0, 'status' => 'UNDER PREPARATION', 'payment_status' => 'UNPAID']);
        $orderedMeds = $request->all();   //id , quantity
        $unAvailableMedsCount = 0;
        $unAvailableMeds = [];
        foreach ($orderedMeds as $orderedMed) {
            $currentMed = Medicine::where('id', $orderedMed['id'])->first();
            $currentMedAvailableQuantity = $currentMed->quantity;
            $currentMedExpDate = $currentMed->expiration_date;
            if ($currentMedAvailableQuantity >= $orderedMed['quantity'] && $currentMedExpDate >= today()->addYears(2)) {
                $orderMed = OrderMedicine::create(['order_id' => $order->id, 'medicine_id' => $orderedMed['id'], 'quantity' => $orderedMed['quantity']]);
                $order->update(['total_price' => $order->total_price += ($currentMed->price) * $orderedMed['quantity']]);
                $currentMed->update(['quantity' => $currentMedAvailableQuantity - $orderedMed['quantity']]);
            } else {
                $unAvailableMedsCount++;
                $unAvailableMeds[] = ["medicine name" => $currentMed->commercial_name, 'quantity' => $currentMedAvailableQuantity, 'expiration date' => $currentMedExpDate];
            }
        }
        if ($unAvailableMedsCount === 0) {
            return response()->json(['message' => 'Order sent successfully!'], 200);
        } else {
            if (count($orderedMeds) === $unAvailableMedsCount) {
                $order->delete();
                return response()->json(['message' => 'Order failed,Warehouse lacks of medicines you want', 'unavailable_meds' => json_encode($unAvailableMeds)], 404);
            }
            return response()->json(['message' => 'Some of the medicines you ordered are either not available or expired', 'unavailable_meds' => json_encode($unAvailableMeds)], 206);
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
        $order_payment_status = $order->payment_status;
        $order_old_status = $order->status;
        if ($order_old_status === 'UNDER PREPARATION' && $status === 'SENT') {
            if ($order_payment_status === 'PAID') {
                $order->update(['status' => $status]);
//                $orderMeds = OrderMedicine::where('order_id', $order->id)->get();
//                foreach ($orderMeds as $orderMed) {
//                    $med_id = $orderMed->medicine_id;
//                    $order_med_quantity = $orderMed->quantity;
//                    $med = Medicine::where('id', $med_id)->first();
//                    $med_quantity = $med->quantity;
//                    $med->update(['quantity' => ($med_quantity - $order_med_quantity)]);
//                }
                return response()->json(['message' => 'Order sent successfully!'], 200);
            }
            return response()->json(['message' => 'Order unpaid yet!'], 400);
        }
        if ($order_old_status === 'SENT' && $status === 'RECEIVED') {
            $order->update(['status' => $status]);
            return response()->json(['message' => 'Order received successfully!'], 200);
        }
        return response()->json(['message' => 'Action denied!'], 400);
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
        $orders = Order::whereBetween('date_ordered', ["$starting_date", "$ending_date"])->get();
        $totalPrice = 0.0; // the orders total price
        $medsWithValues = Collection::make();
        // collection has all the medicines and their values in the whole orders in specific time
        if (count($orders) > 0) {
            foreach ($orders as $order) { // for the order
                $totalPrice += $order->total_price;
                $orderMeds = OrderMedicine::where('order_id', $order->id)->get();
                foreach ($orderMeds as $orderMed) // order medicine
                {
                    $med_name = Medicine::where('id', $orderMed->medicine_id)->first()->commercial_name;
                    $med_quantity = $orderMed->quantity;
                    if ($medsWithValues->has($med_name)) {
                        $medsWithValues[$med_name] += $med_quantity;
                    } else {
                        $medsWithValues->put($med_name, $med_quantity);
                    }
                }
            }
            return response()->json(['orders' => $orders, 'total_price' => $totalPrice, 'meds_with_their_values' => $medsWithValues]);
        }
        return response()->json(['message', 'You do not have any orders yet'], 404);
    }
}
