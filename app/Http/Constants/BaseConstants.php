<?php


namespace App\Http\Constants;


interface BaseConstants
{
    const METAL = [
        1 => "自理",
        2 => "含早",
        3 => "含中",
        4 => "含晚",
        5 => "早中",
        6 => "早晚",
        7 => "中晚",
        8 => "早中晚"
    ];
    const STAY = [
        1 => "自理",
        2 => "行程安排"
    ];

    const TASKTYPE = [
        1 => "论文写作",
        2 => "期刊发表",
        3 => "ppt",
        4 => "翻译",
        5 => "其他",
        6 => "论文辅导"
    ];

    const ORDERPAYTYPE = [
        1 => "支付宝",
        2 => "微信",
        3 => "银行转账",
        4 => "对公账户",
        5 => "线上支付"
    ];

    const ORDERSTARTLIST = [
        -1 => "等待安排",
        1 => "写作中",
        2 => "打回修改",
        3 => "订单完成",
        4 => "提交客户",
        5 => "已交稿",
        6 => "售后中",
        7 => "售后完成"
    ];

    const FINANCE_STATUS = [
        -1 => "否",
        0 => "初审",
        1 => "终审",
        2 => "中审",
    ];
}
