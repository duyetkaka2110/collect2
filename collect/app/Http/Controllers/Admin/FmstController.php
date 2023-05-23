<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Http\Controllers\InsertUpdateController;

class FmstController extends Controller
{
    protected $fmst = "pldtbrinfmst as fmst"; //商品マスタ

    /**
     * 商品マスタ画面 
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
            $list = DB::table($this->fmst)->select("*")
                ->selectRaw("DATE_FORMAT(LUPTIM, '%Y/%m/%d %H:%i') AS LUPTIM");

            //検索条件を追加
            $list = $this->_getSearch($list, $rq);

            $list = $list->orderBy("BRNDCD")
                ->paginate(20);
        }
        session()->flashInput($rq->input());
        $titleheader = "商品マスタ";

        // 一覧のフォーマット表示
        $format = $this->_getTableFormat();

        $searchkey = [
            "key" => "BRNDNM",
            "name" => "銘柄名称"
        ];
        return view("admin.show.index", compact("titleheader", "list", "format", "searchkey"));
    }

    /**
     * 一覧のフォーマット表示
     * @return  配列
     */
    private function _getTableFormat()
    {
        return [
            "BRNDCD" => [
                "name" => "商品（銘柄）<br>コード",
                "width" => "w-6em",
                "align" => "text-center",
                "key" => 1
            ],
            "BRNDNM" => [
                "name" => "商品名（銘柄名称）",
                "width" => "mw-12em",
                "align" => "",
                "key" => 0
            ],
            "ITEMCD" => [
                "name" => "銘柄コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],

            "STYLCD" => [
                "name" => "荷姿コード",
                "width" => "w-em",
                "align" => "text-center",
                "key" => 0
            ],

            "INOTFG" => [
                "name" => "受け入れ<br>フラグ",
                "width" => "w-em",
                "align" => "text-right",
                "key" => 0
            ],

            "GROPCD" => [
                "name" => "商品分類<br>組み合わせコード",
                "width" => "w-em",
                "align" => "text-right",
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
        if ($rq->filled("BRNDNM")) {
            $list = InsertUpdateController::getWhereLike($list, $rq->BRNDNM, "BRNDNM");
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
