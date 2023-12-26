<?php


namespace App\Http\Controllers\Admin;

use App\Http\Model\Order;
use App\Http\Model\OrderFrom;
use Illuminate\Http\Request;
use App\Http\Constants\CodeMessageConstants;

class OrderFromControllers
{
    public function __construct(Request $request)
    {
        $this->request = $request;

    }

    /**
     * FunctionName：add
     * Description：添加
     * Author：cherish
     * @return mixed
     */
    public function add()
    {
        $this->request->validate([
            'order_id' => ['required', 'exists:' . (new Order())->getTable() . ',id'],
            'subject' => 'required',
            'grade' => 'required',
            'use' => 'required',
            'language' => 'required',
            'special_ask' => 'required',
            'school_name' => 'required',
            'manuscript_time' => 'required',
            'contact_way' => 'required',
            'model_essay' => 'required',
        ]);
        $data = $this->request->input();
        $orderFrom = OrderFrom::where('order_id', $data['order_id'])->first();
        if ($orderFrom)
            throw \ExceptionFactory::business(CodeMessageConstants::NOT_CAN);
        return OrderFrom::create($data);
    }

    /**
     * FunctionName：detail
     * Description：
     * Author：cherish
     * @return mixed
     */
    public function detail()
    {
        $this->request->validate([
            'order_id' => ['required', 'exists:' . (new Order())->getTable() . ',id']
        ]);
        $data = $this->request->input();
        return OrderFrom::where('order_id', $data['order_id'])->first();
    }
}
