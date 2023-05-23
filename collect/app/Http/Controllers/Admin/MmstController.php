<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\InsertUpdateController;

class MmstController extends Controller
{
    protected $mmst = "pldtcustmmst as mmst"; //取引先マスタ

    /**
     * 取引先マスタ画面 
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
            $list = DB::table($this->mmst)->select("mmst.*")
            ->selectRaw("DATE_FORMAT(mmst.FUPTIM, '%Y/%m/%d %H:%i') AS FUPTIM")
            ->selectRaw("DATE_FORMAT(mmst.LUPTIM, '%Y/%m/%d %H:%i') AS LUPTIM");

            //検索条件を追加
            $list = $this->_getSearch($list, $rq);

            $list = $list->orderBy("mmst.OUTPOS")
                ->orderBy("mmst.CUSTCD")
                ->paginate(20);
        }
        session()->flashInput($rq->input());
        $titleheader = "取引先マスタ";

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
            "SRKBCD" => [
                "name" => "原料区分<br>コード",
                "width" => "w-4em",
                "align" => "text-center",
                "key" => 0
            ],
            "CUSTID" => [
                "name" => "取引先ID",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "SEGLVL" => [
                "name" => "階層<br>レベル",
                "width" => "w-3em",
                "align" => "text-center",
                "key" => 0
            ],
            "PCSTCD" => [
                "name" => "親取引先<br>コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "CUSTKB" => [
                "name" => "取引先<br>区分",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "CUSTNM" => [
                "name" => "取引先名称",
                "width" => "mw-13em",
                "align" => ""
            ],
            "CUSTRK" => [
                "name" => "取引先略名称",
                "width" => "mw-7em",
                "align" => "",
                "key" => 0
            ],
            "CUSTKN" => [
                "name" => "取引先名称カナ",
                "width" => "mw-8em",
                "align" => "",
                "key" => 0
            ],
            "CUSTEN" => [
                "name" => "取引先英名",
                "width" => "mw-7em",
                "align" => "",
                "key" => 0
            ],
            "TRKBCD" => [
                "name" => "取引区分<br>コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "INSFCD" => [
                "name" => "社内担当者<br>コード",
                "width" => "w-em",
                "align" => "",
                "key" => 0
            ],
            "CLCTCD" => [
                "name" => "回収方法<br>コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "CLCTNM" => [
                "name" => "回収方法名称",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "SGRPCD" => [
                "name" => "締日グループ<br>コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "CTAXCD" => [
                "name" => "消費税計算<br>コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],
            "MTELNO" => [
                "name" => "電話番号<br>(代表)",
                "width" => "mw-8em",
                "align" => "text-center",
                "key" => 0
            ],
            "MFAXNO" => [
                "name" => "ファックス<br>番号(代表)",
                "width" => "mw-8em",
                "align" => "text-center",
                "key" => 0
            ],

            "MZIPNO" => [
                "name" => "郵便番号",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],

            "MADRES" => [
                "name" => "住所",
                "width" => "mw-8em",
                "align" => "",
                "key" => 0
            ],

            "HPADRS" => [
                "name" => "ホームページアドレス",
                "width" => "w-em",
                "align" => "",
                "key" => 0
            ],

            "CBR1CD" => [
                "name" => "取引先分類１<br>コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],

            "CBR2CD" => [
                "name" => "取引先分類２<br>コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],

            "CBR3CD" => [
                "name" => "取引先分類３<br>コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],

            "SUMUKB" => [
                "name" => "集計区分<br>(受入)",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],

            "SUMSKB" => [
                "name" => "集計区分<br>(請求)",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],

            "SUMHKB" => [
                "name" => "集計区分<br>(報告)",
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
            $list = InsertUpdateController::getWhereLike($list, $rq->CUSTNM, "CUSTNM");
        }

        // 有効期間
        if ($rq->filled("SRTDAY") && $rq->SRTDAY) {
            $list = $list->where("ENDDAY", ">=",  \Carbon\Carbon::parse($rq->SRTDAY)->format("Ymd"));
        }
        if ($rq->filled("ENDDAY") && $rq->ENDDAY) {
            $list = $list->where("SRTDAY", "<=",  \Carbon\Carbon::parse($rq->ENDDAY)->format("Ymd"));
        }


        return $list;
    }

}
