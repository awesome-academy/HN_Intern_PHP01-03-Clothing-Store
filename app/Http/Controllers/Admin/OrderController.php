<?php

namespace App\Http\Controllers\Admin;

use Pusher\Pusher;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Events\StatusNotificationEvent;
use App\Notifications\OrderNotification;
use App\Repositories\Order\OrderRepositoryInterface;
use App\Repositories\OrderProduct\OrderProductRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    protected $orderRepo;

    public function __construct(
        OrderRepositoryInterface $orderRepo,
        OrderProductRepositoryInterface $orderProductRepo
    ) {
        $this->orderRepo = $orderRepo;
        $this->orderProductRepo = $orderProductRepo;
    }

    public function index()
    {
        $orders = $this->orderRepo->getAllWithUsers();

        return view('admin.orders.order')->with(compact('orders'));
    }

    public function show($id)
    {
        $order = $this->orderRepo->getOrderWithUser($id);
        $order_products = $this->orderProductRepo->getOrderProduct($id);
        $products = $order->products;

        return view('admin.orders.detailorder')->with(compact('order', 'order_products', 'products'));
    }

    public function update(Request $request)
    {
        $order = $this->orderRepo->getOrderById($request->id);
        $order->status = $request->status;
        $order->update();

        $data = [
            'id' => $order->id,
            'status' => $order->status,
        ];
        $user = $order->user;
        $user->notify(new OrderNotification($data));
        $notification_id = $user->notifications->first()->id;
        $data['notification_id'] = $notification_id;

        event(new StatusNotificationEvent($data, $user->id));

        return redirect()->route('admin.orders.show', $order->id)->with('success', 'Update success');
    }

    public function readNotification($id)
    {
        try {
            Auth::user()->Notifications->find($id)->markAsRead();
        } catch (\Exception $th) {
            return response()->json([
                'mess' => $th,
            ], 500);
        }

        return response()->json([
            'mess' => 'success',
        ], 200);
    }

    public function readAllNotification()
    {
        try {
            Auth::user()->Notifications->markAsRead();
        } catch (\Throwable $th) {
            return response()->json([
                'mess' => 'fail',
            ], 500);
        }

        return response()->json([
            'mess' => 'success',
        ], 200);
    }
}
