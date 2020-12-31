<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use App\Http\Model\Role;
use Illuminate\Http\Request;

class RoleControllers extends Controller
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
    public function list()
    {
        $page = $this->request->input('page') ?? 1;
        $pageSize = $this->request->input('pageSize') ?? 10;
        $role = Role::where("guard_name", "admin");
        if ($this->request->input('name')) {
            $role = $role->where('name', 'like', "%" . $this->request->input('name') . "%");
        }
        return $role->paginate($pageSize, ['*'], "page", $page);
    }
}
