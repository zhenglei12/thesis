<?php


namespace App\Http\Model;


use Illuminate\Database\Eloquent\Model;

class OrderFrom extends Model
{
    protected $table = 'order_from';

    public $fillable = [
        'subject',
        'grade',
        'speciality',
        'use',
        'language',
        'manuscript_word',
        'repeat',
        'special_ask',
        'docu_number',
        'qq',
        'phone',
        'school_name',
        'tutor_name',
        "manuscript_time",
        'contact_way',
        'model_essay',
        'writing_ask',
        'order_id',
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
