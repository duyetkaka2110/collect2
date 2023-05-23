<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InsertUpdateController;
use Illuminate\Http\Request;
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
use App\Models\m_material_divisions;
use DB;
use PDF;
use Carbon;
use Mail;
use Cookie;

class SearchController extends Controller
{
    protected $_mysystemname = "admin";
    protected $mmst = "pldtcustmmst as mmst"; //取引先マスタ
    protected $mmst2 = "pldtcustmmst as mmst2"; //取引先マスタ
    protected $tmst = "pldtcusttmst as tmst"; //取引先担当者マスタ
    protected $kmst = "pldtcustkmst as kmst"; //取引先単価マスタ
    protected $fmst = "pldtbrinfmst as fmst"; //商品マスタ
    protected $vmst = "pldtpddrvmst as vmst"; //運転手マスタ
    protected static $vmst2 = "pldtpddrvmst as vmst"; //運転手マスタ
    protected $smst = "pldtpdcosmst as smst"; //物流費マスタ
    protected $rmst = "pldtpdcarmst as rmst"; //車両マスタ
    protected static $rmst2 = "pldtpdcarmst as rmst"; //車両マスタ
    protected $enums = "m_enums as E"; //汎用マスタ
    protected $enums2 = "m_enums as E2"; //汎用マスタ
    protected $t_dispatch_details = "t_dispatch_details As DD"; //手配詳細
    protected $m_statuses = "m_statuses As S"; //状態マスタ
    protected $t_request_details = "t_request_details As RD"; //依頼内容
    protected $t_requests = "t_requests as R";
    protected $m_users = "m_users as u";
    protected $m_user_authorities = "m_user_authorities as ua"; //管理権限マスタ
    protected $t_user_settings = "t_user_settings"; //ユーザ設定
    protected $admin_search = "admin_search"; // ユーザ設定の対象画面

    /**
     * 依頼検索画面
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    public function search(Request $rq)
    {
        $titleheader = "依頼検索";

        // ログインユーザーの権限種別
        $authority_type = UsersController::authority_type();

        // ユーザ設定
        $user_setting = $this->getUserSetting();

        // 種別一覧
        $listRtype = DB::table($this->enums)->select("enum_value", "enum_key")
            ->where("enum_id", "request_type")
            ->orderBy("enum_key")
            ->get();

        // 状態マスタ一覧
        $status = Status::where("is_deleted", 0);
        if (!array_key_exists(1, $authority_type)) {
            // ログインユーザに受付担当の権限がある場合のみ表示
            $status = $status->where("status_id", "!=", config("const.RDStatus.TEMP"));
        }
        $status = $status->orderBy("sort_no")->get();

        // 初期状態は以下がチェックされた状態とする
        $status_first = [];
        if (!$rq->BtnOk) {
            if (array_key_exists(2, $authority_type)) {
                // 配車担当：【配車】
                $status_first[config("const.RDStatus.DISP")] = config("const.RDStatus.DISP");
            }
            if (array_key_exists(1, $authority_type)) {
                // 受付担当：【受付】【依頼】【配車】【顧客】【確定】【完了】
                $status_first[config("const.RDStatus.RECEPT")] = config("const.RDStatus.RECEPT");
                $status_first[config("const.RDStatus.REQUEST")] = config("const.RDStatus.REQUEST");
                $status_first[config("const.RDStatus.DISP")] = config("const.RDStatus.DISP");
                $status_first[config("const.RDStatus.CUSTOMER")] = config("const.RDStatus.CUSTOMER");
                $status_first[config("const.RDStatus.CONFIRM")] = config("const.RDStatus.CONFIRM");
                $status_first[config("const.RDStatus.COMP")] = config("const.RDStatus.COMP");
            }
        }
        // 一覧に表示する
        $list = DB::table($this->t_requests)
            ->select(
                "R.request_id",
                "R.request_no",
                "RD.information",
                "S.status_name",
                "S.font_color",
                "S.background_color",
                "fmst.BRNDNM",
                "E.enum_value AS RTypeNM",
                "E2.enum_value AS car_typeNM",
                "mmst2.CUSTNM AS PDCPCDNM",
                "RD.PCARNO",
                "S.status_id"
            )
            ->selectRaw("CONCAT(mmst.CUSTNM ,'<br>', 
                                CASE WHEN u.family_name IS NOT NULL AND u.first_name IS NOT NULL
                                    THEN CONCAT(u.family_name, '　', u.first_name)
                                    WHEN u.family_name IS NOT NULL OR u.first_name IS NOT NULL
                                    THEN COALESCE(u.family_name, u.first_name)
                                    WHEN tmst.TCSIKJ IS NOT NULL AND tmst.TCMEKJ IS NOT NULL
                                    THEN CONCAT(tmst.TCSIKJ, '　', tmst.TCMEKJ)
                                    ELSE COALESCE(tmst.TCSIKJ, tmst.TCMEKJ)
                                        END,
                            '<br>',
                            CASE WHEN u.contact_type = 0
                                THEN u.tel_no
                                ELSE u.mail_address
                                END
                            ) AS CFU")
            ->selectRaw("CASE WHEN RD.status_id = '" . config("const.RDStatus.DISP") . "' OR RD.status_id = '" . config("const.RDStatus.CUSTOMER") . "' 
                              THEN 'red'
                              ELSE NULL
                         END AS PCARNO_bg")
            ->selectRaw("CASE WHEN RD.receptioned_at IS NOT NULL
                                THEN CONCAT(DATE_FORMAT(RD.receptioned_at, '%Y/%m/%d<br>%H:%i '),'<br>',IFNULL(u1.family_name, ''))
                                ELSE null
                        END AS receptionNM")

            ->selectRaw("CASE WHEN RD.dispatched_at IS NOT NULL
                            THEN CONCAT(DATE_FORMAT(RD.dispatched_at, '%Y/%m/%d<br>%H:%i '),'<br>',IFNULL(u2.family_name, ''))
                            ELSE null
                        END AS dispatchedNM")

            ->selectRaw("CASE WHEN RD.confirmed_at IS NOT NULL
                        THEN CONCAT(DATE_FORMAT(RD.confirmed_at, '%Y/%m/%d<br>%H:%i '),'<br>',IFNULL(u3.family_name, ''))
                        ELSE null
                    END AS confirmedNM")

            ->selectRaw("CASE WHEN RD.acceptanced_at IS NOT NULL
                    THEN CONCAT(DATE_FORMAT(RD.acceptanced_at, '%Y/%m/%d<br>%H:%i '),'<br>',IF(RD.acceptanced_value IS NULL, '', CAST(RD.acceptanced_value AS CHAR) + 0), IFNULL(RD.acceptanced_unit, ''))
                    ELSE null
                END AS acceptancedNM")
            ->selectRaw("CONCAT(IFNULL(vmst.DVSIKJ, ''),'<br>',IFNULL(vmst.KTELNO, '')) AS DVSTEL")
            ->selectRaw("DATE_FORMAT(R.created_at, '%Y/%m/%d') AS created_at")
            ->selectRaw("DATE_FORMAT(R.applied_at, '%Y/%m/%d') AS applied_at")
            ->selectRaw("DATE_FORMAT(RD.hoped_on, '%Y/%m/%d') AS hoped_on")
            ->selectRaw("DATE_FORMAT(RD.recovered_on, '%Y/%m/%d') AS recovered_on")
            ->selectRaw("CONCAT(u5.family_name,'　',u5.first_name) as locked_user")
            ->join($this->t_request_details, "R.request_id", "RD.request_id")
            ->join($this->m_statuses, "S.status_id", "RD.status_id")
            ->join($this->fmst, "fmst.BRNDCD", "RD.BRNDCD")
            ->join($this->mmst, "mmst.CUSTCD", "R.CUSTCD")
            ->leftJoin($this->m_users, DB::raw("IF(R.applied_user != '',R.applied_user,R.created_user)"), "u.user_id")
            ->leftJoin($this->vmst, function ($join) {
                $join->on("vmst.PDCPCD", "RD.PDCPCD");
                $join->on("vmst.PDRVCD", "RD.PDRVCD");
            })
            ->leftJoin($this->mmst2, "mmst2.CUSTCD", "RD.PDCPCD")
            ->leftJoin($this->enums, function ($join) {
                $join->whereRaw("E.enum_id = 'request_type'");
                $join->on("E.enum_key", "R.request_type");
            })
            ->leftJoin($this->enums2, function ($join) {
                $join->whereRaw("E2.enum_id = 'car_type'");
                $join->on("E2.enum_key", "RD.car_type");
            })
            ->leftJoin("m_users as u1", "u1.user_id", "RD.receptioned_user")
            ->leftJoin("m_users as u2", "u2.user_id", "RD.dispatched_user")
            ->leftJoin("m_users as u3", "u3.user_id", "RD.confirmed_user")
            ->leftJoin("m_users as u4", "u4.user_id", "RD.acceptanced_user")
            ->leftJoin("m_users as u5", "u5.user_id", "R.locked_user")
            ->leftJoin($this->tmst, function ($join) {
                $join->on("u.CUSTCD", "tmst.CUSTCD");
                $join->on("u.TCSTNO", "tmst.TCSTNO");
            });

        //検索条件を追加
        $statusWhere = $status_first;
        if (!$status_first && !array_key_exists(1, $authority_type)) {
            // ログインユーザに受付担当の権限がある場合のみ表示
            $statusWhere = config("const.RDStatus");
            unset($statusWhere["TEMP"]);
        }
        $list = $this->_getSearch($list, $rq, $listRtype, $statusWhere);

        $list = $list->orderBy("R.created_at", "DESC")
            ->orderBy("R.request_id", "DESC")
            ->orderBy("RD.seq_no", "ASC");
        if ($rq->BtnOk == "export") {
            $list = $list->get();
            $data["list"] = $list;
            //ファイル出力
            $pdf = PDF::loadView('admin.collect.request.export', $data)->setPaper('a2', 'landscape');
            return $pdf->download('依頼検索一覧.pdf');
        }
        $list = $list->paginate(($user_setting && $user_setting->line_no) ? $user_setting->line_no : 20);

        $btnSearch = $rq->BtnOk;

        // 表示一覧のタイトル行を取得
        $header = $this->_getTableHeader();
        session()->flashInput($rq->input());


        return view("admin.collect.request.search", compact(
            "titleheader",
            "list",
            "status",
            "btnSearch",
            "listRtype",
            "status_first",
            "authority_type",
            "header",
            "user_setting"
        ));
    }

    /**
     * ユーザ設定情報
     * @return json ユーザ設定情報
     */
    public function getUserSetting()
    {
        $user_setting = DB::table($this->t_user_settings)->select("line_no", "hidden_item")
            ->where("user_id", Auth::user()->user_id)
            ->where("target_view", $this->admin_search)
            ->first();
        if ($user_setting) {
            $user_setting->hidden_item = array_flip(explode(",", $user_setting->hidden_item));
        }
        return $user_setting;
    }

    /**
     * 表示設定変更
     * @param  Request $rq
     * @return 依頼検索画面
     */
    public function setting(Request $rq)
    {
        // 表示一覧のタイトル行を取得
        $hidden_item = $this->_getTableHeader();
        foreach ($rq->key as $k) {
            unset($hidden_item[$k]);
        }
        // 非表示項目
        $hidden_item = implode(",", array_keys($hidden_item));
        $data =  array_merge(
            InsertUpdateController::datainsert($this->_mysystemname),
            [
                "user_id" => Auth::user()->user_id,
                "target_view" => $this->admin_search,
                "line_no" => $rq->line_no,
                "hidden_item" => $hidden_item,
            ]
        );
        DB::table($this->t_user_settings)->upsert($data, ["user_id", "target_view"], ["line_no", "hidden_item", "updated_at", "updated_system", "updated_user"]);
        return redirect()->route("a.rsearch");
    }

    /**
     * 表示一覧のタイトル行を取得
     * @return  表示一覧のタイトル行
     */
    private function _getTableHeader()
    {
        return [
            "edit" => [
                "name" => "編集",
                "class" => "w-4em text-nowrap",
                "disabled" => "disabled"
            ],
            "request_no" => [
                "name" => "依頼No.",
                "class" => "w-5em text-nowrap",
                "disabled" => ""
            ],
            "applied_at" => [
                "name" => "申込日",
                "class" => "w-7em text-nowrap",
                "disabled" => ""
            ],
            "CFU" => [
                "name" => "取引先／担当者／連絡先",
                "class" => "w-7em text-nowrap",
                "disabled" => ""
            ],
            "status_name" => [
                "name" => "状態",
                "class" => "w-4em text-nowrap",
                "disabled" => ""
            ],
            "RTypeNM" => [
                "name" => "種別",
                "class" => "w-9em text-nowrap",
                "disabled" => ""
            ],
            "BRNDNM" => [
                "name" => "銘柄",
                "class" => "w-9em text-nowrap",
                "disabled" => ""
            ],
            "hoped_on" => [
                "name" => "希望日",
                "class" => "w-7em text-nowrap",
                "disabled" => ""
            ],
            "recovered_on" => [
                "name" => "回収日",
                "class" => "w-7em text-nowrap",
                "disabled" => ""
            ],
            "PDCPCDNM" => [
                "name" => "物流会社",
                "class" => "w-7em text-nowrap",
                "disabled" => ""
            ],
            "car_typeNM" => [
                "name" => "車両種別",
                "class" => "w-6em text-nowrap",
                "disabled" => ""
            ],
            "PCARNO" => [
                "name" => "車両No",
                "class" => "w-13em text-nowrap",
                "disabled" => ""
            ],
            "DVSTEL" => [
                "name" => "運転手",
                "class" => "w-13em text-nowrap",
                "disabled" => ""
            ],
            "information" => [
                "name" => "連絡事項",
                "class" => "w-info text-nowrap mw-15em",
                "disabled" => ""
            ],
            "receptionNM" => [
                "name" => "受付",
                "class" => "w-7em text-nowrap",
                "disabled" => ""
            ],
            "dispatchedNM" => [
                "name" => "配車",
                "class" => "w-7em text-nowrap",
                "disabled" => ""
            ],
            "confirmedNM" => [
                "name" => "顧客確認",
                "class" => "w-7em text-nowrap",
                "disabled" => ""
            ],
            "acceptancedNM" => [
                "name" => "受入",
                "class" => "w-7em text-nowrap",
                "disabled" => ""
            ],
            "locked_user" => [
                "name" => "ロックユーザ",
                "class" => "w-7em text-nowrap",
                "disabled" => ""
            ],
        ];
    }

    /**
     * 検索条件を追加
     * @param  $list 表示一覧 
     * @param  $rq input 
     * @param  $listRtype 種別一覧 
     * @param  $status_first 受付担当者、配車担当者 
     * @return  表示一覧
     */
    private function _getSearch($list, Request $rq, $listRtype, $status_first)
    {
        // 状態指定時
        if ($rq->status) {
            $list = $list->whereIn('RD.status_id', $rq->status);
        } else {
            if ($status_first) {
                $list = $list->whereIn('RD.status_id', $status_first);
            }
        }
        // 取引先
        if ($rq->filled("CUSTNM")) {
            $list = InsertUpdateController::getWhereLike($list, $rq->CUSTNM, "mmst.CUSTNM");
        }

        // 種別
        if ($rq->filled("request_type") && (count($rq->request_type) < $listRtype->count())) {
            // 全て未チェックの場合は種別は検索条件とされない（全てチェックと同じ状態となる）
            $list = $list->whereIn("R.request_type", $rq->request_type);
        }

        // 銘柄
        if ($rq->filled("BRNDNM")) {
            $list = InsertUpdateController::getWhereLike($list, $rq->BRNDNM, "fmst.BRNDNM");
        }

        // 物流会社
        if ($rq->filled("PDCPCDNM")) {
            $list = InsertUpdateController::getWhereLike($list, $rq->PDCPCDNM, "mmst2.CUSTNM");
        }

        // 車両No.
        if ($rq->filled("PCARNO")) {
            $list = InsertUpdateController::getWhereLike($list, $rq->PCARNO, "RD.PCARNO");
        }

        // 日付指定時
        if ($rq->DateFrom && $rq->DateTo) {
            // From～Toの範囲内
            $list = $list->whereBetween($rq->DateType, [$rq->DateFrom, $rq->DateTo . " 23:59:59"]);
        } elseif ($rq->DateFrom) {
            // From以降
            $list = $list->whereDate($rq->DateType, ">=", $rq->DateFrom);
        } elseif ($rq->DateTo) {
            // To以前
            $list = $list->whereDate($rq->DateType, "<=", $rq->DateTo . " 23:59:59");
        }
        return $list;
    }

    /**
     * 依頼編集画面
     * @param  $id 依頼内容ID 
     * @return \Illuminate\View\View
     */
    public function edit(Request $rq)
    {
        // ログインユーザーの権限種別
        $authority_type = UsersController::authority_type();
        $id = $rq->id;
        if (!$id && $rq->request_detail_id) {
            // カレンダ画面の手配詳細のダブルクリックで展開
            $id = RequestsDetail::where("request_detail_id", $rq->request_detail_id)->value("request_id");
        }
        $titleheader = "依頼編集";
        $user = [];
        $title_header = "";
        $ErrMsg = "";
        $active_user_id = NULL;
        $classtitle = "form-show";
        $user = $this->_getUser();
        $classtitle = "form-new";
        if (!$id) {
            $data = $dataRD =  [];
        } else {
            // ・ログインユーザに“受入担当”または“配車担当”の権限があり、かつ取得した依頼データの「ロックユーザ」がNullの場合、以下のとおり依頼データを更新
            if (array_key_exists(1, $authority_type) || array_key_exists(2, $authority_type)) {
                DB::table($this->t_requests)
                    ->where("request_id", $id)
                    ->whereNull("locked_user")
                    ->update(["locked_user" => Auth::user()->user_id]);
            }
            $data = $this->_getRequest($id);
            if (!$data && !Session::get('errors')) {
                return redirect()->route("a.rsearch");
            }

            // 表示する依頼データの「ロックユーザ」がログインユーザIDと異なる場合
            $active_user_id = ($data["locked_user"] != Auth::user()->user_id) ? "form-diabled" : "";
            if ($active_user_id && $data["locked_user"]) {
                // ロックユーザ（locked_user）がログインユーザ以外の場合、画面表示時に以下メッセージを表示する
                $ErrMsg = str_replace("{p}",  $data["locked_userNM"], GetMessage::getMessageByID("error002"));
            }
            $dataRD = DB::table($this->t_request_details)
                ->select(
                    "RD.request_detail_id",
                    "RD.is_nohoped",
                    "RD.information",
                    "S.status_name",
                    "S.font_color",
                    "S.background_color",
                    "S.status_id",
                    "RD.BRNDCD",
                    "fmst.BRNDNM",
                    "RD.PDCPCD",
                    "mmst.CUSTNM AS PDCPNM",
                    "RD.car_type",
                    "E.enum_value as car_typeNM"
                )
                ->selectRaw("CONCAT(RD.PCARNO,'-',RD.CIDXNO,'-',RD.PDCPCD,'-',RD.PDRVCD) as car_detail_id")
                ->selectRaw("DATE_FORMAT(RD.hoped_on, '%Y/%m/%d') AS hoped_on")
                ->selectRaw("DATE_FORMAT(RD.recovered_on, '%Y/%m/%d') AS recovered_on")
                ->selectRaw("CASE WHEN RD.receptioned_at IS NOT NULL
                                    THEN CONCAT(DATE_FORMAT(RD.receptioned_at, '%Y/%m/%d<br>%H:%i '),'<br>',IFNULL(u1.family_name, ''))
                                    ELSE null
                            END AS receptionNM")

                ->selectRaw("CASE WHEN RD.dispatched_at IS NOT NULL
                                THEN CONCAT(DATE_FORMAT(RD.dispatched_at, '%Y/%m/%d<br>%H:%i '),'<br>',IFNULL(u2.family_name, ''))
                                ELSE null
                            END AS dispatchedNM")

                ->selectRaw("CASE WHEN RD.confirmed_at IS NOT NULL
                                THEN CONCAT(DATE_FORMAT(RD.confirmed_at, '%Y/%m/%d<br>%H:%i '),'<br>',IFNULL(u3.family_name, ''))
                                ELSE null
                            END AS confirmedNM")

                ->selectRaw("CASE WHEN RD.acceptanced_at IS NOT NULL
                                THEN CONCAT(DATE_FORMAT(RD.acceptanced_at, '%Y/%m/%d<br>%H:%i '),'<br>',IF(RD.acceptanced_value IS NULL, '', CAST(RD.acceptanced_value AS CHAR) + 0), IFNULL(RD.acceptanced_unit, ''))
                                ELSE null
                            END AS acceptancedNM")
                ->selectRaw("CONCAT(RD.PCARNO, '：', IFNULL(vmst.DVSIKJ, ''), IF(IFNULL(vmst.KTELNO, '') = '','','<br>'), IFNULL(vmst.KTELNO, '')) AS title2")
                ->join($this->m_statuses, "S.status_id", "RD.status_id")
                ->leftJoin("m_users as u1", "u1.user_id", "RD.receptioned_user")
                ->leftJoin("m_users as u2", "u2.user_id", "RD.dispatched_user")
                ->leftJoin("m_users as u3", "u3.user_id", "RD.confirmed_user")
                ->leftJoin("m_users as u4", "u4.user_id", "RD.acceptanced_user")
                ->leftJoin($this->fmst, "fmst.BRNDCD", "RD.BRNDCD")
                ->leftJoin($this->mmst, "mmst.CUSTCD", "RD.PDCPCD")
                ->leftJoin($this->enums, function ($join) {
                    $join->on("E.enum_key", "RD.car_type");
                    $join->whereRaw("E.enum_id = 'car_type'");
                })
                ->leftJoin($this->vmst, function ($join) {
                    $join->on("RD.PDCPCD", "vmst.PDCPCD");
                    $join->on("RD.PDRVCD", "vmst.PDRVCD");
                })
                ->where("RD.request_id", $id)
                ->orderBy("RD.seq_no", "ASC")
                ->get()->toArray();

            $rq->CUSTCD = $data->CUSTCD;
            foreach ($dataRD as $key => $rd) {
                $rq->BRNDCD = $dataRD[$key]->BRNDCD;
                $rq->PDCPCD = $dataRD[$key]->PDCPCD;
                // 物流会社一覧
                $dataRD[$key]->list_smst = $this->getListSmst($rq);
                // 車両種別一覧
                $dataRD[$key]->list_car_type = $this->getListCarType($rq);
                // 車両No：運転手
                $rq->recovered_on = $dataRD[$key]->recovered_on;
                $dataRD[$key]->list_car = $this->getListCar($rq);
            }
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

        $listPDCPCDExport = [];

        // 物流会社のポップアップ
        if (Session::has('ExportMsg')) {
            // ・物流会社が選択可能
            // 　※以下検索条件で取得した取引先マスタの「取引先コード」をIDとし、「取引先名称」を表示
            $listPDCPCDExport = DB::table($this->mmst)->select("mmst.CUSTCD", "mmst.CUSTNM", "mmst.OUTPOS")
                ->join($this->smst, "smst.PDCPCD", "mmst.CUSTCD")
                ->join($this->t_request_details, "smst.PDCPCD", "RD.PDCPCD")
                ->where("RD.request_id", $id)
                ->whereNotIn("RD.PDCPCD", config("const.CarWherePDCPCDList")) //・依頼内容．物流会社コード <> 自社（※１）
                ->whereNotIn("RD.status_id", ["", config("const.RDStatus.TEMP"), config("const.RDStatus.COMP")]) //・依頼内容．状態 がNULL、一時、完了以外
                ->groupBy("mmst.CUSTCD", "mmst.CUSTNM", "mmst.OUTPOS")
                ->orderBy("mmst.OUTPOS")
                ->get();
        }
        return view("admin.collect.request.edit", compact(
            "data",
            "dataRD",
            "listproducts",
            "titleheader",
            "classtitle",
            "user",
            "status",
            "request_type",
            "authority_type",
            "listPDCPCDExport",
            "id",
            "active_user_id",
            "ErrMsg"
        ));
    }

    /**
     *　画面解放時(unload)
     * @param  int $id
     */
    public function updateLockUser(Request $rq)
    {
        DB::table($this->t_requests)
            ->where("request_id", $rq->id)
            ->where("locked_user", Auth::user()->user_id)
            ->update(["locked_user" => NULL]);
    }

    /**
     *　物流会社一覧取得
     * @param  Request $rq
     * @return 一覧
     */
    public function getListSmst(Request $rq)
    {
        $list = DB::table($this->mmst)->select("mmst.OUTPOS", "mmst.CUSTCD", "mmst.CUSTNM")
            ->join($this->smst, function ($join) {
                $join->on("smst.PDCPCD", "mmst.CUSTCD"); // 取引先マスタ．取引先コード ＝ 物流費マスタ．物流会社コード
                $join->where("smst.BRNDKB", 1); // 物流費マスタ．銘柄区分 ＝ '1'（受入）
            })
            ->where("smst.CUSTCD", $rq->CUSTCD) // 物流費マスタ．取引先コード ＝ 表示行の「お取引先ID」の選択値
            ->where("smst.BRNDCD", $rq->BRNDCD) // 物流費マスタ．銘柄コード ＝ 表示行の「銘柄」の選択値
            ->groupBy("mmst.OUTPOS", "mmst.CUSTCD", "mmst.CUSTNM")
            ->orderBy("mmst.OUTPOS")
            ->get();
        return $list;
    }

    /**
     *　車両種別一覧取得
     * @param  Request $rq
     * @return 一覧
     */
    public function getListCarType(Request $rq)
    {
        $list = DB::table($this->smst)
            ->select("E2.enum_key", "E2.enum_value", "E2.enum_sort")
            ->join($this->enums2, function ($join) {
                $join->on("E2.enum_key", "smst.car_type"); //・汎用マスタ．キー ＝ 物流費マスタ．車両種別
                $join->whereRaw("E2.enum_id = 'car_type'"); //・汎用マスタ．マスタコード ＝ “car_type”
            })
            ->where("smst.CUSTCD", $rq->CUSTCD) // 物流費マスタ．取引先コード ＝ 表示行の「お取引先ID」の選択値
            ->where("smst.PDCPCD", $rq->PDCPCD) // 物流費マスタ．物流会社コード ＝ 表示行の「物流会社」の選択値
            ->where("smst.BRNDKB", 1) //・物流費マスタ．銘柄区分 ＝ '1'（受入）
            ->where("smst.BRNDCD", $rq->BRNDCD) // 物流費マスタ．銘柄コード ＝ 表示行の「銘柄」の選択値
            ->groupBy("E2.enum_key", "E2.enum_value", "E2.enum_sort")
            ->orderBy("E2.enum_sort")
            ->get();
        return $list;
    }

    /**
     *　車両No：運転手取得
     * @param  Request $rq
     * @return 一覧
     */
    public function getListCar(Request $rq)
    {
        $list = DB::table($this->vmst)
            ->select(
                "rmst.PCARNO",
                "rmst.CIDXNO",
                "rmst.PDCPCD",
                "rmst.PDRVCD",
                "vmst.DVSIKJ"
            )
            ->selectRaw("CONCAT(rmst.PCARNO,'-',rmst.CIDXNO,'-',rmst.PDCPCD,'-',rmst.PDRVCD) as id")
            ->selectRaw("CONCAT(rmst.PCARNO, '：', IFNULL(vmst.DVSIKJ, '')) AS title")
            ->selectRaw("CONCAT(rmst.PCARNO, '：', IFNULL(vmst.DVSIKJ, ''), IF(IFNULL(vmst.KTELNO, '') = '','','<br>'), IFNULL(vmst.KTELNO, '')) AS title2")
            ->join($this->rmst, function ($join) {
                $join->on("rmst.PDCPCD", "vmst.PDCPCD");
                $join->on("rmst.PDRVCD", "vmst.PDRVCD");
            })
            ->where("rmst.PDCPCD", $rq->PDCPCD);

        if ($rq->recovered_on) {
            // ・車両マスタ．有効期間(開始日) <= 表示行の「回収日」（yyyymmdd形式）
            // ・車両マスタ．有効期間(終了日) >= 表示行の「回収日」（yyyymmdd形式）
            // ・運転手マスタ．有効期間(開始日) <= 表示行の「回収日」（yyyymmdd形式）
            // ・運転手マスタ．有効期間(終了日) >= 表示行の「回収日」（yyyymmdd形式）
            $list = $list->where("vmst.SRTDAY", "<=", $rq->recovered_on)
                ->where("vmst.ENDDAY", ">=", $rq->recovered_on)
                ->where("rmst.SRTDAY", "<=", $rq->recovered_on)
                ->where("rmst.ENDDAY", ">=", $rq->recovered_on);
        }
        $list = $list->orderBy("rmst.OUTPOS")
            ->orderByRaw("CONVERT(rmst.PCARNO, SIGNED) ")
            ->orderBy("rmst.CIDXNO")
            ->orderBy("vmst.OUTPOS")
            ->orderBy("vmst.PDCPCD")
            ->orderBy("vmst.PDRVCD")
            ->get();
        return $list;
    }

    /**
     *　車両No：運転手取得+車両種別一覧取得
     * @param  Request $rq
     * @return 一覧
     */
    public function getListCarTypeDetail(Request $rq)
    {
        $data["ListCarType"] =  $this->getListCarType($rq);
        $data["ListCar"] =  $this->getListCar($rq);
        return $data;
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
            // 表示する依頼データの「ロックユーザ」がログインユーザIDと異なる場合
            if ($data["locked_user"] != Auth::user()->user_id) {
                return redirect()->back()->withErrors([GetMessage::getMessageByID("error004")]);
            }
        }
        try {
            // 「物流会社」の自社一覧
            $listCarWherePDCPCDList = array_flip(config("const.CarWherePDCPCDList"));
            DB::beginTransaction();
            //【保存】ボタン
            if ($rq->btnOk == "save" || $rq->btnOk == "export") {
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
                $request_detail_id = $rq->request_detail_id; // 依頼内容ID一覧

                // 依頼内容IDが画面上の依頼内容に存在する確認
                foreach ($request_detail_id as $k => $rdid) {
                    if ($k > 0) {
                        $dataRD = $this->_getRD($rq, $request_id, $k);
                        if ($rdid && array_key_exists($rdid, $listRDold)) {
                            unset($listRDold[$rdid]);
                            unset($dataRD["status_id"]); // DB更新の時、状態を更新しない。
                            //　　－取得した依頼内容IDが画面上の依頼内容に存在する場合、依頼内容IDに合致する依頼内容データを更新
                            RequestsDetail::where("request_detail_id", $rdid)->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $dataRD));

                            //画面上の「回収日」が未入力か「物流会社」が自社（※１）以外で依頼内容ID0以外
                            // 「物流会社」が自社（※１）以外
                            if (array_key_exists($rq->PDCPCD[$k], $listCarWherePDCPCDList)) {
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
                                // 物流会社を自社＝＞他社に切り替えた場合、手配詳細データが削除される
                                $recovered_delete[] = $rdid;
                            }
                        } else {
                            // 取得した依頼内容IDが画面上の依頼内容に存在しない場合、依頼内容データを新規に作成
                            $request_detail_id[$k] = RequestsDetail::insertGetId(array_merge(InsertUpdateController::datainsert($this->_mysystemname), $dataRD));

                            // 「回収日」が入力済みで自社の場合
                            if ($rq->recovered_on[$k] && array_key_exists($rq->PDCPCD[$k], $listCarWherePDCPCDList)) {
                                $RDnew[] = $k;
                            }
                        }
                    }
                }
                // 　　－取得した依頼内容IDが画面上の依頼内容に存在しない場合、
                if ($listRDold) {
                    // 依頼内容IDに合致する依頼内容データを削除
                    RequestsDetail::whereIn("request_detail_id", $listRDold)->delete();
                    // 保存ボタンで表示している依頼データの「依頼種別」が0（引取）の場合
                    if ($rq->request_type == 0) {
                        // 依頼内容IDに合致する手配詳細データを削除
                        t_dispatch_details::whereIn("request_detail_id", $listRDold)->delete();
                    }
                }

                // 保存ボタンで表示している依頼データの「依頼種別」が0（引取）の場合
                if ($rq->request_type == 0) {
                    // 　　－画面上の「回収日」が未入力で依頼内容IDが0以外の場合、依頼内容IDに合致する手配詳細データを削除
                    if ($recovered_delete) {
                        t_dispatch_details::whereIn("request_detail_id", $recovered_delete)->delete();
                    }

                    //　　－画面上の「回収日」が入力済みで依頼内容IDに合致する手配詳細データが存在しない場合、以下を実施
                    if ($RDnew) {
                        foreach ($RDnew as $v) {
                            // 車両が未定の手配データを取得
                            $car_detail = explode("-", $rq->car_detail[$v]);
                            $dispatch_id = t_dispatchesAsD::select("dispatch_id")
                                ->where("PCARNO", (isset($car_detail[0]) && $car_detail[0]) ? $car_detail[0] : 0)
                                ->where("CIDXNO", (isset($car_detail[1]) && $car_detail[1]) ? $car_detail[1] : 0)
                                ->where("PDCPCD", (isset($car_detail[2]) && $car_detail[2]) ? $car_detail[2] : 0)
                                ->where("PDRVCD", (isset($car_detail[3]) && $car_detail[3]) ? $car_detail[3] : 0)
                                ->whereDate("dispatched_on", $rq->recovered_on[$v])
                                ->value("dispatch_id");
                            if (!$dispatch_id) {
                                // 未定の手配データが取得できなかった場合、手配データを新規に作成
                                $data = [
                                    "PCARNO" => (isset($car_detail[0]) && $car_detail[0]) ? $car_detail[0] : 0,
                                    "CIDXNO" => (isset($car_detail[1]) && $car_detail[1]) ? $car_detail[1] : 0,
                                    "PDCPCD" => (isset($car_detail[2]) && $car_detail[2]) ? $car_detail[2] : 0,
                                    "PDRVCD" => (isset($car_detail[3]) && $car_detail[3]) ? $car_detail[3] : 0,
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
                            //※値がNullまたは空欄の場合、取引先英名、取引先略名称、取引先名称の優先順位で値を取得
                            $cunm =  t_Requests::select("fmst.BRNDNM", "RD.information")
                                ->selectRaw("COALESCE(
                                                    IF(mmst.CUSTEN IS NULL or mmst.CUSTEN = '', null, mmst.CUSTEN),
                                                    IF(mmst.CUSTRK IS NULL or mmst.CUSTRK = '', null, mmst.CUSTRK),
                                                    IF(mmst.CUSTNM IS NULL or mmst.CUSTNM = '', null, mmst.CUSTNM)
                                                ) AS cunm")
                                ->join($this->mmst, "mmst.CUSTCD", "t_requests.CUSTCD")
                                ->join($this->t_request_details, "RD.request_id", "t_requests.request_id")
                                ->join($this->fmst, "fmst.BRNDCD", "RD.BRNDCD")
                                ->where("t_requests.request_id", $request_id)
                                ->where("RD.request_detail_id",  $request_detail_id[$v])
                                ->first();
                            // －手配詳細データを新規に作成
                            $data = [
                                "dispatch_id" => $dispatch_id, //・手配ID：上記で取得または新規作成した手配データの手配IDをセット
                                "sort_no" => $sort_no_total++, //・表示順：手配IDデータで検索した手配詳細データの表示順の最大値＋１をセット
                                "request_detail_id" => $request_detail_id[$v], //・依頼内容ID：「依頼内容ID」の値をセット
                                // ・指示：依頼データの「取引先コード」で取得した取引先マスタの値と依頼内容データの値、および選択した銘柄を以下形式でセット	
                                // 「取引先．取引先英名」（※）＋“：”＋選択した銘柄の名称
                                "instruct" => $cunm ?  $cunm->cunm . "：" . $cunm->BRNDNM : ""
                            ];
                            t_dispatch_details::insert(array_merge(InsertUpdateController::datainsert($this->_mysystemname), $data));
                        }
                    }
                    //  　－画面上の「回収日」が入力済みで依頼内容IDに合致する手配詳細データが存在して、かつ「回収日」と「手配日」が異なる場合、以下を実施
                    if ($RDChange) {
                        foreach ($RDChange as $v) {
                            // 車両が未定の手配データを取得
                            $car_detail = explode("-", $rq->car_detail[$v]);
                            $dispatch_id = t_dispatchesAsD::select("dispatch_id")
                                ->where("PCARNO", (isset($car_detail[0]) && $car_detail[0]) ? $car_detail[0] : 0)
                                ->where("CIDXNO", (isset($car_detail[1]) && $car_detail[1]) ? $car_detail[1] : 0)
                                ->where("PDCPCD", (isset($car_detail[2]) && $car_detail[2]) ? $car_detail[2] : 0)
                                ->where("PDRVCD", (isset($car_detail[3]) && $car_detail[3]) ? $car_detail[3] : 0)
                                ->whereDate("dispatched_on", $rq->recovered_on[$v])
                                ->value("dispatch_id");
                            if (!$dispatch_id) {
                                // －手配データが取得できなかった場合、手配データを新規に作成
                                $data = [
                                    "PCARNO" => (isset($car_detail[0]) && $car_detail[0]) ? $car_detail[0] : 0,
                                    "CIDXNO" => (isset($car_detail[1]) && $car_detail[1]) ? $car_detail[1] : 0,
                                    "PDCPCD" => (isset($car_detail[2]) && $car_detail[2]) ? $car_detail[2] : 0,
                                    "PDRVCD" => (isset($car_detail[3]) && $car_detail[3]) ? $car_detail[3] : 0,
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
                            t_dispatch_details::where("request_detail_id", $request_detail_id[$v])
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
            $return_url = redirect()->route("a.redit", ["id" => $request_id]);
            if ($rq->btnOk == "export") {
                // 引取依頼書出力のポップアップが表示される
                \Session::flash('ExportMsg', true);

                return  $return_url;
            }
            $btnName = "保存";
            return $return_url->withSuccess(str_replace("{p}", $btnName, GetMessage::getMessageByID("error007")));
        } catch (Throwable $e) {
            DB::rollBack();
            $ErrMsg = GetMessage::getMessageByID("error009");
            return redirect()->back()->withErrors($ErrMsg);
        }
    }

    /**
     *　依頼申込／依頼詳細画面
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    public function redit_ajax(Request $rq)
    {
        $k = $rq->btnAjaxRow; // 選択行ID
        $request_id = $rq->request_id; //依頼ID
        $request_detail_id_k = $rq->request_detail_id[$k];
        $btnName = null;
        // 「依頼ID」が0以外の場合は「依頼ID」がチェックする
        if ($rq->request_id) {
            $data = $this->_getRequest($rq->request_id);
            if (!$data) {
                // ・「依頼ID」が合致する依頼データが存在しない場合、以下エラーメッセージを表示して、処理を中止
                return redirect()->route("a.rsearch", ["id" => 0])->withErrors([GetMessage::getMessageByID("error008")]);
            }
            // 表示する依頼データの「ロックユーザ」がログインユーザIDと異なる場合
            if ($data["locked_user"] != Auth::user()->user_id) {
                return redirect()->back()->withErrors([GetMessage::getMessageByID("error004")]);
            }
            // ・クリックした行の「依頼内容ID」に合致する依頼内容データの状態が表示されている状態と異なる場合、以下エラーメッセージを表示して、処理を中止
            $status_id = RequestsDetail::where("request_detail_id", $request_detail_id_k)->value("status_id");
            if ($status_id && $status_id != $rq->status[$k]) {
                return redirect()->back()->withErrors([GetMessage::getMessageByID("error018")]);
            }
            // 行追加、もしくは、行複写した場合、状態を「受付」とし、「受付」「依頼」ボタンを押下可とする。
            if (!$status_id && !$request_detail_id_k) {
                // 依頼内容テーブルへ追加
                $data = $this->_getRD($rq, $request_id, $k);
                $request_detail_id_k = RequestsDetail::insertGetId(array_merge(InsertUpdateController::datainsert($this->_mysystemname), $data));
            }
        }
        try {
            // 「物流会社」の自社一覧
            $listCarWherePDCPCDList = array_flip(config("const.CarWherePDCPCDList"));
            DB::beginTransaction();
            //【保存】ボタン
            if ($rq->btnAjax == "RECEPT" || $rq->btnAjax == "DISP" || $rq->btnAjax == "CONFIRM" || $rq->btnAjax == "ACCEPT" || $rq->btnAjax == "REVERT") {
                if ($rq->btnAjax == "RECEPT") {
                    $btnName = "受付";
                    $status_new = config("const.RDStatus.DISP");

                    if (!$rq->request_type == 0) {
                        $status_new = config("const.RDStatus.CUSTOMER");    //持込の場合は状態に顧客をセット
                    } elseif (!array_key_exists($rq->PDCPCD[$k], $listCarWherePDCPCDList)) {
                        $status_new = config("const.RDStatus.REQUEST");     //引取で他社の場合は状態に依頼をセット
                    }
                }
                if ($rq->btnAjax == "DISP") {
                    $btnName = "配車";
                    $status_new = config("const.RDStatus.CUSTOMER");
                }
                if ($rq->btnAjax == "CONFIRM") {
                    $btnName = "確認";
                    $status_new = config("const.RDStatus.CONFIRM");
                }
                if ($rq->btnAjax == "ACCEPT") {
                    $btnName = "受入";
                    $status_new = config("const.RDStatus.COMP");
                }
                if ($rq->btnAjax == "REVERT") {
                    $btnName = "取差戻";
                    // ・状態：表示されている状態が“配車”の場合“受付”、“顧客”の場合“配車”、“確定”の場合“顧客”のIDをセット
                    if ($rq->status[$k] == config("const.RDStatus.DISP")) {
                        $status_new = config("const.RDStatus.RECEPT");
                    }
                    if ($rq->status[$k] == config("const.RDStatus.CUSTOMER")) {
                        $status_new = config("const.RDStatus.DISP");
                        if (($rq->request_type == 1) || (!array_key_exists($rq->PDCPCD[$k], $listCarWherePDCPCDList))) {
                            // 依頼種別が持込の場合は状態が“顧客”で「取差戻」を行った場合、“受付”とする
                            // 依頼種別が引取で物流会社が他社の場合は状態が“顧客”で「取差戻」を行った場合、“受付”とする
                            $status_new = config("const.RDStatus.RECEPT");
                        }
                    }
                    if ($rq->status[$k] == config("const.RDStatus.CONFIRM")) {
                        $status_new = config("const.RDStatus.CUSTOMER");
                    }
                }
                // 　　－クリックした行の依頼内容IDに合致する依頼内容データを更新 | 物流会社が自社:同じ
                $dataRD = $this->_getRD2($rq, $k, $status_new);
                RequestsDetail::where("request_detail_id", $request_detail_id_k)->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $dataRD));

                // ・表示している依頼データの「依頼種別」が0（引取）でかつ、クリックした行の物流会社が自社（※１）の場合、以下を実施
                if (array_key_exists($rq->PDCPCD[$k], $listCarWherePDCPCDList) && $rq->request_type == 0) {
                    // 　　－クリックした行の「回収日」が未入力で依頼内容IDが0以外の場合、依頼内容IDに合致する手配詳細データを削除
                    if (!$rq->recovered_on[$k] && $request_detail_id_k) {
                        t_dispatch_details::where("request_detail_id", $request_detail_id_k)->delete();
                    }
                    if ($rq->recovered_on[$k]) {
                        //※値がNullまたは空欄の場合、取引先英名、取引先略名称、取引先名称の優先順位で値を取得
                        $cunm =  t_Requests::select("fmst.BRNDNM", "RD.information")
                            ->selectRaw("COALESCE(
                                IF(mmst.CUSTEN IS NULL or mmst.CUSTEN = '', null, mmst.CUSTEN),
                                IF(mmst.CUSTRK IS NULL or mmst.CUSTRK = '', null, mmst.CUSTRK),
                                IF(mmst.CUSTNM IS NULL or mmst.CUSTNM = '', null, mmst.CUSTNM)
                            ) AS cunm")
                            ->join($this->mmst, "mmst.CUSTCD", "t_requests.CUSTCD")
                            ->join($this->t_request_details, "RD.request_id", "t_requests.request_id")
                            ->join($this->fmst, "fmst.BRNDCD", "RD.BRNDCD")
                            ->where("t_requests.request_id", $request_id)
                            ->where("RD.request_detail_id",  $request_detail_id_k)
                            ->first();
                        // 車両が未定の手配データを取得
                        $car_detail = explode("-", $rq->car_detail[$k]);
                        $dispatch_id = t_dispatchesAsD::select("dispatch_id")
                            ->where("PCARNO", (isset($car_detail[0]) && $car_detail[0]) ? $car_detail[0] : 0)
                            ->where("CIDXNO", (isset($car_detail[1]) && $car_detail[1]) ? $car_detail[1] : 0)
                            ->where("PDCPCD", (isset($car_detail[2]) && $car_detail[2]) ? $car_detail[2] : 0)
                            ->where("PDRVCD", (isset($car_detail[3]) && $car_detail[3]) ? $car_detail[3] : 0)
                            ->whereDate("dispatched_on", $rq->recovered_on[$k])
                            ->value("dispatch_id");
                        if (!$dispatch_id) {
                            // 未定の手配データが取得できなかった場合、手配データを新規に作成
                            $data = [
                                "PCARNO" => (isset($car_detail[0]) && $car_detail[0]) ? $car_detail[0] : 0,
                                "CIDXNO" => (isset($car_detail[1]) && $car_detail[1]) ? $car_detail[1] : 0,
                                "PDCPCD" => (isset($car_detail[2]) && $car_detail[2]) ? $car_detail[2] : 0,
                                "PDRVCD" => (isset($car_detail[3]) && $car_detail[3]) ? $car_detail[3] : 0,
                                "dispatched_on" => $rq->recovered_on[$k],
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
                            "dispatch_id" => $dispatch_id, //・手配ID：上記で取得または新規作成した手配データの手配IDをセット
                            "sort_no" => $sort_no_total++, //・表示順：手配IDデータで検索した手配詳細データの表示順の最大値＋１をセット
                        ];
                        if (!t_dispatch_details::where("request_detail_id", $request_detail_id_k)->count()) {
                            // 　　－クリックした行の「回収日」が入力済みで依頼内容IDに合致する手配詳細データが存在しない場合、以下を実施

                            $data["instruct"] = $cunm ?  $cunm->cunm . "：" . $cunm->BRNDNM : "";
                            // －手配詳細データを新規に作成
                            $data["request_detail_id"] = $request_detail_id_k;
                            t_dispatch_details::insert(array_merge(InsertUpdateController::datainsert($this->_mysystemname), $data));
                        } else {
                            if ($rq->btnAjax == "RECEPT") {
                                // ・指示：依頼データの「取引先コード」で取得した取引先マスタの値と依頼内容データの値、および選択した銘柄を以下形式でセット	
                                // 「取引先．取引先英名」（※）＋“：”＋選択した銘柄の名称
                                // ※値がNullまたは空欄の場合、取引先英名、取引先略名称、取引先名称の優先順位で値を取得
                                $data["instruct"] = $cunm ?  $cunm->cunm . "：" . $cunm->BRNDNM : "";
                            }
                            // 　－クリックした行の「回収日」が入力済みで依頼内容IDに合致する手配詳細データが存在した場合、以下を実施
                            // －依頼内容IDに合致する手配詳細データを更新
                            t_dispatch_details::where("request_detail_id", $request_detail_id_k)
                                ->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data));
                        }
                    }
                } else {
                    // 表示している依頼データの「依頼種別」が1（持込）、又はクリックした行の物流会社が自社（※１）以外の場合、以下を実施
                    // ・クリックした行の物流会社が自社（※１）以外の場合、以下を実施
                    // 　　－クリックした行の依頼内容IDに合致する手配詳細データが存在する場合、依頼内容IDに合致する手配詳細データを削除								
                    t_dispatch_details::where("request_detail_id", $request_detail_id_k)->delete();
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
            // 受付ボタン：・依頼種別が1(持込)の場合、画面のメールアドレスに対して、メールを送信
            // 配車ボタン：・画面のメールアドレスに対して、メールを送信
            if (($rq->btnAjax == "RECEPT" && $rq->request_type == 1) || $rq->btnAjax == "DISP") {
                $this->sendMail($request_id, $request_detail_id_k, "button");
            }
            $return_url = redirect()->route("a.redit", ["id" => $request_id]);
            if ($btnName) {
                return $return_url->withSuccess(str_replace("{p}", $btnName, GetMessage::getMessageByID("error007")));
            } else {
                return $return_url;
            }
        } catch (Throwable $e) {
            DB::rollBack();
            $ErrMsg = GetMessage::getMessageByID("error009");
            return redirect()->back()->withErrors($ErrMsg);
        }
    }

    /**
     *　依頼内容：配車ボタンをクリック時、メールを送信
     * @param  $id 依頼ID
     * @param  $rdid 依頼内容ID
     * @param  $type 確認・更新
     * @return メール送信
     */
    public function sendMail($id, $rdid, $type)
    {
        // 依頼テーブルデータ
        $data = t_Requests::select("t_requests.*", "t_requests.request_id as request_no ")
            ->selectRaw("DATE_FORMAT(applied_at, '%Y年%m月%d日') AS applied_at")
            ->selectRaw("eRT.enum_value AS request_type_name")
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
            ->leftJoin("m_enums AS eRT", function ($join) {
                $join->whereRaw("eRT.enum_id = 'request_type'");
                $join->on("t_requests.request_type", "eRT.enum_key");
            })
            ->where("request_id", $id)->get()->first();
        if ($data) {
            // 依頼内容テーブルデータ
            $dataRD = DB::table($this->t_request_details)
                ->select("RD.information", "fmst.BRNDNM")
                ->selectRaw("DATE_FORMAT(RD.hoped_on, '%Y年%m月%d日') AS hoped_on")
                ->selectRaw("DATE_FORMAT(RD.recovered_on, '%Y年%m月%d日') AS recovered_on")
                ->leftJoin($this->fmst, "fmst.BRNDCD", "RD.BRNDCD")
                ->where("RD.request_detail_id", $rdid)
                ->where("RD.request_id", $id)->get();

            // 会社の情報
            $company = array(
                "COMPANY_PHONE" => config('const.CompanyInfo.phone'),
                "COMPANY_MAIL" =>  config('const.CompanyInfo.mail'),
                "COMPANY_MANAGER" => config('const.CompanyInfo.manager'),
            );

            // 引取依頼システムの依頼申込画面のURL
            $data["request_url"] = route("r.detail", ["id" => $id]);

            //複数のメールアドレス対応
            $mailtolist = "";
            if (!empty($data->mail_address)) $mailtolist = explode(",", $data->mail_address);
            $mailcclist = "";
            if ($data->contact_type && !empty($data->ccmail_address)) $mailcclist = explode(",", $data->ccmail_address);
            if ($mailcclist == "") {
                $mailcclist = explode(",", config("const.Mail.MAIL_CC"));
            } else {
                $mailcclist = array_merge($mailcclist, explode(",", config("const.Mail.MAIL_CC")));
            }

            // メール送信設定
            $mailsetting = array(
                "to" => $data->contact_type ? $mailtolist : "",
                "from" => config("const.Mail.MAIL_FROM_ADDRESS"),
                "fromname" => config("const.Mail.MAIL_FROM_NAME"),
                "cc" => $mailcclist,
            );
            // メールテンプレートのデータ
            $dataMail = [
                'data' => $data,
                "dataRD" => $dataRD,
                "company" => $company,
                "mailsetting" => $mailsetting,
                "type" => $type
            ];

            // メール送信
            Mail::send(new \App\Mail\AppMailCUSTOMER($dataMail));
        }
    }
    /**
     *【依頼書を出力】実行
     * @param  Request $rq
     * @return PDF
     */
    public function redit_export(Request $rq)
    {
        // 「依頼ID」が0以外の場合は「依頼ID」がチェックする
        if ($rq->request_id) {
            if (!$this->_getRequest($rq->request_id)) {
                // ・「依頼ID」が合致する依頼データが存在しない場合、以下エラーメッセージを表示して、処理を中止
                return redirect()->route("a.rsearch", ["id" => 0])->withErrors([GetMessage::getMessageByID("error008")]);
            }
        }
        // 　　－以下の検索条件に合致する依頼内容データを更新
        RequestsDetail::where("request_id", $rq->request_id)
            ->where("PDCPCD", $rq->PDCPCDEExport)
            ->where("status_id", config("const.RDStatus.RECEPT"))
            ->update([
                "status_id" => config("const.RDStatus.REQUEST"),
                "receptioned_at" => Carbon\Carbon::now()->format("Y/m/d H:i:s"),
                "receptioned_user" => Auth()->user()->user_id
            ]);

        // 　　－以下条件に合致する依頼内容データを取得し、PDFファイルで出力
        $data["PDCPCD"] = DB::table($this->mmst)->select("mmst.CUSTNM", "tmst.OUTPOS")
            ->selectRaw("CONCAT(IFNULL(tmst.CSTSPN, ''), IF(IFNULL(tmst.CSTSPN, '') != '' AND IFNULL(tmst.CSTHPN, '') != '','　',''), IFNULL(tmst.CSTHPN, '')) AS CST")
            ->selectRaw("CONCAT(IFNULL(tmst.TCSIKJ, ''), IF(IFNULL(tmst.TCSIKJ, '') != '' AND IFNULL(tmst.TCMEKJ, '') != '','　',''), IFNULL(tmst.TCMEKJ, '')) AS TCM")
            ->join($this->smst, "smst.PDCPCD", "mmst.CUSTCD")
            ->leftJoin($this->tmst, "tmst.CUSTCD", "mmst.CUSTCD")
            ->where("smst.PDCPCD", $rq->PDCPCDEExport)
            ->orderBy("tmst.OUTPOS")
            ->first();
        $data["RD"] = DB::table($this->t_request_details)
            ->select("RD.PDCPCD", "RD.BRNDCD", "RD.created_at", "RD.seq_no", "RD.request_id")
            ->selectRaw("IF(RD.hoped_on IS NULL, IFNULL(RD.information, ''), CONCAT(DATE_FORMAT(RD.hoped_on, '%Y/%m/%d'), '：', IFNULL(RD.information, ''))) AS hoped")
            ->where("RD.request_id", $rq->request_id)
            ->where("RD.PDCPCD", $rq->PDCPCDEExport)
            ->whereNotIn("RD.status_id", ["", config("const.RDStatus.TEMP"), config("const.RDStatus.COMP")])
            ->groupBy("RD.PDCPCD", "RD.BRNDCD", "RD.hoped_on", "RD.information", "RD.created_at", "RD.seq_no", "RD.request_id")
            ->orderBy("RD.created_at")
            ->orderBy("RD.request_id")
            ->orderBy("RD.seq_no")
            ->get()->toArray();

        $data["R"] = DB::table($this->t_requests)->select("mmst.CUSTNM", "fmst.BRNDNM", "R.request_note", "RD.BRNDCD")
            ->join($this->t_request_details, "RD.request_id", "R.request_id")
            ->join($this->mmst, "mmst.CUSTCD", "R.CUSTCD")
            ->join($this->fmst, "fmst.BRNDCD", "RD.BRNDCD")
            ->where("R.request_id", $rq->request_id)
            ->groupBy("mmst.CUSTNM", "fmst.BRNDNM", "R.request_note", "RD.BRNDCD")
            ->get();

        //画面再表示用にCookieを発行
        Cookie::queue("downloaded", "yes", 1, "", "", false, false);

        $data["request_id"] = $rq->request_id;
        $data["CompanyInfo"] = config("const.CompanyInfo");
        $now =  Carbon\Carbon::now();
        $data["today"] = $this->wareki($now->format('Y')) . $now->format('m月d日'); //「和暦 yy年mm月dd日」
        $pdf = PDF::loadView("admin.collect.request.edit_export", $data);
        return $pdf->download('引取依頼書_' . $rq->request_id . '_' . $rq->PDCPCDEExport . '.pdf');
    }

    // 西暦 => 和暦
    public function wareki($year)
    {
        $eras = [
            ['year' => 2018, 'name' => '令和'],
            ['year' => 1988, 'name' => '平成'],
            ['year' => 1925, 'name' => '昭和'],
            ['year' => 1911, 'name' => '大正'],
            ['year' => 1867, 'name' => '明治']
        ];

        foreach ($eras as $era) {

            $base_year = $era['year'];
            $era_name = $era['name'];

            if ($year > $base_year) {

                $era_year = $year - $base_year;

                if ($era_year === 1) {

                    return $era_name . '元年';
                }

                return $era_name . $era_year . '年';
            }
        }

        return null;
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
            //"request_type" => $rq->request_type, // ・依頼種別：画面の「引取／持込」の選択値のIDをセット
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
        $car_detail = (isset($rq->car_detail[$k]) && $rq->car_detail[$k]) ? explode("-", $rq->car_detail[$k]) : "";
        $dataRD = array(
            "request_id" => $request_id, // ・依頼ID：上記で新規に作成した依頼データの依頼IDをセット
            "seq_no" => $k, // ・連番：１から連番をセット
            "BRNDCD" => $rq->BRNDCD[$k], // ・商品コード：画面の依頼内容の「銘柄」の選択値のIDをセット
            "material_division" => m_material_divisions::where("BRNDCD", $rq->BRNDCD[$k])->first()->value("material_division"), //・原料区分コード：取引先コードと画面の依頼内容の「銘柄」の選択値で原料区分マスタを検索し、取得した「原料区分コード」の値をセット
            "status_id"  => config("const.RDStatus.RECEPT"), //状態：“受付”のIDをセット
            "hoped_on" => $rq->hoped_on[$k], // ・希望日：画面の依頼内容の「希望日」の値をセット
            "recovered_on" => $rq->recovered_on[$k], // ・回収日：画面の依頼内容の「回収日」の値をセット
            "information" => $rq->information[$k], // ・連絡事項：画面の依頼内容の「連絡事項」の値をセット
            "PDCPCD" => $rq->PDCPCD[$k], // 物流会社コード：画面の依頼内容の「物流会社」の選択値をセット
            "car_type" =>  $rq->car_type[$k], // ・車両種別：画面の依頼内容の「車両種別」の選択値をセット
            "PCARNO" => (isset($car_detail[0]) && $car_detail[0]) ? $car_detail[0] : 0, // ・車両番号：画面の依頼内容の「車両No：運転手」の選択値から車両番号をセット
            "CIDXNO" => (isset($car_detail[1]) && $car_detail[1]) ? $car_detail[1] : 0, // ・内訳番号：画面の依頼内容の「車両No：運転手」の選択値から内訳番号をセット
            "PDRVCD" => (isset($car_detail[3]) && $car_detail[3]) ? $car_detail[3] : 0 // ・運転手コード：画面の依頼内容の「車両No：運転手」の選択値から運転手コードをセット
        );
        return $dataRD;
    }

    /**
     *   依頼内容データを作成
     * @param  Request $rq
     * @param   int $status_id 状態ID
     * @param   int $k 選択行
     * @return \Illuminate\View\View
     */
    protected function _getRD2(Request $rq,  int $k, string $status_id)
    {
        $now = Carbon\Carbon::now()->format("Y/m/d H:i:s");
        $car_detail = $rq->car_detail[$k] ? explode("-", $rq->car_detail[$k]) : "";
        $dataRD = array(
            "status_id"  => $status_id, //状態をセット
            "recovered_on" => $rq->recovered_on[$k], // ・回収日：画面の依頼内容の「回収日」の値をセット
            "information" => $rq->information[$k], // ・連絡事項：画面の依頼内容の「連絡事項」の値をセット
            "PDCPCD" => $rq->PDCPCD[$k], // 物流会社コード：画面の依頼内容の「物流会社」の選択値をセット
            "car_type" =>  $rq->car_type[$k], // ・車両種別：画面の依頼内容の「車両種別」の選択値をセット
            "PCARNO" => (isset($car_detail[0]) && $car_detail[0]) ? $car_detail[0] : 0, // ・車両番号：画面の依頼内容の「車両No：運転手」の選択値から車両番号をセット
            "CIDXNO" => (isset($car_detail[1]) && $car_detail[1]) ? $car_detail[1] : 0, // ・内訳番号：画面の依頼内容の「車両No：運転手」の選択値から内訳番号をセット
            "PDRVCD" => (isset($car_detail[3]) && $car_detail[3]) ? $car_detail[3] : 0 // ・運転手コード：画面の依頼内容の「車両No：運転手」の選択値から運転手コードをセット
        );
        if ($rq->btnAjax == "RECEPT") {
            // 受付ボタン
            $dataRD["hoped_on"] = $rq->hoped_on[$k]; // ・希望日：画面の依頼内容の「希望日」の値をセット
            $dataRD["BRNDCD"] =  $rq->BRNDCD[$k]; // ・商品コード：画面の依頼内容の「銘柄」の選択値のIDをセット
            $dataRD["material_division"] = m_material_divisions::where("BRNDCD", $rq->BRNDCD[$k])->first()->value("material_division"); //・原料区分コード：取引先コードと画面の依頼内容の「銘柄」の選択値で原料区分マスタを検索し、取得した「原料区分コード」の値をセット
            $dataRD["receptioned_at"] = $now;
            $dataRD["receptioned_user"] = Auth()->user()->user_id;
        }
        if ($rq->btnAjax == "DISP") {
            // 配車ボタン
            $dataRD["hoped_on"] = $rq->hoped_on[$k]; // ・希望日：画面の依頼内容の「希望日」の値をセット
            $dataRD["dispatched_at"] = $now;
            $dataRD["dispatched_user"] = Auth()->user()->user_id;
        }
        if ($rq->btnAjax == "CONFIRM") {
            $dataRD["confirmed_at"] = $now;
            $dataRD["confirmed_user"] = Auth()->user()->user_id;
        }
        if ($rq->btnAjax == "ACCEPT") {
            $dataRD["acceptanced_at"] = $now;
            $dataRD["acceptanced_user"] = Auth()->user()->user_id;
        }

        if ($rq->btnAjax == "REVERT") {
            // ・状態：表示されている状態が“配車”の場合“受付”、“顧客”の場合“配車”、“確定”の場合“顧客”のIDをセット
            if ($rq->status[$k] == config("const.RDStatus.DISP")) {
                $dataRD["receptioned_at"] = null;
                $dataRD["receptioned_user"] = null;
            }
            if ($rq->status[$k] == config("const.RDStatus.CUSTOMER")) {
                $dataRD["dispatched_at"] = null;
                $dataRD["dispatched_user"] = null;
            }
            if ($rq->status[$k] == config("const.RDStatus.CONFIRM")) {
                $dataRD["confirmed_at"] = null;
                $dataRD["confirmed_user"] = null;
            }
        }
        return $dataRD;
    }
    /**
     *　依頼テーブルの情報
     * @param  $id 依頼ID
     * @return array 依頼
     */
    private function _getRequest($id)
    {
        return t_Requests::select("t_requests.*", "mmst.CUSTNM", "E.enum_value as request_typeNM", "u.user_id")
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
            ->selectRaw("u.user_id as applied_user")
            ->selectRaw("CONCAT(IFNULL(mmst.CUSTCD, ''),'-',u.user_id) AS CUSTCD_UserID")
            ->selectRaw("CONCAT(mmst.CUSTNM,'：',u.family_name) AS nameview")
            ->selectRaw("CONCAT(u2.family_name,'　',u2.first_name) as locked_userNM")
            ->join($this->enums, function ($join) {
                $join->on("E.enum_key", "t_requests.request_type");
                $join->where("E.enum_id", "request_type");
            })
            ->leftJoin($this->m_users, DB::raw("IF(t_requests.applied_user != '',t_requests.applied_user,t_requests.created_user)"), "u.user_id")
            ->leftJoin($this->tmst, function ($join) {
                $join->on("u.CUSTCD", "tmst.CUSTCD");
                $join->on("u.TCSTNO", "tmst.TCSTNO");
            })
            ->leftJoin($this->mmst, "t_requests.CUSTCD", "mmst.CUSTCD")
            ->leftJoin("m_users as u2", "u2.user_id", "t_requests.locked_user")
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

    /**
     * 依頼作成画面
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    public function create(Request $rq)
    {
        $user = [];
        $title_header = "依頼作成";
        $classtitle = "form-new";
        $listproducts = "";
        $user = $this->_getUser();
        $id = $rq->id;
        if (!$id) {
            $classtitle = "form-new";
            $title_header = "依頼作成（新規依頼）";
            $data = $dataRD =  [];
        } else {
            $data = $this->_getRequest($id);
            if (!$data && !Session::get('errors')) {
                return redirect()->route("a.rsearch");
            }
            if ($data["applied_at"]) {
                $classtitle = "form-show";
            } else {
                $classtitle = "form-temp";
            }
            $dataRD = DB::table($this->t_request_details)
                ->select("RD.request_detail_id",  "RD.is_nohoped",  "RD.information", "RD.status_id", "S.status_name", "S.font_color", "S.background_color", "RD.BRNDCD", "fmst.BRNDNM")
                ->selectRaw("DATE_FORMAT(RD.hoped_on, '%Y/%m/%d') AS hoped_on")
                ->selectRaw("DATE_FORMAT(RD.recovered_on, '%Y/%m/%d') AS recovered_on")
                ->join($this->m_statuses, "S.status_id", "RD.status_id")
                ->leftJoin($this->fmst, "RD.BRNDCD", "fmst.BRNDCD")
                ->where("RD.request_id", $id)
                ->orderBy("RD.seq_no", "ASC")
                ->get()->toArray();
            // 銘柄一覧
            $listproducts = DB::table($this->fmst)->select("fmst.BRNDNM", "fmst.BRNDCD", "kmst.OUTPOS")
                ->join($this->kmst, "kmst.BRNDCD", "fmst.BRNDCD")
                ->Join("m_material_divisions AS mMD", function ($join) {
                    $join->on("kmst.CUSTCD", "mMD.CUSTCD");
                    $join->on("kmst.BRNDCD", "mMD.BRNDCD");
                    $join->whereRaw("mMD.material_division <> ''");
                })
                ->where("kmst.CUSTCD", $data["CUSTCD"])
                ->whereRaw("kmst.BRNDKB = '1'")
                ->groupBy("fmst.BRNDNM", "fmst.BRNDCD", "kmst.OUTPOS")
                ->orderBy("kmst.OUTPOS")
                ->orderBy("fmst.BRNDCD")->get();
        }
        // 取引先選択一覧
        $now = Carbon\Carbon::now()->format("Ymd");
        $listCUSTNM = UserAsU::select("u.user_id", "u.family_name", "mmst.CUSTNM", "u.CUSTCD", "mmst.OUTPOS")
            ->selectRaw("CONCAT(mmst.CUSTNM,'：',u.family_name) AS nameview")
            ->selectRaw("CONCAT(mmst.CUSTCD,'-',u.user_id) AS CUSTCD_UserID")
            ->join($this->mmst, "mmst.CUSTCD", "u.CUSTCD")
            ->where("u.user_type", 1)
            ->where("mmst.SRTDAY", "<=", $now)
            ->where("mmst.ENDDAY", ">=", $now)
            ->orderBy("mmst.OUTPOS")
            ->orderBy("u.CUSTCD")
            ->orderBy("u.user_id")
            ->get();
        // 引取／持込
        $listrequesttype = DB::table("m_enums")->select("enum_key", "enum_value")
            ->whereRaw("enum_id = 'request_type'")
            ->orderBy("enum_sort")->get();

        // ログインユーザーの権限種別
        $authority_type = UsersController::authority_type();
        if (!array_key_exists(1, $authority_type)) {
            $classtitle = "form-show";
        }
        return view("admin.collect.request.create", compact("data", "dataRD", "listrequesttype", "listproducts", "title_header", "classtitle", "user", "listCUSTNM", "authority_type"));
    }

    /**
     * ・値変更時は選択値で取得したユーザマスタのデータ
     * @param  Request $rq
     * @return 配列　ユーザ情報と銘柄一覧
     */
    public function getCUSTCDInfo(Request $rq)
    {
        // 銘柄一覧
        $listproducts = DB::table($this->fmst)->select("fmst.BRNDNM", "fmst.BRNDCD", "kmst.OUTPOS")
            ->join($this->kmst, "kmst.BRNDCD", "fmst.BRNDCD")
            ->Join("m_material_divisions AS mMD", function ($join) {
                $join->on("kmst.CUSTCD", "mMD.CUSTCD");
                $join->on("kmst.BRNDCD", "mMD.BRNDCD");
                $join->whereRaw("mMD.material_division <> ''");
            })
            ->where("kmst.CUSTCD", $rq->CUSTCD)
            ->whereRaw("kmst.BRNDKB = '1'")
            ->groupBy("fmst.BRNDNM", "fmst.BRNDCD", "kmst.OUTPOS")
            ->orderBy("kmst.OUTPOS")
            ->orderBy("fmst.BRNDCD")->get();
        $html = "<option></option>";
        if ($listproducts) {
            foreach ($listproducts as $p) {
                $html .= '<option value="' . $p->BRNDCD . '">' . $p->BRNDNM . '</option>';
            }
        }
        // ユーザマスタのデータ
        $user = UserAsU::select("u.mail_address", "ccmail_address", "contact_type", "tel_no", "mmst.CUSTNM")
            ->selectRaw("CASE WHEN u.family_name IS NOT NULL AND u.first_name IS NOT NULL
                                THEN CONCAT(u.family_name, '　', u.first_name)
                                WHEN u.family_name IS NOT NULL OR u.first_name IS NOT NULL
                                THEN COALESCE(u.family_name, u.first_name)
                                WHEN tmst.TCSIKJ IS NOT NULL AND tmst.TCMEKJ IS NOT NULL
                                THEN CONCAT(tmst.TCSIKJ, '　', tmst.TCMEKJ)
                                ELSE COALESCE(tmst.TCSIKJ, tmst.TCMEKJ)
                        END AS manager_name")
            ->join($this->mmst, "mmst.CUSTCD", "u.CUSTCD")
            ->leftJoin($this->tmst, "tmst.TCSTNO", "u.TCSTNO")
            ->where("u.user_id", $rq->user_id)
            ->first();

        return [
            "user" => $user,
            "listproducts" => $html
        ];
    }

    /**
     *　依頼作成画面
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    public function createstore(Request $rq)
    {
        $btnName = null;
        // 「依頼ID」が0以外の場合は「依頼ID」がチェックする
        if ($rq->btnOk != "btnConfirme" && $rq->request_id) {
            $data = $this->_getRequest($rq->request_id);
            if ($data && $data["applied_at"]) {
                // ・「依頼ID」が合致する依頼データの「依頼ID」が0以外以外の場合、以下エラーメッセージを表示して、処理を中止
                return redirect()->back()->withErrors([GetMessage::getMessageByID("error006")]);
            }
            if (!$data) {
                return redirect()->route("a.rsearch")->withErrors([GetMessage::getMessageByID("error008")]);
            }
        }
        //確認ボタンクリック時はクリック行の状謡をチェック
        if ($rq->btnOk == "btnConfirme") {
            $dataRD = DB::table($this->t_request_details)
                ->select("RD.status_id")
                ->selectRaw("DATE_FORMAT(RD.recovered_on, '%Y/%m/%d') AS recovered_on")
                ->where("RD.request_detail_id", $rq->request_detail_id)
                ->get()->first();
            //取得した依頼内容データの状態、回収日が変更されていた場合エラー
            if (
                !$dataRD || $dataRD->status_id != config("const.RDStatus.CUSTOMER")
                || $dataRD->recovered_on != $rq->recovered_on
            ) {
                return redirect()->back()->withErrors([GetMessage::getMessageByID("error001")]);
            }
        }

        try {
            DB::beginTransaction();
            switch ($rq->btnOk) {
                    //【一時保存】ボタン
                case 'btnTemp':
                    $btnName = "一時保存";
                    if (!$rq->request_id) {
                        // ・「依頼ID」が0（新規依頼）の場合、以下を実施
                        $dataR = $this->_getR2($rq, true);
                        $request_id = t_Requests::insertGetId($dataR);

                        //  新規に依頼内容データを作成
                        RequestsDetail::insert($this->_getRD3($rq, $request_id));
                    } else {
                        $request_id = $rq->request_id;
                        // ・「依頼ID」が0以外（一時保存）の場合、以下を実施
                        $dataR = $this->_getR2($rq);
                        t_Requests::where("request_id", $request_id)->update($dataR);

                        //－依頼IDが合致する依頼内容データを削除して再作成
                        RequestsDetail::where("request_id", $request_id)->delete();
                        //  新規に依頼内容データを作成
                        RequestsDetail::insert($this->_getRD3($rq, $request_id));
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
                    break;

                    //【依頼申込】ボタン
                case 'btnAppOk':
                    $btnName = "依頼申込";
                    if (!$rq->request_id) {
                        // ・「依頼ID」が0（新規依頼）の場合、以下を実施
                        $dataR = $this->_getR2($rq, true, true);
                        $request_id = t_Requests::insertGetId($dataR);

                        //  新規に依頼内容データを作成
                        RequestsDetail::insert($this->_getRD3($rq, $request_id, true));
                    } else {
                        $request_id = $rq->request_id;
                        // ・「依頼ID」が0以外（一時保存）の場合、以下を実施
                        $dataR = $this->_getR2($rq, false, true);
                        t_Requests::where("request_id", $request_id)->update($dataR);

                        //－依頼IDが合致する依頼内容データを削除して再作成
                        RequestsDetail::where("request_id", $request_id)->delete();

                        //  新規に依頼内容データを作成
                        RequestsDetail::insert($this->_getRD3($rq, $request_id, true));
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
                    break;

                    //【削除】ボタン
                case 'btnDelele':
                    $btnName = "削除";
                    $request_id = $rq->request_id;
                    //・依頼IDが合致する依頼データを削除
                    t_Requests::where("request_id", $request_id)->delete();
                    // ・依頼IDが合致する依頼内容データを削除
                    RequestsDetail::where("request_id", $request_id)->delete();
                    break;
            }
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            $Errmsg = GetMessage::getMessageByID("error009");
            return redirect()->back()->withErrors($Errmsg);
        }
        if ($rq->btnOk == "btnTemp" || $rq->btnOk == "btnConfirme") {
            return redirect()->route("a.rcreate", ["id" => $request_id])->withSuccess(str_replace("{p}", $btnName, GetMessage::getMessageByID("error007")));
        } else {
            return redirect()->route("a.rsearch")->withSuccess(str_replace("{p}", $btnName, GetMessage::getMessageByID("error007")));
        }
    }
    /**
     *　依頼申込／依頼詳細画面
     *   依頼内容データを作成
     * @param  Request $rq
     * @param   int $request_id 依頼ID
     * @param   bool $app 【依頼申込】ボタンフラグ
     * @return 依頼内容一覧
     */
    private function _getRD3(Request $rq, int $request_id, bool $app = false)
    {
        $dataRD = [];
        $i = 0;
        foreach ($rq->BRNDCD as $k => $v) {
            $BRNDCD = $rq->BRNDCD;
            $information = $rq->information;
            $hoped_on = $rq->hoped_on;
            $is_nohoped = $rq->is_nohoped;
            $getFlag = false;
            if ($rq->btnOk == "btnTemp") {
                if ($BRNDCD[$k]) {
                    $getFlag = true;
                }
            }
            if ($rq->btnOk == "btnAppOk") {
                if ($BRNDCD[$k]) {
                    $getFlag = true;
                }
            }

            if ($getFlag) {
                $i++;
                $temp = array(
                    "request_id"  => $request_id, //依頼ID：上記で新規に作成した依頼データの依頼IDをセット
                    "seq_no"      => $i,          //連番：１から連番をセット
                    "BRNDCD"      => $BRNDCD[$k], //商品コード：画面の依頼内容の「銘柄」の選択値のIDをセット
                    "status_id"   => config("const.RDStatus.TEMP"), //状態：一時をセット
                    "hoped_on"    => isset($hoped_on[$k]) ? $hoped_on[$k] : null, //希望日：画面の依頼内容の「希望日」の値をセット
                    "is_nohoped"  => (isset($is_nohoped[$k])  && $is_nohoped[$k]) ? 1 : 0, //希望日なしフラグ：画面の依頼内容の「希望日なしフラグ」のチェック状態をセット
                    "information" => isset($information[$k]) ? $information[$k] : null,    //連絡事項：画面の依頼内容の「連絡事項」の値をセット
                );
                $temp = array_merge(InsertUpdateController::datainsert($this->_mysystemname), $temp);
                //【依頼申込】ボタン
                if ($app) {
                    $temp["status_id"] = config("const.RDStatus.RECEPT");   //状態に受付をセット
                }
                $dataRD[] = $temp;
            }
        }
        return $dataRD;
    }

    /**
     *　依頼申込／依頼詳細画面
     *   依頼データを作成
     * @param  Request $rq
     * @param   bool $insert 新規追加フラグ
     * @param   bool $app 【依頼申込】ボタンフラグ
     * @return 依頼申込情報
     */
    private function _getR2(Request $rq, bool $insert = false, bool $app = false)
    {
        $data =  array(
            "CUSTCD"       => $rq->CUSTCD, //・取引先コード：画面の「取引先選択」で選択されたユーザマスタの「取引先コード」の値をセット
            "request_type" => $rq->request_type,    //依頼種別：画面の「引取／持込」の選択値のIDをセット
            "request_note" => $rq->request_note,    //依頼備考：画面の「依頼備考」の値をセット
            "applied_user" => $rq->user_id  // ・申込者：画面の「取引先選択」で選択されたユーザIDの値をセット
        );

        //追加データ
        if ($insert) {
            $data = array_merge(InsertUpdateController::datainsert($this->_mysystemname), $data);
        } else {
            $data = array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data);
        }

        //【依頼申込】ボタン
        if ($app) {
            $data["request_no"]   = (t_Requests::max("request_no") ? t_Requests::max("request_no") : 0) + 1;  //依頼No：依頼データの最大「依頼No」＋１をセット（依頼データが存在しない場合は1）
            $data["applied_at"]   = Carbon\Carbon::now()->format("Y/m/d H:i:s"); //申込日時：現在日時をセット
        }

        return $data;
    }
}
