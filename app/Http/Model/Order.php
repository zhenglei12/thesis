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
        'wr_where',
        'pay_type',
        "receipt_time",
        "receipt_account",
        "classify_id",
        'alter_word',
        'classify_local_id',
        'finance_check',
        'edit_submit_time',
        'after_banlace',
        'after_time',
        'hard_grade',
        "twice_received_amount",
        'end_received_amount',
        "twice_time",
        "end_time",
        "receipt_account_type",
        "twice_img",
        "specialty",
        "after_name"
    ];

    protected $casts = [
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'created_at' => 'datetime:Y-m-d H:i:s',
    ];

    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function classify()
    {
        return $this->hasOne(Classify::class, "id", "classify_local_id");
    }

}
