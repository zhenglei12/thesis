<?php


namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UploadController extends Controller
{
    /**
     * FunctionName：qiniuAuth
     * Description：获取七牛云认证
     * Author：cherish
     * @return int
     */
    public function qiniuAuth(Request $request)
    {
        $request->validate([
            'disk' => ['required', Rule::in(['lywang'])]
        ]);
        $disk = $request->input('disk');
        $diskConnect = \Storage::disk($disk);
        $url = config('filesystems.disks.' . $disk . '.domains.default');
        return ['token' => $diskConnect->getDriver()->uploadToken(null, 3600, ['returnBody' => '{"key":"$(key)","bucket":"$(bucket)","url":"' . "http://" .$url . '"}']), 'token_type' => 'Bearer'];
    }
}
