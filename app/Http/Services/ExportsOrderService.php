<?php


namespace App\Http\Services;


use App\Http\Constants\BaseConstants;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportsOrderService implements FromCollection, WithHeadings, WithStyles
{
    use Exportable;

    private $data;

    public function __construct($result)
    {
        $this->setData($result);
    }

    public function headings(): array
    {
        return ["id", "任务类型", "题目", "字数", "截止时间", "订单总额", "已收金额", "首款", "首款付款时间", "二次收款时间", "二次收款金额", "尾款金额", "尾款收款时间", "财务审核", "付款截图", "支付方式", "详细要求", "客服名称", "编辑名称","售后人员","难度等级",
            "备注", "稿件下载", "状态", "创建时间"];
    }


    public function collection()
    {
        return collect($this->data);
    }

    public function styles(Worksheet $sheet)
    {
    }

    public function defaultStyle(Worksheet $sheet)
    {
        $sheet->getDefaultRowDimension()->setRowHeight(35);//设置默认行高
        $sheet->getDefaultColumnDimension()->setWidth(12);//设置默认的
        $sheet->getStyle('A1:H' . $this->row)->getAlignment()->setWrapText(true);
        $sheet->getStyle('A1:H' . $this->row)->getAlignment()->setVertical('center');//设置第一行垂直居中
        $sheet->getStyle("A1:H" . $this->row)->getAlignment()->setHorizontal('center');//设置垂直居中
        $styles = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ];
        $sheet->getStyle('A1:h' . $this->row)->applyFromArray($styles);
    }


    private function setData($result)
    {
        $this->data = [];
        foreach ($result as $key => $v) {
            array_push($this->data, [
                $v['id'],
                BaseConstants::TASKTYPE[$v['task_type']],
                $v['subject'],
                $v['word_number'],
                $v['submission_time'],
                $v['amount'],
                $v['received_amount'] + $v['twice_received_amount'] + $v['end_received_amount'],
                $v['received_amount'],
                $v['receipt_time'],
                $v['twice_received_amount'],
                $v['twice_time'],
                $v['end_received_amount'],
                $v['end_time'],
                BaseConstants::FINANCE_STATUS[$v['finance_check']] ?? "",
                $v['pay_img'],
                BaseConstants::ORDERPAYTYPE[$v['pay_type']] ?? "",
                $v['detail_re'],
                $v['staff_name'],
                $v['edit_name'],
                $v['after_name'],
                $v['hard_grade'],
                $v['remark'],
                $v['manuscript'],
                BaseConstants::ORDERSTARTLIST[$v['status']],
                $v['created_at']
            ]);
        }

    }
}
