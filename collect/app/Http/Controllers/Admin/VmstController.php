<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\InsertUpdateController;

class VmstController extends Controller
{
    protected $mmst = "pldtcustmmst as mmst"; //取引先マスタ
    protected $vmst = "pldtpddrvmst as vmst"; //運転手マスタ

    /**
     * 運転手マスタ画面
     * @param  Request $rq
     * @return view
     */
    public function index(Request $rq)
    {
        $SRTDAY = \Carbon\Carbon::parse($rq->SRTDAY)->format("Ymd");
        $ENDDAY = \Carbon\Carbon::parse($rq->ENDDAY)->format("Ymd");
        if ($SRTDAY > $ENDDAY && $rq->SRTDAY && $rq->ENDDAY) {
            // ・From／Toが逆転していてもエラーとせず、検索件数0件で表示
            $list = [];
        } else { 
            $list = DB::table($this->vmst)->select("vmst.*", "mmst.CUSTNM")
                ->selectRaw("DATE_FORMAT(vmst.FUPTIM, '%Y/%m/%d %H:%i') AS FUPTIM")
                ->selectRaw("DATE_FORMAT(vmst.LUPTIM, '%Y/%m/%d %H:%i') AS LUPTIM")
                ->join($this->mmst, "mmst.CUSTCD", "vmst.PDCPCD");

            //検索条件を追加
            $list = $this->_getSearch($list, $rq);

            $list = $list->orderBy("mmst.OUTPOS")
                ->orderBy("mmst.CUSTCD")
                ->orderBy("vmst.OUTPOS")
                ->orderBy("vmst.PDRVCD")
                ->paginate(20);
        }
        session()->flashInput($rq->input());
        $titleheader = "運転手マスタ";

        // 一覧のフォーマット表示
        $format = $this->_getTableFormat();

        return view("admin.show.index", compact("titleheader", "list", "format"));
    }

    /**
     * 一覧のフォーマット表示
     * @return  配列
     */
    private function _getTableFormat()
    {
        return [
            "PDCPCD" => [
                "name" => "取引会社<br>コード",
                "width" => "w-6em",
                "align" => "text-center",
            ],
            "CUSTNM" => [
                "name" => "取引先名称",
                "width" => "mw-15em",
                "align" => "",
            ],
            "PDRVCD" => [
                "name" => "運転手<br>コード",
                "width" => "w-6em",
                "align" => "text-center",
            ],
            "DVSIKJ" => [
                "name" => "運転手氏名(姓)",
                "width" => "mw-8em",
                "align" => "",
            ],
            "DVMEKJ" => [
                "name" => "運転手氏名(名)",
                "width" => "mw-8em",
                "align" => "",
            ],
            "DVSIKN" => [
                "name" => "運転手氏名<br>カナ(姓)",
                "width" => "w-6em",
                "align" => "",
            ],
            "DVMEKN" => [
                "name" => "運転手氏名<br>カナ(名)",
                "width" => "w-6em",
                "align" => "",
            ],
            "RTELNO" => [
                "name" => "連絡先(電話番号)",
                "width" => "w-6em",
                "align" => "text-center",
            ],
            "KTELNO" => [
                "name" => "連絡先(携帯電話)",
                "width" => "w-6em",
                "align" => "text-center",
            ],
            "RFAXNO" => [
                "name" => "連絡先<br>(ファックス番号)",
                "width" => "w-6em",
                "align" => "text-center",
            ],
            "RZIPNO" => [
                "name" => "連絡先<br>(郵便番号)",
                "width" => "w-6em",
                "align" => "text-center",
            ],
            "ZADRES" => [
                "name" => "連絡先(住所)",
                "width" => "mw-10em",
                "align" => "",
            ],
            "MLADRS" => [
                "name" => "連絡先<br>(メールアドレス）",
                "width" => "w-6em",
                "align" => "",
            ],
            "AGMEMO" => [
                "name" => "契約内容",
                "width" => "w-6em",
                "align" => "",
            ],
            "UTPRKG" => [
                "name" => "単価金額",
                "width" => "w-6em",
                "align" => "text-right",
            ],
            "CSUTCD" => [
                "name" => "単位コード",
                "width" => "w-6em",
                "align" => "text-center",
            ],
            "TRKBCD" => [
                "name" => "取引区分<br>コード",
                "width" => "w-6em",
                "align" => "text-center",
            ],
            "OUTPOS" => [
                "name" => "表示順位",
                "width" => "w-em",
                "align" => "text-right",
            ],
            "BIKONM" => [
                "name" => "備考欄",
                "width" => "mw-8em",
                "align" => "",
            ],
            "SRTDAY" => [
                "name" => "有効期間<br>(開始日)",
                "width" => "w-em",
                "align" => "text-center",
            ],
            "ENDDAY" => [
                "name" => "有効期間<br>(終了日)",
                "width" => "w-em",
                "align" => "text-center",
            ],
            "FUSRCD" => [
                "name" => "登録者コード",
                "width" => "w-em",
                "align" => "",
            ],
            "FUPTIM" => [
                "name" => "登録日時",
                "width" => "w-em",
                "align" => "text-center",
            ],
            "LUSRCD" => [
                "name" => "最終更新者<br>コード",
                "width" => "w-em",
                "align" => "",
            ],
            "LUPTIM" => [
                "name" => "最終更新日時",
                "width" => "w-em",
                "align" => "text-center",
            ],
        ];
    }

    /**
     * 検索条件を追加
     * @param  $list 表示一覧 
     * @param  $rq input 
     * @return  表示一覧
     */
    private function _getSearch($list, Request $rq)
    {
        // 取引先名称
        if ($rq->filled("CUSTNM")) {
            $list = InsertUpdateController::getWhereLike($list, $rq->CUSTNM, "mmst.CUSTNM");
        }

        // 有効期間
        if ($rq->filled("SRTDAY") && $rq->SRTDAY) {
            $list = $list->where("vmst.ENDDAY", ">=",  \Carbon\Carbon::parse($rq->SRTDAY)->format("Ymd"));
        }
        if ($rq->filled("ENDDAY") && $rq->ENDDAY) {
            $list = $list->where("vmst.SRTDAY", "<=",  \Carbon\Carbon::parse($rq->ENDDAY)->format("Ymd"));
        }


        return $list;
    }
}
