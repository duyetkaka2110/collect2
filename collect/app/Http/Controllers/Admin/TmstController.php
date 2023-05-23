<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\InsertUpdateController;

class TmstController extends Controller
{
    protected $mmst = "pldtcustmmst as mmst"; //取引先マスタ
    protected $tmst = "pldtcusttmst as tmst"; //取引先担当者マスタ

    /**
     * 取引先担当者マスタ
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
            $list = DB::table($this->tmst)->select("tmst.*", "mmst.CUSTNM")
                ->selectRaw("DATE_FORMAT(tmst.FUPTIM, '%Y/%m/%d %H:%i') AS FUPTIM")
                ->selectRaw("DATE_FORMAT(tmst.LUPTIM, '%Y/%m/%d %H:%i') AS LUPTIM")
                ->join($this->mmst, "mmst.CUSTCD", "tmst.CUSTCD");

            //検索条件を追加
            $list = $this->_getSearch($list, $rq);

            $list = $list->orderBy("mmst.OUTPOS")
                ->orderBy("mmst.CUSTCD")
                ->orderBy("tmst.OUTPOS")
                ->orderBy("tmst.TCSTNO")
                ->paginate(20);
        }
        session()->flashInput($rq->input());
        $titleheader = "取引先担当者マスタ";

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
            "TCSTNO" => [
                "name" => "担当者番号",
                "width" => "w-4em",
                "align" => "text-right",
                "key" => 0
            ],
            "TCSIKJ" => [
                "name" => "担当者氏名(姓)",
                "width" => "w-4em",
                "align" => "",
                "key" => 0
            ],
            "TCMEKJ" => [
                "name" => "担当者氏名(名)",
                "width" => "w-4em",
                "align" => "",
                "key" => 0
            ],
            "TCSIKN" => [
                "name" => "担当者氏名カナ<br>(姓)",
                "width" => "w-4em",
                "align" => "",
                "key" => 0
            ],
            "TCMEKN" => [
                "name" => "担当者氏名カナ<br>(名)",
                "width" => "w-4em",
                "align" => "",
                "key" => 0
            ],
            "TCNMEN" => [
                "name" => "担当者英名",
                "width" => "w-4em",
                "align" => "",
                "key" => 0
            ],
            "CSTSPN" => [
                "name" => "所属部署名",
                "width" => "mw-13em",
                "align" => "",
                "key" => 0
            ],
            "CSTHPN" => [
                "name" => "役職名",
                "width" => "w-4em",
                "align" => "",
                "key" => 0
            ],
            "CSKSCD" => [
                "name" => "敬称<br>コード",
                "width" => "w-4em",
                "align" => "text-center",
                "key" => 0
            ],
            "RTELNO" => [
                "name" => "連絡先(電話番号)",
                "width" => "w-4em",
                "align" => "text-center",
                "key" => 0
            ],
            "KTELNO" => [
                "name" => "連絡先(携帯電話)",
                "width" => "w-4em",
                "align" => "text-center",
                "key" => 0
            ],
            "RFAXNO" => [
                "name" => "連絡先<br>(ファックス番号)",
                "width" => "w-4em",
                "align" => "text-center",
                "key" => 0
            ],
            "RZIPNO" => [
                "name" => "連絡先(郵便番号)",
                "width" => "w-4em",
                "align" => "text-center",
                "key" => 0
            ],
            "ZADRES" => [
                "name" => "連絡先(住所)",
                "width" => "w-4em",
                "align" => "",
                "key" => 0
            ],
            "MLADRS" => [
                "name" => "連絡先(メールアドレス）",
                "width" => "w-4em",
                "align" => "text-center",
                "key" => 0
            ],
            "MEMONM" => [
                "name" => "メモ",
                "width" => "mw-4em",
                "align" => "",
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
            $list = InsertUpdateController::getWhereLike($list, $rq->CUSTNM, "mmst.CUSTNM");
        }

        // 有効期間
        if ($rq->filled("SRTDAY") && $rq->SRTDAY) {
            $list = $list->where("tmst.ENDDAY", ">=",  \Carbon\Carbon::parse($rq->SRTDAY)->format("Ymd"));
        }
        if ($rq->filled("ENDDAY") && $rq->ENDDAY) {
            $list = $list->where("tmst.SRTDAY", "<=",  \Carbon\Carbon::parse($rq->ENDDAY)->format("Ymd"));
        }


        return $list;
    }
}
