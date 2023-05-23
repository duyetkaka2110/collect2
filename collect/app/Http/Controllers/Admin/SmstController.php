<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\GetMessage;
use App\Models\m_enums;
use App\Http\Controllers\InsertUpdateController;

class SmstController extends Controller
{ 
    protected $_mysystemname = "admin";
    protected $mmst = "pldtcustmmst as mmst"; //取引先マスタ
    protected $mmst2 = "pldtcustmmst as mmst2"; //取引先マスタ
    protected $fmst = "pldtbrinfmst as fmst"; //商品マスタ
    protected $MD = "m_material_divisions as MD"; //原料区分マスタ
    protected $smst = "pldtpdcosmst as smst"; //物流費マスタ

    /**
     * 物流費マスタ画面
     * @param  Request $rq
     * @return view
     */
    public function index(Request $rq)
    {
        $success = null;
        // 保存ボタンをクリックするとき
        if ($rq->filled("btnOk") && $rq->btnOk) {
            try {
                DB::beginTransaction();
                // データ保存
                $this->_saveData($rq);
                DB::commit();
                $success = str_replace("{p}", "保存", GetMessage::getMessageByID("error007"));
            } catch (Throwable $e) {
                DB::rollBack();
                $Errmsg = GetMessage::getMessageByID("error009");
                return redirect()->back()->withInput()->withErrors($Errmsg);
            }
        }
        $SRTDAY = \Carbon\Carbon::parse($rq->SRTDAY)->format("Ymd");
        $ENDDAY = \Carbon\Carbon::parse($rq->ENDDAY)->format("Ymd");
        if ($SRTDAY > $ENDDAY && $rq->SRTDAY && $rq->ENDDAY) {
            // ・From／Toが逆転していてもエラーとせず、検索件数0件で表示
            $list = [];
        } else {
            $list = DB::table($this->smst)->select("smst.*", "mmst.CUSTNM", "mmst2.CUSTNM as PDCPNM", "fmst.BRNDNM", "MD.material_division", "mmst.OUTPOS")
                ->selectRaw("DATE_FORMAT(smst.FUPTIM, '%Y/%m/%d %H:%i') AS FUPTIM")
                ->selectRaw("DATE_FORMAT(smst.LUPTIM, '%Y/%m/%d %H:%i') AS LUPTIM")
                ->join($this->mmst, "mmst.CUSTCD", "smst.CUSTCD")
                ->join($this->mmst2, "mmst2.CUSTCD", "smst.PDCPCD")
                ->join($this->fmst, "fmst.BRNDCD", "smst.BRNDCD")
                ->leftJoin($this->MD, function ($join) {
                    $join->on("MD.CUSTCD", "smst.CUSTCD");
                    $join->on("MD.BRNDCD", "smst.BRNDCD");
                });

            //検索条件を追加
            $list = $this->_getSearch($list, $rq);
            $list = $list->orderBy("mmst.OUTPOS")
                ->orderBy("smst.CUSTCD")
                ->orderBy("smst.OUTPOS")
                ->paginate(20);
        }
        // 汎用一覧
        $list_enums = m_enums::select("enum_key", "enum_value")
            ->where("enum_id", "car_type")
            ->orderBy("enum_sort")
            ->get();

        session()->flashInput($rq->input());
        $titleheader = "物流費マスタ";

        // 一覧のフォーマット表示
        $format = $this->_getTableFormat();

        return view("admin.smst.index", compact("titleheader", "list", "list_enums", "success", "format"));
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
                "width" => "mw-8em",
                "align" => ""
            ],
            "PDCPCD" => [
                "name" => "物流会社<br>コード",
                "width" => "w-4em",
                "align" => "text-center",
                "key" => 1
            ],
            "PDCPNM" => [
                "name" => "物流会社名称",
                "width" => "mw-13em",
                "align" => ""
            ],
            "SRKBCD" => [
                "name" => "原料区分<br>コード",
                "width" => "w-4em",
                "align" => "text-center",
                "key" => 1
            ],
            "BRNDKB" => [
                "name" => "銘柄区分",
                "width" => "w-4em",
                "align" => "text-center",
                "key" => 1
            ],
            "BRNDCD" => [
                "name" => "銘柄<br>コード",
                "width" => "w-4em",
                "align" => "text-center",
                "key" => 1
            ],
            "BRNDNM" => [
                "name" => "銘柄名称",
                "width" => "mw-10em",
                "align" => ""
            ],
            "PDFMCD" => [
                "name" => "物流形態<br>コード",
                "width" => "w-4em",
                "align" => "text-center",
                "key" => 1
            ],
            "AGMEMO" => [
                "name" => "契約内容",
                "width" => "w-4em",
                "align" => ""
            ],
            "UTPRKG" => [
                "name" => "単価金額",
                "width" => "w-4em",
                "align" => "text-right"
            ],
            "CSUTCD" => [
                "name" => "単位<br>コード",
                "width" => "w-2em",
                "align" => "text-center"
            ],
            "TRKBCD" => [
                "name" => "取引区分<br>コード",
                "width" => "w-4em",
                "align" => "text-center"
            ],
            "OUTPOS" => [
                "name" => "表示順位",
                "width" => "w-4em",
                "align" => "text-right"
            ],
            "BIKONM" => [
                "name" => "備考欄",
                "width" => "w-em",
                "align" => ""
            ],
            "SRTDAY" => [
                "name" => "有効期間<br>(開始日)",
                "width" => "w-4em",
                "align" => "text-center"
            ],
            "ENDDAY" => [
                "name" => "有効期間<br>(終了日)",
                "width" => "w-4em",
                "align" => "text-center"
            ],
            "FUSRCD" => [
                "name" => "登録者コード",
                "width" => "w-3em",
                "align" => ""
            ],
            "FUPTIM" => [
                "name" => "登録日時",
                "width" => "w-6em",
                "align" => "text-center"
            ],
            "LUSRCD" => [
                "name" => "最終更新者<br>コード",
                "width" => "w-5em",
                "align" => ""
            ],
            "LUPTIM" => [
                "name" => "最終更新日時",
                "width" => "w-6em",
                "align" => "text-center"
            ],
            "car_type" => [
                "name" => "車両種別",
                "width" => "w-6em",
                "align" => "text-center"
            ]
        ];
    }

    /**
     * 保存ボタンをクリックするとき
     * @param  $rq 入力画面 
     * @return  true
     */
    private function _saveData(Request $rq)
    {
        if ($rq->CUSTCD) {
            foreach ($rq->CUSTCD as $key => $val) {
                $data = [];
                $datakey = [];
                $smst = DB::table($this->smst);
                foreach ($this->_getTableFormat() as $k => $c) {
                    if (isset($c["key"]) && $c["key"]) {
                        $smst = $smst->where($k, $rq->$k[$key]);
                    }
                }
                $data["car_type"] = isset($rq->car_type[$key]) ? $rq->car_type[$key] : null;
                $smst->update($data, $datakey);
            }
        }
    }

    /**
     * 検索条件を追加
     * @param  $list 表示一覧 
     * @param  $rq input 
     * @return  表示一覧
     */
    private function _getSearch($list, Request $rq)
    {
        // 物流会社名称
        if ($rq->filled("PDCPNM")) {
            $list = InsertUpdateController::getWhereLike($list, $rq->PDCPNM, "mmst2.CUSTNM");
        }
        // 取引先名称
        if ($rq->filled("CUSTNM")) {
            $list = InsertUpdateController::getWhereLike($list, $rq->CUSTNM, "mmst.CUSTNM");
        }

        // 有効期間
        if ($rq->filled("SRTDAY") && $rq->SRTDAY) {
            $list = $list->where("smst.ENDDAY", ">=",  \Carbon\Carbon::parse($rq->SRTDAY)->format("Ymd"));
        }
        if ($rq->filled("ENDDAY") && $rq->ENDDAY) {
            $list = $list->where("smst.SRTDAY", "<=",  \Carbon\Carbon::parse($rq->ENDDAY)->format("Ymd"));
        }


        return $list;
    }
}
