<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\InsertUpdateController;

class RmstController extends Controller
{
    protected $mmst = "pldtcustmmst as mmst"; //取引先マスタ
    protected $rmst = "pldtpdcarmst as rmst"; //車両マスタ
    protected $vmst = "pldtpddrvmst as vmst"; //運転手マスタ

    /**
     * 車両マスタ画面
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
            $list = DB::table($this->rmst)->select("rmst.*", "mmst.CUSTNM","vmst.DVSIKJ")
                ->selectRaw("DATE_FORMAT(rmst.FUPTIM, '%Y/%m/%d %H:%i') AS FUPTIM")
                ->selectRaw("DATE_FORMAT(rmst.LUPTIM, '%Y/%m/%d %H:%i') AS LUPTIM")
                ->join($this->mmst, "mmst.CUSTCD", "rmst.PDCPCD")
                ->join($this->vmst, function($join){
                    $join->on("vmst.PDCPCD", "rmst.PDCPCD");
                    $join->on("vmst.PDRVCD", "rmst.PDRVCD");
                });

            //検索条件を追加
            $list = $this->_getSearch($list, $rq);

            $list = $list->orderBy("rmst.OUTPOS")
                ->orderBy("rmst.PCARNO")
                ->orderBy("rmst.CIDXNO")
                ->paginate(20);
        }
        session()->flashInput($rq->input());
        $titleheader = "車両マスタ";

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
            "PCARNO" => [
                "name" => "車両番号",
                "width" => "w-6em",
                "align" => "",
            ],
            "CIDXNO" => [
                "name" => "内訳番号",
                "width" => "w-6em",
                "align" => "text-center",
            ],
            "PDCPCD" => [
                "name" => "取引会社コード",
                "width" => "w-6em",
                "align" => "text-center",
            ],
            "CUSTNM" => [
                "name" => "取引先名称",
                "width" => "mw-12em",
                "align" => "",
            ],
            "PDRVCD" => [
                "name" => "運転手<br>コード",
                "width" => "w-6em",
                "align" => "text-center",
            ],
            "DVSIKJ" => [
                "name" => "運転手氏名(姓)",
                "width" => "mw-10em",
                "align" => "",
            ],
            "CARVAL" => [
                "name" => "車両重量",
                "width" => "w-6em",
                "align" => "text-right",
            ],
            "DKNFLG" => [
                "name" => "台貫回数<br>フラグ",
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
            $list = $list->where("rmst.ENDDAY", ">=",  \Carbon\Carbon::parse($rq->SRTDAY)->format("Ymd"));
        }
        if ($rq->filled("ENDDAY") && $rq->ENDDAY) {
            $list = $list->where("rmst.SRTDAY", "<=",  \Carbon\Carbon::parse($rq->ENDDAY)->format("Ymd"));
        }


        return $list;
    }
}
