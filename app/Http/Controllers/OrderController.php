<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function create(Request $request)
    {
        $rules = [
            'customer_email' => 'required_without:customer_id|email|exists:customers,email',
            'customer_id' => 'required_without:customer_email|integer|exists:customers,id'
        ];

        $this->checkFields($request->all(), $rules);


        $customer_id = $request->get('customer_id');
        if ($request->filled('customer_email'))
        {
            $customer = Customer::where('email', $request->get('customer_email'))->first();
            $customer_id = $customer->id;
        }
        Order::create(['customer_id' => $customer_id]);

        return response()->json([], 200);
    }

    public function list(Request $request)
    {
        $rules = [
            'customer_email' => 'email|exists:customers,email',
            'customer_id' => 'integer|exists:customers,id',
            'is_payed' => 'boolean'
        ];

        $this->checkFields($request->all(), $rules);

        $orders = Order::with('customer', 'products')
            ->when($request->filled('customer_email'), function ($query) use ($request){
                return $query->whereHas('customer', function ($q) use ($request){
                    return $q->where('email', $request->get('customer_email'));
                });
            })
            ->when($request->filled('customer_id'), function ($query) use ($request){
                return $query->where('customer_id', $request->get('customer_id'));
            })
            ->when($request->filled('is_payed'), function ($query) use ($request){
                return $query->where('is_payed', $request->get('is_payed'));
            })
            ->latest()->paginate();

        return response()->json(['data' => $orders], 200);

    }

    public function get(Order $order)
    {
        return $order->load('products', 'customer');
    }

    public function delete(Order $order)
    {
        // soft delete may be added
        $order->delete();

        return response()->json([], 200);
    }

    public function update(Request $request, Order $order)
    {
        $rules = [
            'customer_email' => 'required_without:customer_id|email|exists:customers,email',
            'customer_id' => 'required_without:customer_email|integer|exists:customers,id',
            'is_payed' => 'boolean'
        ];

        $this->checkFields($request->all(), $rules);

        if ($request->filled('customer_email'))
        {
            $customer = Customer::where('email', $request->get('customer_email'))->first();
            $order->customer_id = $customer->id;
        }

        if ($request->filled('customer_id'))
        {
            $order->customer_id = $request->get('customer_id');
        }

        if ($request->filled('is_payed'))
        {
            $order->is_payed = $request->get('is_payed');
        }

        $order->save();

        return response()->json(['data' => $order->load('customer')], 200);
    }

    public function attach(Order $order, Request $request)
    {
        $rules = [
            'product_id' => 'required|integer|exists:products,id',
        ];

        $this->checkFields($request->all(), $rules);

        if ($order->is_payed){
            return response()->json(['data' => ['message' => 'This order is already paid']], 400);
        }

        $product = $order->products()->where('product_id', $request->get('product_id'))->first();
        if ($product)
        {
            DB::table('order_products')
                ->where('order_id', $order->id)
                ->where('product_id', $request->get('product_id'))
                ->update(['amount' => DB::raw("amount + 1")]);
        }
        else
        {
            $order->products()->attach($request->get('product_id'));
        }

        return response()->json([], 200);
    }

    public function pay(Order $order)
    {

        if ($order->is_payed){
            return response()->json(['data' => ['message' => 'This order is already paid']], 400);
        }
        $client = new Client();


        $cost = 0;
        foreach ($order->products as $product) {
            $cost += $product->price * $product->amount['amount'];
        }

        $body = [
            'order_id' => $order->id,
            'customer_email' => $order->customer->email,
            'value' => $cost
        ];


        try{
            $result = $client->request('post', Order::PAYMENT_URI, [
                'json' => $body
            ]);
            $status = $result->getStatusCode();
        }
        catch (ClientException $e )
        {
            $status = $e->getCode();
        }

        switch ($status){
            case 200:
                $response = ['data' => ['message' => 'Payment successful']];
                $order->is_payed = true;
                $order->save();
                break;
            case 400:
                $response = ['data' => ['message' => 'Invalid request']];
                break;
            case 503:
                $response = ['data' => ['message' => 'Service Unavailable']];
                break;
            default:
                $response = ['data' => ['message' => '']];
                break;
        }



        return response()->json($response, $status);

    }
}
