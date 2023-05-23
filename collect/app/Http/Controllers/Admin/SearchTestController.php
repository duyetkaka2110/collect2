<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InsertUpdateController;
use Illuminate\Http\Request;
use Carbon;
use App\GetMessage;
use Session;
use App\Models\UserAsU;
use Auth;
use App\Models\Status;
use App\Models\t_Requests;
use App\Models\RequestsDetail;
use App\Models\t_dispatches;
use App\Models\t_dispatchesAsD;
use App\Models\t_dispatch_details;
use DB;

class SearchTestController extends Controller
{
    protected $_mysystemname = "admin";
    protected $mmst = "pldtcustmmst as mmst"; //取引先マスタ
    protected $tmst = "pldtcusttmst as tmst"; //取引先担当者マスタ
    protected $kmst = "pldtcustkmst as kmst"; //取引先単価マスタ
    protected $fmst = "pldtbrinfmst as fmst"; //商品マスタ
    protected $t_dispatch_details = "t_dispatch_details As DD"; //手配詳細
    protected $m_statuses = "m_statuses As S"; //状態マスタ
    protected $t_request_details = "t_request_details As RD"; //依頼内容
    protected $t_requests = "t_requests as R";
    protected $m_users = "m_users as u";

    /**
     * 依頼検索画面
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    public function search(Request $rq)
    {
        $titleheader = "依頼検索（テスト用）";
        $list = DB::table($this->t_requests)
            ->select("R.request_id", "R.request_no", "RD.information", "S.status_name", "S.font_color", "S.background_color", "fmst.BRNDNM", "mmst.CUSTNM", "E.enum_value AS RTypeNM")
            ->selectRaw("DATE_FORMAT(R.created_at, '%Y/%m/%d') AS created_at")
            ->selectRaw("DATE_FORMAT(R.applied_at, '%Y/%m/%d') AS applied_at")
            ->selectRaw("DATE_FORMAT(RD.hoped_on, '%Y/%m/%d') AS hoped_on")
            ->selectRaw("DATE_FORMAT(RD.recovered_on, '%Y/%m/%d') AS recovered_on")
            ->join($this->t_request_details, "R.request_id", "RD.request_id")
            ->join($this->m_statuses, "S.status_id", "RD.status_id")
            ->join($this->fmst, "fmst.BRNDCD", "RD.BRNDCD")
            ->join($this->mmst, "mmst.CUSTCD", "R.CUSTCD")
            ->leftJoin("m_enums as E", function ($join) {
                $join->whereRaw("E.enum_id = 'request_type'");
                $join->on("E.enum_key", "R.request_type");
            });

        //検索条件を追加
        // 状態指定時
        if ($rq->status) {
            $list = $list->whereIn('RD.status_id', $rq->status);
        }
        // 日付指定時
        if ($rq->DateFrom && $rq->DateTo) {
            // From～Toの範囲内
            $list = $list->whereBetween($rq->DateType, [$rq->DateFrom, $rq->DateTo . " 23:59:59"]);
        }
        elseif ($rq->DateFrom) {
            // From以降
            $list = $list->whereDate($rq->DateType, ">=", $rq->DateFrom);
        }
        elseif ($rq->DateTo) {
            // To以前
            $list = $list->whereDate($rq->DateType, "<=", $rq->DateTo . " 23:59:59");
        }

        $list = $list->orderBy("R.created_at", "DESC")
            ->orderBy("R.request_id", "DESC")
            ->orderBy("RD.seq_no", "ASC");
        $list = $list->paginate(20);
        $ErrMsg = null;
        $btnSearch = $rq->btnSearch;

        // 状態マスタ一覧
        $status = Status::where("is_deleted", 0)->orderBy("sort_no")->get();

        session()->flashInput($rq->input());
        return view("admin.collect.requesttest.search", compact("titleheader", "list", "status",  "ErrMsg", "btnSearch"));
    }

    /**
     * 依頼編集画面
     * @param  $id 依頼内容ID 
     * @return \Illuminate\View\View
     */
    public function edit(Request $rq)
    {
        $id = $rq->id;
        if (!$id && $rq->request_detail_id) {
            // カレンダ画面の手配詳細のダブルクリックで展開
            $id = RequestsDetail::where("request_detail_id", $rq->request_detail_id)->value("request_id");
        }
        $titleheader = "依頼編集（テスト用）";
        $user = [];
        $title_header = "";
        $classtitle = "form-show";
        $user = $this->_getUser();
        $classtitle = "form-new";
        if (!$id) {
            $data = $dataRD =  [];
        } else {
            $data = $this->_getRequest($id);
            if (!$data && !Session::get('errors')) {
                return redirect()->route("a.rsearch");
            }
            $dataRD = DB::table($this->t_request_details)
                ->select("RD.request_detail_id",  "RD.is_nohoped",  "RD.information", "S.status_name", "S.font_color", "S.background_color", "S.status_id", "RD.BRNDCD")
                ->selectRaw("DATE_FORMAT(RD.hoped_on, '%Y/%m/%d') AS hoped_on")
                ->selectRaw("DATE_FORMAT(RD.recovered_on, '%Y/%m/%d') AS recovered_on")
                ->join($this->m_statuses, "S.status_id", "RD.status_id")
                ->where("RD.request_id", $id)
                ->orderBy("RD.seq_no", "ASC")
                ->get()->toArray();
        }

        // 銘柄一覧
        $listproducts = DB::table($this->fmst)->select("fmst.BRNDNM", "fmst.BRNDCD", "kmst.OUTPOS")
            ->join($this->kmst, "kmst.BRNDCD", "fmst.BRNDCD")
            ->join("m_material_divisions as MD", function ($join) {
                $join->on("MD.CUSTCD", "kmst.CUSTCD");
                $join->on("MD.BRNDCD", "kmst.BRNDCD");
                $join->whereRaw("MD.material_division <> ''");
            })
            ->join($this->t_requests, "R.CUSTCD", "kmst.CUSTCD")
            ->where("kmst.BRNDKB", 1)
            ->where("R.request_id", $id)
            ->groupBy("fmst.BRNDNM", "fmst.BRNDCD", "kmst.OUTPOS")
            ->orderBy("kmst.OUTPOS")
            ->orderBy("fmst.BRNDCD")->get();
        // 状態マスタ一覧
        $status = Status::where("is_deleted", 0)->orderBy("sort_no")->get();
        // 引取／持込
        $request_type = DB::table("m_enums")
            ->select("enum_key", "enum_value")
            ->whereRaw("enum_id = 'request_type'")
            ->orderBy("enum_sort")->get();

        return view("admin.collect.requesttest.edit", compact("data", "dataRD", "listproducts", "titleheader", "classtitle", "user", "status", "request_type"));
    }

    /**
     *　依頼申込／依頼詳細画面
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    public function redit_store(Request $rq)
    {
        $btnName = null;
        // 「依頼ID」が0以外の場合は「依頼ID」がチェックする
        if ($rq->request_id) {
            $data = $this->_getRequest($rq->request_id);
            if (!$data) {
                // ・「依頼ID」が合致する依頼データが存在しない場合、以下エラーメッセージを表示して、処理を中止
                return redirect()->route("a.rsearch", ["id" => 0])->withErrors([GetMessage::getMessageByID("error008")]);
            }
        }
        try {
            DB::beginTransaction();
            //【保存】ボタン
            if ($rq->btnOk == "save") {
                $request_id = $rq->request_id;
                // 　　－依頼IDが合致する依頼データを更新
                $dataR = $this->_getR($rq);
                t_Requests::where("request_id", $request_id)->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $dataR));

                // 　　－依頼IDが合致する依頼内容データの「依頼内容ID」を取得
                $listRD = RequestsDetail::select("request_detail_id")->where("request_id", $request_id)->get();
                $listRDold = [];
                foreach ($listRD as $rd) {
                    $listRDold[$rd->request_detail_id] = $rd->request_detail_id;
                }
                $recovered_delete = []; // 画面上の「回収日」が未入力で依頼内容ID0以外
                $RDnew = []; // 画面上の「回収日」が入力済みで依頼内容IDに合致する手配詳細データが存在しない
                $RDChange = []; //画面上の「回収日」が入力済みで依頼内容IDに合致する手配詳細データが存在する

                // 依頼内容IDが画面上の依頼内容に存在する確確認
                foreach ($rq->request_detail_id as $k => $rdid) {
                    if ($k > 0) {
                        $dataRD = $this->_getRD($rq, $request_id, $k);
                        if ($rdid && array_key_exists($rdid, $listRDold)) {
                            unset($listRDold[$rdid]);
                            //　　－取得した依頼内容IDが画面上の依頼内容に存在する場合、依頼内容IDに合致する依頼内容データを更新
                            RequestsDetail::where("request_detail_id", $rdid)->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $dataRD));

                            //画面上の「回収日」が未入力で依頼内容ID0以外
                            if (!$rq->recovered_on[$k]) {
                                $recovered_delete[] = $rdid;
                            } else {
                                // 「回収日」が入力済みで手配詳細データが存在する確認
                                if (t_dispatch_details::where("request_detail_id", $rdid)->count()) {
                                    $RDChange[] = $k;
                                } else {
                                    $RDnew[] = $k;
                                }
                            }
                        } else {
                            // 新規に作成
                            if ($rq->recovered_on[$k]) {
                                $RDnew[] = $k;
                            }
                            //　　－取得した依頼内容IDが画面上の依頼内容に存在しない場合、依頼内容データを新規に作成
                            RequestsDetail::insert(array_merge(InsertUpdateController::datainsert($this->_mysystemname), $dataRD));
                        }
                    }
                }
                // 　　－取得した依頼内容IDが画面上の依頼内容に存在しない場合、
                if ($listRDold) {
                    // 依頼内容IDに合致する依頼内容データを削除
                    RequestsDetail::whereIn("request_detail_id", $listRDold)->delete();

                    // 依頼内容IDに合致する手配詳細データを削除
                    t_dispatch_details::whereIn("request_detail_id", $listRDold)->delete();
                }

                // 　　－画面上の「回収日」が未入力で依頼内容IDが0以外の場合、依頼内容IDに合致する手配詳細データを削除
                if ($recovered_delete) {
                    t_dispatch_details::whereIn("request_detail_id", $recovered_delete)->delete();
                }

                //　　－画面上の「回収日」が入力済みで依頼内容IDに合致する手配詳細データが存在しない場合、以下を実施
                if ($RDnew) {
                    foreach ($RDnew as $v) {
                        // 車両が未定の手配データを取得
                        $dispatch_id = t_dispatchesAsD::select("dispatch_id")
                            ->where("PCARNO", 0)->where("CIDXNO", 0)->where("PDCPCD", 0)->where("PDRVCD", 0)
                            ->whereDate("dispatched_on", $rq->recovered_on[$v])
                            ->value("dispatch_id");
                        if (!$dispatch_id) {
                            // 未定の手配データが取得できなかった場合、手配データを新規に作成
                            $data = [
                                "PCARNO" => 0,
                                "CIDXNO" => 0,
                                "PDCPCD" => 0,
                                "PDRVCD" => 0,
                                "dispatched_on" => $rq->recovered_on[$v],
                                "is_confirmed" => 0
                            ];
                            $dispatch_id = t_dispatches::insertGetId(array_merge(InsertUpdateController::datainsert($this->_mysystemname), $data));
                            $sort_no_total = 0;
                        } else {
                            $sort_no_total = t_dispatch_details::where("dispatch_id", $dispatch_id)->max("sort_no");
                            if (!$sort_no_total) {
                                $sort_no_total = 0;
                            }
                        }
                        // －手配詳細データを新規に作成
                        $data = [
                            "dispatch_id" => $dispatch_id,
                            "sort_no" => ++$sort_no_total,
                            "request_detail_id" => $rq->request_detail_id[$v],
                            "instruct" => $rq->CUSTNM
                        ];
                        t_dispatch_details::insert(array_merge(InsertUpdateController::datainsert($this->_mysystemname), $data));
                    }
                }
                //  　－画面上の「回収日」が入力済みで依頼内容IDに合致する手配詳細データが存在して、かつ「回収日」と「手配日」が異なる場合、以下を実施
                if ($RDChange) {
                    foreach ($RDChange as $v) {
                        // 車両が未定の手配データを取得
                        $dispatch = t_dispatchesAsD::select("D.*")
                            ->selectRaw("DATE_FORMAT(D.dispatched_on, '%Y/%m/%d') AS dispatched_on2")
                            ->join($this->t_dispatch_details, "D.dispatch_id", "DD.dispatch_id")
                            ->where("DD.request_detail_id", $rq->request_detail_id[$v])
                            ->first();
                        // 回収日」と「手配日」が異なる場合
                        if ($dispatch->dispatched_on2 != $rq->recovered_on[$v]) {

                            // 車両が未定の手配データを取得
                            $dispatch_id = t_dispatchesAsD::select("dispatch_id")
                                ->where("PCARNO", $dispatch->PCARNO)->where("CIDXNO", $dispatch->CIDXNO)
                                ->where("PDCPCD", $dispatch->PDCPCD)->where("PDRVCD", $dispatch->PDRVCD)
                                ->whereDate("dispatched_on", $rq->recovered_on[$v])
                                ->value("dispatch_id");
                            if (!$dispatch_id) {
                                // －手配データが取得できなかった場合、手配データを新規に作成
                                $data = [
                                    "PCARNO" => $dispatch->PCARNO,
                                    "CIDXNO" => $dispatch->CIDXNO,
                                    "PDCPCD" => $dispatch->PDCPCD,
                                    "PDRVCD" => $dispatch->PDRVCD,
                                    "dispatched_on" => $rq->recovered_on[$v],
                                    "is_confirmed" => 0
                                ];
                                $dispatch_id = t_dispatches::insertGetId(array_merge(InsertUpdateController::datainsert($this->_mysystemname), $data));
                                $sort_no_total = 0;
                            } else {
                                $sort_no_total = t_dispatch_details::where("dispatch_id", $dispatch_id)->max("sort_no");
                                if (!$sort_no_total) {
                                    $sort_no_total = 0;
                                }
                            }
                            // －依頼内容IDに合致する手配詳細データを更新
                            $data = [
                                "dispatch_id" => $dispatch_id,
                                "sort_no" => ++$sort_no_total
                            ];
                            t_dispatch_details::where("request_detail_id", $rq->request_detail_id[$v])
                                ->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data));
                        }
                    }
                }
            }

            //依頼内容データの原料区分コードを更新
            RequestsDetail::query()
                ->join($this->t_requests, "R.request_id", "t_request_details.request_id")
                ->join("m_material_divisions AS mDiv", function ($join) {
                    $join->on("R.CUSTCD", "mDiv.CUSTCD");
                    $join->on("t_request_details.BRNDCD", "mDiv.BRNDCD");
                })
                ->where("t_request_details.request_id", $request_id)
                ->update(['t_request_details.material_division' => DB::raw("`mDiv`.`material_division`")]);

            DB::commit();
            $btnName = "保存";
            return redirect()->route("a.redit", ["id" => $request_id])->withSuccess(str_replace("{p}", $btnName, GetMessage::getMessageByID("error007")));
        } catch (Throwable $e) {
            DB::rollBack();
            $ErrMsg = GetMessage::getMessageByID("error009");
            return redirect()->back()->withErrors($ErrMsg);
        }
    }

    /**
     *　依頼申込／依頼詳細画面
     *   依頼データを作成
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    protected function _getR(Request $rq)
    {
        $data =  array(
            "request_type" => $rq->request_type, // ・依頼種別：画面の「引取／持込」の選択値のIDをセット
            "request_note" => $rq->request_note,  // ・依頼備考：画面の「依頼備考」の値をセット
        );
        return $data;
    }

    /**
     *   依頼内容データを作成
     * @param  Request $rq
     * @param   int $request_id 依頼ID
     * @param   bool $app 【依頼申込】ボタンフラグ
     * @return \Illuminate\View\View
     */
    protected function _getRD(Request $rq, int $request_id, int $k)
    {
        $dataRD = array(
            "request_id" => $request_id, // ・依頼ID：上記で新規に作成した依頼データの依頼IDをセット
            "seq_no" => $k, // ・連番：１から連番をセット
            "BRNDCD" => $rq->BRNDCD[$k], // ・商品コード：画面の依頼内容の「銘柄」の選択値のIDをセット
            "status_id" =>  $rq->status[$k], // ・状態：画面の依頼内容の「状態」の選択値のIDをセット
            "hoped_on" => $rq->hoped_on[$k], // ・希望日：画面の依頼内容の「希望日」の値をセット
            "recovered_on" => $rq->recovered_on[$k], // ・回収日：画面の依頼内容の「回収日」の値をセット
            "information" => $rq->information[$k], // ・連絡事項：画面の依頼内容の「連絡事項」の値をセット
        );
        return $dataRD;
    }

    /**
     *　依頼テーブルの情報
     * @param  $id 依頼ID
     * @return array 依頼
     */
    private function _getRequest($id)
    {
        return t_Requests::select("t_requests.*", "mmst.CUSTNM")
            ->selectRaw("DATE_FORMAT(t_requests.created_at, '%Y/%m/%d') AS created_at2")
            ->selectRaw("DATE_FORMAT(t_requests.updated_at, '%Y/%m/%d') AS updated_at2")
            ->selectRaw("DATE_FORMAT(applied_at, '%Y/%m/%d') AS applied_at2")
            ->selectRaw("CASE WHEN u.family_name IS NOT NULL AND u.first_name IS NOT NULL
                              THEN CONCAT(u.family_name, '　', u.first_name)
                              WHEN u.family_name IS NOT NULL OR u.first_name IS NOT NULL
                              THEN COALESCE(u.family_name, u.first_name)
                              WHEN tmst.TCSIKJ IS NOT NULL AND tmst.TCMEKJ IS NOT NULL
                              THEN CONCAT(tmst.TCSIKJ, '　', tmst.TCMEKJ)
                              ELSE COALESCE(tmst.TCSIKJ, tmst.TCMEKJ)
                         END AS manager_name")
            ->selectRaw("COALESCE(u.tel_no,tmst.RTELNO) AS tel_no")
            ->selectRaw("COALESCE(u.mail_address, tmst.MLADRS) AS mail_address")
            ->selectRaw("u.ccmail_address")
            ->selectRaw("u.contact_type")
            ->leftJoin($this->m_users, "applied_user", "u.user_id")
            ->leftJoin($this->tmst, function ($join) {
                $join->on("u.CUSTCD", "tmst.CUSTCD");
                $join->on("u.TCSTNO", "tmst.TCSTNO");
            })
            ->leftJoin($this->mmst, "t_requests.CUSTCD", "mmst.CUSTCD")
            ->where("request_id", $id)->get()->first();
    }

    /**
     *　ユーザマスタ情報
     * @return　ユーザ
     */
    private function _getUser()
    {
        return UserAsU::select("u.*", "mmst.CUSTNM")
            ->selectRaw("CASE WHEN u.family_name IS NOT NULL AND u.first_name IS NOT NULL
                              THEN CONCAT(u.family_name, '　', u.first_name)
                              WHEN u.family_name IS NOT NULL OR u.first_name IS NOT NULL
                              THEN COALESCE(u.family_name, u.first_name)
                              WHEN tmst.TCSIKJ IS NOT NULL AND tmst.TCMEKJ IS NOT NULL
                              THEN CONCAT(tmst.TCSIKJ, '　', tmst.TCMEKJ)
                              ELSE COALESCE(tmst.TCSIKJ, tmst.TCMEKJ)
                         END AS user_name")
            ->selectRaw("COALESCE(u.tel_no, tmst.RTELNO) AS tel_no")
            ->selectRaw("COALESCE(u.mail_address, tmst.MLADRS) AS mail_address")
            ->selectRaw("CASE WHEN u.family_name IS NOT NULL OR u.first_name IS NOT NULL
                              THEN u.family_name
                              ELSE tmst.TCSIKJ 
                         END AS family_name")
            ->selectRaw("CASE WHEN u.family_name IS NOT NULL OR u.first_name IS NOT NULL
                              THEN u.first_name
                              ELSE tmst.TCMEKJ 
                         END AS first_name")
            ->leftJoin($this->tmst, function ($join) {
                $join->on("u.CUSTCD", "tmst.CUSTCD");
                $join->on("u.TCSTNO", "tmst.TCSTNO");
            })
            ->leftJoin($this->mmst, "mmst.CUSTCD", "u.CUSTCD")
            ->where("u.user_id", Auth::user()->user_id)
            ->get()->first();
    }
}
