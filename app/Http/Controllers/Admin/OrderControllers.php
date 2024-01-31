<?php


namespace App\Http\Controllers\Admin;


use App\Http\Constants\CodeMessageConstants;
use App\Http\Controllers\Controller;
use App\Http\Model\Classify;
use App\Http\Model\Department;
use App\Http\Model\Order;
use App\Http\Model\OrderLogs;
use App\Http\Model\User;
use App\Http\Services\ExportsOrderService;
use App\Http\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class OrderControllers extends Controller
{
    public function __construct(Request $request, OrderService $services)
    {
        $this->request = $request;
        $this->services = $services;
    }

    /**
     * FunctionName：getInitSelect
     * Description：根据角色和部门获取权限
     * User: cherish
     * @param $order
     * @return mixed|void
     */
    public function getInitSelect($order)
    {
        $user = \Auth::user();
        $department_id = $user->department_id;
        //查询当前部门信息
        $department = Department::where('id', $department_id)->first();
        //查询用户当前角色
        $role = $user->roles->pluck('alias')->toArray();
        if (in_array('admin', $role)) {
            return $order;
        } elseif (in_array('after_admin', $role)) {
            $order = $order->whereNotNull('after_name');
            if ($department) {
                if ($department['level'] == 3) {
                    $userName = User::where('department_id', $department['id'])->pluck('name');
                    $parentDepartment = Department::where("id", $department['parent_id'])->first();
                    if ($userName->count()) {
                        if ($parentDepartment['alias'] == "staff")
                            $order = $order->whereIn('staff_name', $userName->toArray());
                        if ($parentDepartment['alias'] == "edit")
                            $order = $order->whereIn('edit_name', $userName->toArray());
                        if ($parentDepartment['alias'] == "after")
                            $order = $order->whereIn('after_name', $userName->toArray());
                    } else {
                        $order = $order->whereNull('staff_name');
                    }
                }
            }
        } elseif (in_array('staff_admin', $role)) {
            $order = $order->whereNotNull('staff_name');
            if ($department) {
                if ($department['level'] == 3) {
                    $userName = User::where('department_id', $department['id'])->pluck('name');
                    $parentDepartment = Department::where("id", $department['parent_id'])->first();
                    if ($userName->count()) {
                        if ($parentDepartment['alias'] == "staff")
                            $order = $order->whereIn('staff_name', $userName->toArray());
                        if ($parentDepartment['alias'] == "edit")
                            $order = $order->whereIn('edit_name', $userName->toArray());
                        if ($parentDepartment['alias'] == "after")
                            $order = $order->whereIn('after_name', $userName->toArray());
                    } else {
                        $order = $order->whereNull('staff_name');
                    }
                }
            }
        } elseif (in_array('edit_admin', $role)) {
           // $order = $order->whereNotNull('edit_name');
            if ($department) {
                if ($department['level'] == 3) {
                    $userName = User::where('department_id', $department['id'])->pluck('name');
                    $parentDepartment = Department::where("id", $department['parent_id'])->first();
                    if ($userName->count()) {
                        if ($parentDepartment['alias'] == "staff")
                            $order = $order->whereIn('staff_name', $userName->toArray());
                        if ($parentDepartment['alias'] == "edit")
                            $order = $order->whereIn('edit_name', $userName->toArray());
                        if ($parentDepartment['alias'] == "after")
                            $order = $order->whereIn('after_name', $userName->toArray());
                    } else {
                        $order = $order->whereNull('staff_name');
                    }
                }
            }
        } elseif (!in_array('after_admin', $role) && in_array('after', $role)) {
            $order = $order->where('after_name', $user->name);
        } elseif (!in_array('edit_admin', $role) && in_array('edit', $role)) {
            $order = $order->where('edit_name', $user->name);
        } elseif (!in_array('staff_admin', $role) && in_array('staff', $role)) {
            $order = $order->where('staff_name', $user->name);
        }
        return $order;
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
        $order = $this->getInitSelect($order);
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
        if ($this->request->input('classify_id')) {
            $order = $order->where('classify_id', 'like', "%" . $this->request->input('classify_id') . "%");
        }
        if ($this->request->input('name')) {
            $order = $order->where('name', 'like', "%" . $this->request->input('name') . "%");
        }
        if ($this->request->input('specialty')) {
            $order = $order->where('specialty', 'like', "%" . $this->request->input('specialty') . "%");
        }
        if ($this->request->input('staff_name')) {
            $order = $order->where('staff_name', 'like', "%" . $this->request->input('staff_name') . "%");
        }
        if ($this->request->input('edit_name')) {
            $order = $order->where('edit_name', 'like', "%" . $this->request->input('edit_name') . "%");
        }
        if ($this->request->input('task_ask')) {
            $order = $order->where('task_ask', 'like', "%" . $this->request->input('task_ask') . "%");
        }
        if ($this->request->input('after_name')) {
            $order = $order->where('after_name', 'like', "%" . $this->request->input('after_name') . "%");
        }
        if ($this->request->input('submission_end_time')) {
            $order = $order->whereDate('submission_time', '<=', $this->request->input('submission_end_time'))->whereDate('submission_time', '>=', $this->request->input('submission_time'));
        }
        if ($this->request->input('status')) {
            $order = $order->where('status', '=', $this->request->input('status'));
        }

        if ($this->request->input('finance_check')) {
            $order = $order->where('finance_check', '=', $this->request->input('finance_check'));
        }
        $dataaa = $this->request->input();
        if (isset($dataaa['finance_check'])) {
            if ($dataaa['finance_check'] == 0)
                $order = $order->where('finance_check', 0);
        }

        if ($this->request->input('receipt_account_type')) {
            $order = $order->where('receipt_account_type', '=', $this->request->input('receipt_account_type'));
        }

//        if ($this->request->input('created_at')) {
//            $order = $order->where('created_at', 'like', "%" . $this->request->input('created_at') . "%");
//        }
        if ($this->request->input('end_time')) {
            $order = $order->whereDate('created_at', '<=', $this->request->input('end_time'))->whereDate('created_at', '>=', $this->request->input('created_at'));
        }
        $order = $order->orderBy('created_at', 'desc')->with('classify')->paginate($pageSize, ['*'], "page", $page);
        if ($order->items()) {
            foreach ($order->items() as $values) {
                $values['received_all'] = $values['received_amount'] + $values['twice_received_amount'] + $values['end_received_amount'];
            }
        }
        return $order;
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
     * FunctionName：delete
     * Description：删除
     * Author：cherish
     * @return bool|null
     * @throws \Exception
     */
    public function delete()
    {
        $this->request->validate([
            'id' => ['required', 'exists:' . (new Order())->getTable() . ',id'],
        ]);
        return Order::where('id', $this->request->input('id'))->delete();
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
        if (isset($data['classify_id'])) {
            $data['classify_local_id'] = (new ManuscriptBankControllers())->getClassifyId($data['classify_id']);
            $data['classify_id'] = implode(",", $data['classify_id']);
        } else {
            $data['classify_local_id'] = null;
            $data['classify_id'] = null;
        }

        if (isset($data['pay_img'])) {
            $data['finance_check'] = 0;
        }

        if (isset($data['twice_img'])) {
            $data['finance_check'] = 2;
        }
        if (isset($data['receipt_account'])) {
            $data['finance_check'] = 1;
        }
        return Order::create($data);
    }

    /**
     * FunctionName：add
     * Description：更新
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
        if (isset($data['classify_id'])) {
            $data['classify_local_id'] = (new ManuscriptBankControllers())->getClassifyId($data['classify_id']);
            $data['classify_id'] = implode(",", $data['classify_id']);
        } else {
            $data['classify_local_id'] = null;
            $data['classify_id'] = null;
        }
        $img = $this->request->input();
        if (isset($img['pay_img'])) {
            $data['finance_check'] = 0;
        }

        if (isset($img['twice_img'])) {
            $data['finance_check'] = 2;
        }
        if (isset($img['receipt_account'])) {
            $data['finance_check'] = 1;
        }
        return Order::where('id', $this->request->input('id'))->Update($data);
    }

    /**
     * FunctionName：after
     * Description：售后
     * Author：cherish
     * @return mixed
     */
    public function after()
    {
        $this->request->validate([
            'id' => ['required', 'exists:' . (new Order())->getTable() . ',id'],
            'after_banlace' => ['required'],
        ]);
        $data = $this->request->input();
        $data['after_time'] = date("Y-m-d H:i:s");
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

        $year = date('Y');
        $start_time = $year . '-01-01 00:00:00';
        $end_time = $year . '-12-31 23:59:59';
        $data['amount_count'] = $order->whereDate('created_at', '>=', $start_time)->whereDate('created_at', '<=', $end_time)->sum('amount');
        $received_amount_all = $order->whereDate('created_at', '>=', $start_time)->whereDate('created_at', '<=', $end_time)->sum('received_amount');
        $twice_received_amount_all = $order->whereDate('created_at', '>=', $start_time)->whereDate('created_at', '<=', $end_time)->sum('twice_received_amount');
        $data['received_amount_count'] = $received_amount_all + $twice_received_amount_all;
        $data['month_amount_count'] = $order->whereDate('created_at', '<=', date('Y-m-t'))->whereDate('created_at', '>=', date('Y-m-01'))->sum('amount');
        // $data['month_amount_count'] = $order->whereBetween('created_at', [date('Y-m-01'), date('Y-m-t')])->sum('amount');
        // $data['month_received_amount_count'] = $order->whereBetween('created_at', [date('Y-m-01'), date('Y-m-t')])->sum('received_amount');
        $received_amount_month = $order->whereDate('created_at', '<=', date('Y-m-t'))->whereDate('created_at', '>=', date('Y-m-01'))->sum('received_amount');
        $twice_received_amount_month = $order->whereDate('created_at', '<=', date('Y-m-t'))->whereDate('created_at', '>=', date('Y-m-01'))->sum('twice_received_amount');
        $data['month_received_amount_count'] = $received_amount_month + $twice_received_amount_month;
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
        $order = Order::find($this->request->input('id'));
        $orderLogs = [];
        $orderLogs['order_id'] = $this->request->input('id');
        $orderLogs['remark'] = $this->statusReplace(\Auth::user()->name, $order['status'], $this->request['status']);
        if ($this->request->input('manuscript')) {
            $data['manuscript'] = $this->request->input('manuscript');
            $orderLogs['url'] = $this->request->input('manuscript');
        }

        if ($this->request->input('reason')) {
            $orderLogs['reason'] = $this->request->input('reason');
        }
        if ($this->request->input('submission_time')) {
            $data['submission_time'] = $this->request->input('submission_time');
            $orderLogs['remark'] = $orderLogs['remark'] . ",将完成时间" . $order['submission_time'] . "修改为" . $this->request->input('submission_time');
        }
        return DB::transaction(function () use ($data, $orderLogs) {
            OrderLogs::create($orderLogs);
            return Order::where('id', $this->request->input('id'))->Update($data);
        });

    }


    public function statusReplace($name, $historyStarus, $status)
    {
        $data = [
            '-1' => '等待安排',
            '1' => '写作中',
            '2' => '打回修改',
            '3' => '订单完成',
            '4' => '提交客户',
            '5' => "已经交稿",
            '6' => "售后中",
            '7' => "售后完成"

        ];
        return $name . ",将订单状态" . $data[$historyStarus] . "修改为" . $data[$status];
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
//            "alter_word" => ['required'],
//            "classify_id" => ['required']
        ]);
        $order = Order::find($this->request->input('id'));
        $alter_word = $this->request->input('alter_word') ?? $order['alter_word'];
        $orderLogs['remark'] = $this->statusReplace(\Auth::user()->name, $order['status'], 5);
        $orderLogs['url'] = $this->request->input('manuscript');
        $orderLogs['order_id'] = $this->request->input('id');
        $data = $this->request->input();
        if (isset($data['classify_id'])) {
            $classify_local_id = (new ManuscriptBankControllers())->getClassifyId($this->request->input('classify_id'));
            $classify_id = implode(",", $this->request->input('classify_id'));
        } else {
            $classify_local_id = null;
            $classify_id = null;
        }
        return DB::transaction(function () use ($orderLogs, $alter_word, $classify_id, $classify_local_id) {
            OrderLogs::create($orderLogs);
            return Order::where('id', $this->request->input('id'))->Update(['manuscript' => $this->request->input('manuscript'), "status" => 5, 'alter_word' => $alter_word, 'classify_id' => $classify_id, 'classify_local_id' => $classify_local_id, 'edit_submit_time' => date("Y-m-d H:i:s")]);
        });
    }

    public function logs()
    {
        $this->request->validate([
            'id' => ['required', 'exists:' . (new Order())->getTable() . ',id'],
        ]);
        $page = $this->request->input('page') ?? 1;
        $pageSize = $this->request->input('pageSize') ?? 10;
        $orderLogs = new OrderLogs();
        $orderLogs = $orderLogs->where('order_id', "=", $this->request->input('id'));
        return $orderLogs->orderBy('created_at', 'desc')->paginate($pageSize, ['*'], "page", $page);
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
//            'after_name' => ['required'],
        ]);
        return Order::where('id', $this->request->input('id'))->Update(['edit_name' => $this->request->input('edit_name'), "status" => 1]);
    }

    /**
     * FunctionName：after_name
     * Description：分配售后
     * User: cherish
     * @return mixed
     */
    public function afterName()
    {
        $this->request->validate([
            'id' => ['required', 'exists:' . (new Order())->getTable() . ',id'],
            'after_name' => ['required'],
        ]);
        return Order::where('id', $this->request->input('id'))->Update(['after_name' => $this->request->input('after_name'), "status" => 1]);
    }

    /**
     * FunctionName：grade
     * Description：更新难度等级
     * Author：cherish
     * @return mixed
     */
    public function grade()
    {
        $this->request->validate([
            'id' => ['required', 'exists:' . (new Order())->getTable() . ',id'],
            'hard_grade' => ['required'],
        ]);
        return Order::where('id', $this->request->input('id'))->Update(['hard_grade' => $this->request->input('hard_grade')]);
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
            //          'phone' => $data['phone'] ?? '',
            'want_name' => $data['want_name'] ?? '',
            'amount' => $data['amount'] ?? 0,
            'received_amount' => $data['received_amount'] ?? 0,
            'pay_img' => $data['pay_img'] ?? '',
            'pay_type' => $data['pay_type'] ?? '',
            'detail_re' => $data['detail_re'] ?? '',
            'receipt_time' => $data['receipt_time'] ?? '',
            'receipt_account' => $data['receipt_account'] ?? '',
            'remark' => $data['remark'] ?? '',
            'twice_received_amount' => $data['twice_received_amount'] ?? 0,
            'end_received_amount' => $data['end_received_amount'] ?? 0,
            'end_time' => $data['end_time'] ?? '',
            'twice_time' => $data['twice_time'] ?? '',
            'receipt_account_type' => $data['receipt_account_type'] ?? 1,
            "twice_img" => $data['twice_img'] ?? '',
            "specialty" => $data['specialty'] ?? ''
//            'wr_where' => $data['wr_where']
        ];
        return $initData;
    }


    public function export()
    {
        $order = new Order();
        $order = $this->getInitSelect($order);
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
        if ($this->request->input('classify_id')) {
            $order = $order->where('classify_id', 'like', "%" . $this->request->input('classify_id') . "%");
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
        if ($this->request->input('task_ask')) {
            $order = $order->where('task_ask', 'like', "%" . $this->request->input('task_ask') . "%");
        }
        if ($this->request->input('after_name')) {
            $order = $order->where('after_name', 'like', "%" . $this->request->input('after_name') . "%");
        }
        if ($this->request->input('submission_end_time')) {
            $order = $order->whereDate('submission_time', '<=', $this->request->input('submission_end_time'))->whereDate('submission_time', '>=', $this->request->input('submission_time'));
        }
        if ($this->request->input('status')) {
            $order = $order->where('status', '=', $this->request->input('status'));
        }

        if ($this->request->input('finance_check')) {
            $order = $order->where('finance_check', '=', $this->request->input('finance_check'));
        }
        if ($this->request->input('specialty')) {
            $order = $order->where('specialty', 'like', "%" . $this->request->input('specialty') . "%");
        }
        $dataaa = $this->request->input();
        if (isset($dataaa['finance_check'])) {
            if ($dataaa['finance_check'] == 0)
                $order = $order->where('finance_check', 0);
        }

        if ($this->request->input('receipt_account_type')) {
            $order = $order->where('receipt_account_type', '=', $this->request->input('receipt_account_type'));
        }

//        if ($this->request->input('created_at')) {
//            $order = $order->where('created_at', 'like', "%" . $this->request->input('created_at') . "%");
//        }
        if ($this->request->input('end_time')) {
            $order = $order->whereDate('created_at', '<=', $this->request->input('end_time'))->whereDate('created_at', '>=', $this->request->input('created_at'));
        }
        $data = $order->get();
        //  Log::debug("11", [ count($data)]);
        if (count($data) < 1)
            throw \ExceptionFactory::business(CodeMessageConstants::CHECK_ORDER_NULL);
        if (count($data) > 5000)
            throw \ExceptionFactory::business(CodeMessageConstants::CHECK_ORDER_NUM);
        $filename = '订单列表.xls';
        return Excel::download(new ExportsOrderService($data), $filename);
    }
}
