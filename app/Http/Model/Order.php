<?php


namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'order';

    public $fillable = [
        'task_type',
        'subject',
        'word_number',
        'task_ask',
        'name',
        'phone',
        'want_name',
        'submission_time',
        'amount',
        'received_amount',
        'pay_img',
        'detail_re',
        'staff_name',
        'edit_name',
        'remark',
        'manuscript',
        'status',
        'pay_type'
    ];

    protected $casts = [
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }
}
