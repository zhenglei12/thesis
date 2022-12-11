<?php


namespace App\Http\Controllers\Admin;


use App\Http\Model\Order;
use App\Http\Model\Role;
use App\Http\Model\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StaffControllers
{
    public function __construct(Request $request)
    {
        $this->request = $request;
    }


    /**
     * FunctionName：list
     * Description：列表
     * Author：cherish
     * @return mixed
     */
    public function list(){
        $page = $this->request->input('page') ?? 1;
        $pageSize = $this->request->input('pageSize') ?? 10;
        $order = new Order();
        $all = new Order();
        if ($this->request->input('name')) {
            $order = $order->where('staff_name', 'like', "%" . $this->request->input('name') . "%");
            $all = $all->where('staff_name', 'like', "%" . $this->request->input('name') . "%");
        }
        if ($this->request->input('end_time')) {
            $order = $order->whereDate('created_at', '<=', $this->request->input('end_time'))->whereDate('created_at', '>=', $this->request->input('created_at'));
            $all = $all->whereDate('created_at', '<=', $this->request->input('end_time'))->whereDate('created_at', '>=', $this->request->input('created_at'));
        }
        $role = Role::where('alias', "staff")->first();
        $userName = User::role($role['name'])->pluck('name');
        $order = $order->whereIn('staff_name', $userName);
        $all = $all->whereIn('staff_name', $userName);
        $data['amount_count'] = $all->sum('amount');
        $data['received_amount_count'] = $all->sum('received_amount');
        $data['list'] = $order->select(
            "staff_name",
            DB::raw('sum(amount) as amount'),
            DB::raw('sum(received_amount) as received_amount')
        )->groupBy('staff_name')->paginate($pageSize, ['*'], "page", $page);
        return $data;
    }
}
