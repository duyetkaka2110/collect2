<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\GetMessage;
use App\Models\m_enums;
use App\Http\Controllers\InsertUpdateController;

class DivisionsController extends Controller
{
    protected $_mysystemname = "admin";
    protected $mmst = "pldtcustmmst as mmst"; //取引先マスタ
    protected $kmst = "pldtcustkmst as kmst"; //取引先単価マスタ
    protected $fmst = "pldtbrinfmst as fmst"; //商品マスタ
    protected $MD = "m_material_divisions as MD"; //原料区分マスタ
    protected $m_material_divisions = "m_material_divisions"; //原料区分マスタ

    /**
     * 原料区分マスタ画面
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
        $list = DB::table($this->kmst)->select("kmst.CUSTCD", "kmst.BRNDCD", "mmst.CUSTNM", "fmst.BRNDNM", "MD.material_division", "mmst.OUTPOS as OUTPOS1", "kmst.OUTPOS as OUTPOS2")
            ->join($this->mmst, "mmst.CUSTCD", "kmst.CUSTCD")
            ->join($this->fmst, "fmst.BRNDCD", "kmst.BRNDCD")
            ->leftJoin($this->MD, function ($join) {
                $join->on("MD.CUSTCD", "kmst.CUSTCD");
                $join->on("MD.BRNDCD", "kmst.BRNDCD");
            });

        //検索条件を追加
        $list = $this->_getSearch($list, $rq);
        $list = $list->groupBy("kmst.CUSTCD", "kmst.BRNDCD", "mmst.CUSTNM", "fmst.BRNDNM", "MD.material_division", "mmst.OUTPOS", "kmst.OUTPOS")
            ->orderBy("mmst.OUTPOS")
            ->orderBy("kmst.CUSTCD")
            ->orderBy("kmst.OUTPOS")
            ->orderBy("kmst.BRNDCD")
            ->paginate(20);
        // 汎用一覧
        $list_enums = m_enums::select("enum_key", "enum_value")
            ->where("enum_id", "material_division")
            ->orderBy("enum_sort")
            ->get();
        session()->flashInput($rq->input());
        $titleheader = "原料区分マスタ";

        return view("admin.divisions.index", compact("titleheader", "list", "list_enums", "success"));
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
                $data = array_merge(
                    InsertUpdateController::datainsert($this->_mysystemname),
                    [
                        "CUSTCD" =>  $val,
                        "BRNDCD" => $rq->BRNDCD[$key],
                        "material_division" => isset($rq->material_division[$key]) ? $rq->material_division[$key] : null
                    ]
                );
                DB::table($this->m_material_divisions)->upsert($data, ["CUSTCD", "BRNDCD"], ["material_division", "updated_at", "updated_system", "updated_user"]);
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
        // 銘柄名称
        if ($rq->filled("BRNDNM")) {
            $list = InsertUpdateController::getWhereLike($list, $rq->BRNDNM, "fmst.BRNDNM");
        }
        // 取引先名称
        if ($rq->filled("CUSTNM")) {
            $list = InsertUpdateController::getWhereLike($list, $rq->CUSTNM, "mmst.CUSTNM");
        }
        return $list;
    }
}
