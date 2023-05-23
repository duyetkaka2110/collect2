<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\InsertUpdateController;

class KmstController extends Controller
{
    protected $mmst = "pldtcustmmst as mmst"; //取引先マスタ
    protected $kmst = "pldtcustkmst as kmst"; //取引先単価マスタ
    protected $fmst = "pldtbrinfmst as fmst"; //商品マスタ

    /**
     * 取引先単価マスタ画面
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
            $list = DB::table($this->kmst)->select("kmst.*", "mmst.CUSTNM","fmst.BRNDNM")
                ->selectRaw("DATE_FORMAT(kmst.FUPTIM, '%Y/%m/%d %H:%i') AS FUPTIM")
                ->selectRaw("DATE_FORMAT(kmst.LUPTIM, '%Y/%m/%d %H:%i') AS LUPTIM")
                ->join($this->mmst, "mmst.CUSTCD", "kmst.CUSTCD")
                ->join($this->fmst, "fmst.BRNDCD", "kmst.BRNDCD");

            //検索条件を追加
            $list = $this->_getSearch($list, $rq);

            $list = $list->orderBy("mmst.OUTPOS")
                ->orderBy("mmst.CUSTCD")
                ->orderBy("kmst.OUTPOS")
                ->paginate(20);
        }
        session()->flashInput($rq->input());
        $titleheader = "取引先単価マスタ";

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
            "CUSTCD" => [
                "name" => "取引先コード",
                "width" => "w-6em",
                "align" => "text-center",
                "key" => 1
            ],
            "CUSTNM" => [
                "name" => "取引先名称",
                "width" => "mw-13em",
                "align" => ""
            ],
            "BRNDKB" => [
                "name" => "銘柄<br>区分",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "BRNDCD" => [
                "name" => "銘柄コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "BRNDNM" => [
                "name" => "銘柄名称",
                "width" => "mw-10em",
                "align" => "",
                "key" => 0
            ],
            "TRFMCD" => [
                "name" => "取引形態<br>コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "AGRMNM" => [
                "name" => "件名",
                "width" => "mw-15em",
                "align" => "",
                "key" => 0
            ],
            "AGMEMO" => [
                "name" => "契約内容",
                "width" => "w-em",
                "align" => "",
                "key" => 0
            ],
            "UTPRKG" => [
                "name" => "単価金額",
                "width" => "w-em",
                "align" => "text-right",
                "key" => 0
            ],
            "CSUTCD" => [
                "name" => "単位<br>コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "TRKBCD" => [
                "name" => "取引区分<br>コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "OUTPOS" => [
                "name" => "表示順位",
                "width" => "w-em",
                "align" => "text-right",
                "key" => 0
            ],
            "CLIMCD" => [
                "name" => "請求分類<br>コード",
                "width" => "w-em",
                "align" => "",
                "key" => 0
            ],
            "UCSTCD" => [
                "name" => "上位取引先<br>コード",
                "width" => "w-em",
                "align" => "",
                "key" => 0
            ],
            "BIKONM" => [
                "name" => "備考欄",
                "width" => "mw-8em",
                "align" => "",
                "key" => 0
            ],
            "SRTDAY" => [
                "name" => "有効期間<br>(開始日)",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "ENDDAY" => [
                "name" => "有効期間<br>(終了日)",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "FUSRCD" => [
                "name" => "登録者コード",
                "width" => "w-em",
                "align" => "",
                "key" => 0
            ],
            "FUPTIM" => [
                "name" => "登録日時",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "LUSRCD" => [
                "name" => "最終更新者<br>コード",
                "width" => "w-em",
                "align" => "",
                "key" => 0
            ],
            "LUPTIM" => [
                "name" => "最終更新日時",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
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
            $list = $list->where("kmst.ENDDAY", ">=",  \Carbon\Carbon::parse($rq->SRTDAY)->format("Ymd"));
        }
        if ($rq->filled("ENDDAY") && $rq->ENDDAY) {
            $list = $list->where("kmst.SRTDAY", "<=",  \Carbon\Carbon::parse($rq->ENDDAY)->format("Ymd"));
        }


        return $list;
    }
}
