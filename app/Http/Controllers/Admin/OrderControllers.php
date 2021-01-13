<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Model\Order;
use App\Http\Model\User;
use App\Http\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class OrderControllers extends Controller
{
    public function __construct(Request $request, OrderService $services)
    {
        $this->request = $request;
        $this->services = $services;
    }

    /**
     * FunctionName：list
     * Description：列表
     * Author：cherish
     * @return mixed
     */
    public function list()
    {
        $page = $this->request->input('page') ?? 1;
        $pageSize = $this->request->input('pageSize') ?? 10;
        $order = new Order();
        if ($this->request->input('subject')) {
            $order = $order->where('subject', 'like', "%" . $this->request->input('subject') . "%");
        }
        if ($this->request->input('word_number')) {
            $order = $order->where('word_number', $this->request->input('word_number'));
        }
        if ($this->request->input('task_type')) {
            $order = $order->where('task_type', '=', $this->request->input('task_type'));
        }
        if ($this->request->input('id')) {
            $order = $order->where('id', '=', $this->request->input('id'));
        }
        if ($this->request->input('name')) {
            $order = $order->where('name', 'like', "%" . $this->request->input('name') . "%");
        }
        if ($this->request->input('staff_name')) {
            $order = $order->where('staff_name', 'like', "%" . $this->request->input('staff_name') . "%");
        }
        if ($this->request->input('edit_name')) {
            $order = $order->where('edit_name', 'like', "%" . $this->request->input('edit_name') . "%");
        }
        if ($this->request->input('submission_time')) {
            $order = $order->where('submission_time', '=', $this->request->input('submission_time'));
        }
        if ($this->request->input('status')) {
            $order = $order->where('status', '=', $this->request->input('status'));
        }
        if ($this->request->input('created_at')) {
            $order = $order->where('created_at', '=', $this->request->input('created_at'));
        }
        return $order->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], "page", $page);
    }

    /**
     * FunctionName：personalDetail
     * Description：用户详情
     * Author：cherish
     * @return mixed
     */
    public function detail()
    {
        $this->request->validate([
            'id' => ['required', 'exists:' . (new Order())->getTable() . ',id'],
        ]);
        return Order::find($this->request->input('id'));
    }

    /**
     * FunctionName：add
     * Description：创建
     * Author：cherish
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function add()
    {
        $this->request->validate([
            'subject' => ['required'],
            'word_number' => 'required',
            'task_type' => 'required',
            'task_ask' => 'required',
            'name' => 'required',
            'submission_time' => 'required',
        ]);
        $data = $this->request->input();
        $data['staff_name'] = Auth::user()->name;
        return Order::create($data);
    }

    /**
     * FunctionName：add
     * Description：创建
     * Author：cherish
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model
     */
    public function update()
    {
        $this->request->validate([
            'id' => ['required', 'exists:' . (new Order())->getTable() . ',id'],
            'subject' => ['required'],
            'word_number' => 'required',
            'task_type' => 'required',
            'task_ask' => 'required',
            'name' => 'required',
            'submission_time' => 'required',
        ]);
        $data = self::initData($this->request->input());
        return Order::where('id', $this->request->input('id'))->Update($data);
    }

    /**
     * FunctionName：statistics
     * Description：统计
     * Author：cherish
     * @return mixed
     */
    public function statistics()
    {
        $order = new Order();
        $user = \Auth::user();
        if ($user->roles->pluck('alias')[0] == 'staff') {
            $order = $order->where('staff_name', $user['name']);
        }
        $data['amount_count'] = $order->sum('amount');
        $data['received_amount_count'] = $order->sum('received_amount');
        $data['month_amount_count'] = $order->whereBetween('created_at', [date('Y-m-01'), date('Y-m-t')])->sum('amount');
        $data['month_received_amount_count'] = $order->whereBetween('created_at', [date('Y-m-01'), date('Y-m-t')])->sum('received_amount');
        return $data;
    }

    /**
     * FunctionName：count_num
     * Description：统计字数
     * Author：cherish
     * @return mixed
     */
    public function count_num()
    {
        $order = new Order();
        $user = \Auth::user();
        if ($user->roles->pluck('alias')[0] == 'edit') {
            $order = $order->where('edit_name', $user['name']);
        }
        $data['count_num'] = $order->sum('word_number');
        return $data;
    }

    /**
     * FunctionName：status
     * Description：修改状态
     * Author：cherish
     * @return mixed
     */
    public function status()
    {
        $this->request->validate([
            'id' => ['required', 'exists:' . (new Order())->getTable() . ',id'],
            'status' => ['required'],
        ]);
        $data = ['status' => $this->request->input('status')];
        if ($this->request->input('manuscript')) {
            $data['manuscript'] = $this->request->input('manuscript');
        }
        if ($this->request->input('submission_time')) {
            $data['submission_time'] = $this->request->input('submission_time');
        }
        return Order::where('id', $this->request->input('id'))->Update($data);
    }

    /**
     * FunctionName：manuscript
     * Description：上传稿件
     * Author：cherish
     * @return mixed
     */
    public function manuscript()
    {
        $this->request->validate([
            'id' => ['required', 'exists:' . (new Order())->getTable() . ',id'],
            'manuscript' => ['required'],
        ]);
        return Order::where('id', $this->request->input('id'))->Update(['manuscript' => $this->request->input('manuscript'), "status" => 5]);
    }

    /**
     * FunctionName：editName
     * Description：分配编辑
     * Author：cherish
     * @return mixed
     */
    public function editName()
    {
        $this->request->validate([
            'id' => ['required', 'exists:' . (new Order())->getTable() . ',id'],
            'edit_name' => ['required'],
        ]);
        return Order::where('id', $this->request->input('id'))->Update(['edit_name' => $this->request->input('edit_name'), "status" => 1]);
    }


    /**
     * FunctionName：initData
     * Description：初始化数据
     * Author：cherish
     * @param $data
     * @return array
     */
    public function initData($data)
    {
        $initData = [
            'subject' => $data['subject'],
            'word_number' => $data['word_number'],
            'task_type' => $data['task_type'],
            'task_ask' => $data['task_ask'],
            'name' => $data['name'],
            'submission_time' => $data['submission_time'],
            'phone' => $data['phone'] ?? '',
            'want_name' => $data['want_name'] ?? '',
            'amount' => $data['amount'] ?? 0,
            'received_amount' => $data['received_amount'] ?? 0,
            'pay_img' => $data['pay_img'] ?? '',
            'pay_type' => $data['pay_type'] ?? '',
            'detail_re' => $data['detail_re'] ?? '',
            'remark' => $data['remark'] ?? '',
//            'wr_where' => $data['wr_where']
        ];
        return $initData;
    }
}
