<?php

namespace App\Http\Controllers\Request;

use App\Http\Controllers\Controller;
use App\Http\Controllers\InsertUpdateController;
use Illuminate\Http\Request;
use Carbon;
use App\GetMessage;
use App\Models\t_Requests;
use App\Models\Status;
use App\Models\RequestsDetail;
use App\Models\UserAsU;
use App\Models\User;
use Auth;
use Mail;
use PDF;
use DB;
use Session;

class CollectController extends Controller
{
    protected $_mysystemname = "request";
    protected $m_users = "m_users as u";
    protected $_t_requests = "t_requests as R";
    protected $_t_request_details = "t_request_details as RD";
    protected $_m_statuses = "m_statuses as S";
    protected $mmst = "pldtcustmmst as mmst"; //取引先マスタ
    protected $tmst = "pldtcusttmst as tmst"; //取引先担当者マスタ
    protected $kmst = "pldtcustkmst as kmst"; //取引先単価マスタ
    protected $fmst = "pldtbrinfmst as fmst"; //商品マスタ

    /**
     *　依頼履歴画面
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    public function history(Request $rq)
    {
        $list = DB::table($this->_t_requests)
            ->select("R.request_id", "R.request_no", "RD.information", "RD.status_id", "S.status_name", "S.font_color", "S.background_color", "fmst.BRNDNM")
            ->selectRaw("eRT.enum_value AS RTypeNM")
            ->selectRaw("DATE_FORMAT(R.created_at, '%Y/%m/%d') AS created_at")
            ->selectRaw("DATE_FORMAT(R.applied_at, '%Y/%m/%d') AS applied_at")
            ->selectRaw("DATE_FORMAT(RD.hoped_on, '%Y/%m/%d') AS hoped_on")
            ->selectRaw("DATE_FORMAT(RD.recovered_on, '%Y/%m/%d') AS recovered_on")
            ->join($this->_t_request_details, "R.request_id", "RD.request_id")
            ->join($this->_m_statuses, "S.status_id", "RD.status_id")
            ->join($this->fmst, "fmst.BRNDCD", "RD.BRNDCD")
            ->leftJoin("m_enums AS eRT", function ($join) {
                $join->whereRaw("eRT.enum_id = 'request_type'");
                $join->on("R.request_type", "eRT.enum_key");
            })
            ->where("R.CUSTCD", Auth::user()->CUSTCD);

        if ($rq->status) {
            $list = $list->whereIn('RD.status_id', $rq->status);
        }
        // 期間のFrom～Toの双方が未指定の場合は検索条件としない
        if ($rq->DateFrom && $rq->DateTo) {
            $list = $list->whereBetween($rq->DateType, [$rq->DateFrom, $rq->DateTo . " 23:59:59"]);
        }
        if ($rq->DateFrom && !$rq->DateTo) {
            $list = $list->whereDate($rq->DateType, ">=", $rq->DateFrom);
        }
        if (!$rq->DateFrom && $rq->DateTo) {
            $list = $list->whereDate($rq->DateType, "<=", $rq->DateTo . " 23:59:59");
        }
        $list = $list->orderBy("R.created_at", "DESC")
            ->orderBy("R.request_id", "DESC")
            ->orderBy("RD.seq_no", "ASC");

        // ファイル出力
        $ErrMsg = null;
        if ($rq->btnExport) {
            $list2 = $list->get();
            if ($list2->count()) {
                $data["list"] = $list->get();
                $pdf = PDF::loadView('request.collect.historyexport', $data)->setPaper('a4', 'landscape');
                return $pdf->download('依頼履歴一覧.pdf');
            } else {
                $ErrMsg = GetMessage::getMessageByID("error001");
            }
        }
        $list = $list->paginate(20);
        $btnSearch = $rq->btnSearch;

        // 状態マスタ一覧
        $status = Status::where("is_deleted", 0)->orderBy("sort_no")->get();

        session()->flashInput($rq->input());
        return view("request.collect.history", compact("status", "list", "ErrMsg", "btnSearch"));
    }

    /**
     *　依頼申込／依頼詳細画面
     * @param  $id 依頼ID
     * @return \Illuminate\View\View
     */
    public function detail(int $id)
    {
        $user = [];
        $title_header = "";
        $classtitle = "form-show";
        $user = $this->_getUser();
        if (!$id) {
            $title_header = "依頼申込（新規依頼）";
            $classtitle = "form-new";
            $data = $dataRD =  [];
        } else {
            $data = $this->_getRequest($id);
            if (!$data && !Session::get('errors')) {
                return redirect()->route("r.history");
            }
            if ($data["applied_at"]) {
                $title_header = "依頼詳細";
                $classtitle = "form-show";
            } else {
                $title_header = "依頼申込（一時保存）";
                $classtitle = "form-temp";
            }
            $dataRD = DB::table($this->_t_request_details)
                ->select("RD.request_detail_id",  "RD.is_nohoped",  "RD.information", "RD.status_id", "S.status_name", "S.font_color", "S.background_color", "RD.BRNDCD", "fmst.BRNDNM")
                ->selectRaw("DATE_FORMAT(RD.hoped_on, '%Y/%m/%d') AS hoped_on")
                ->selectRaw("DATE_FORMAT(RD.recovered_on, '%Y/%m/%d') AS recovered_on")
                ->join($this->_m_statuses, "S.status_id", "RD.status_id")
                ->leftJoin($this->fmst, "RD.BRNDCD", "fmst.BRNDCD")
                ->where("RD.request_id", $id)
                ->orderBy("RD.seq_no", "ASC")
                ->get()->toArray();
        }

        // 引取／持込
        $listrequesttype = DB::table("m_enums")->select("enum_key", "enum_value")
            ->whereRaw("enum_id = 'request_type'")
            ->orderBy("enum_sort")->get();

        // 銘柄一覧
        $listproducts = DB::table($this->fmst)->select("fmst.BRNDNM", "fmst.BRNDCD", "kmst.OUTPOS")
            ->join($this->kmst, "kmst.BRNDCD", "fmst.BRNDCD")
            ->Join("m_material_divisions AS mMD", function ($join) {
                $join->on("kmst.CUSTCD", "mMD.CUSTCD");
                $join->on("kmst.BRNDCD", "mMD.BRNDCD");
                $join->whereRaw("mMD.material_division <> ''");
            })
            ->where("kmst.CUSTCD", Auth::user()->CUSTCD)
            ->whereRaw("kmst.BRNDKB = '1'")
            ->groupBy("fmst.BRNDNM", "fmst.BRNDCD", "kmst.OUTPOS")
            ->orderBy("kmst.OUTPOS")
            ->orderBy("fmst.BRNDCD")->get();
        return view("request.collect.detail", compact("data", "dataRD", "listrequesttype", "listproducts", "title_header", "classtitle", "user"));
    }

    /**
     *　依頼データの取得
     * @param  $id 依頼ID
     * @return array 依頼
     */
    private function _getRequest($id)
    {
        return t_Requests::select("t_requests.*")
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
            ->where("request_id", $id)->get()->first();
    }

    /**
     *　ユーザデータの取得
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
            ->selectRaw("u.ccmail_address")
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
     *　依頼申込／依頼詳細画面
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    public function detail_store(Request $rq)
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
                return redirect()->route("r.detail", ["id" => 0])->withErrors([GetMessage::getMessageByID("error008")]);
            }
        }
        //確認ボタンクリック時はクリック行の状謡をチェック
        if ($rq->btnOk == "btnConfirme") {
            $dataRD = DB::table($this->_t_request_details)
                ->select("RD.status_id")
                ->selectRaw("DATE_FORMAT(RD.recovered_on, '%Y/%m/%d') AS recovered_on")
                ->where("RD.request_detail_id", $rq->request_detail_id)
                ->get()->first();
            //取得した依頼内容データの状態、回収日が変更されていた場合エラー
            if (!$dataRD || $dataRD->status_id != config("const.RDStatus.CUSTOMER")
                         || $dataRD->recovered_on != $rq->store_recovered_on){
                return redirect()->back()->withErrors([GetMessage::getMessageByID("error028")]);
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
                    $dataR = $this->_getR($rq, true);
                    $request_id = t_Requests::insertGetId($dataR);

                    //  新規に依頼内容データを作成
                    RequestsDetail::insert($this->_getRD($rq, $request_id));
                } else {
                    $request_id = $rq->request_id;
                    // ・「依頼ID」が0以外（一時保存）の場合、以下を実施
                    $dataR = $this->_getR($rq);
                    t_Requests::where("request_id", $request_id)->update($dataR);

                    //－依頼IDが合致する依頼内容データを削除して再作成
                    RequestsDetail::where("request_id", $request_id)->delete();
                    //  新規に依頼内容データを作成
                    RequestsDetail::insert($this->_getRD($rq, $request_id));
                }
                //依頼内容データの原料区分コードを更新
                RequestsDetail::query()
                    ->join($this->_t_requests, "R.request_id", "t_request_details.request_id")
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
                    $dataR = $this->_getR($rq, true, true);
                    $request_id = t_Requests::insertGetId($dataR);

                    //  新規に依頼内容データを作成
                    RequestsDetail::insert($this->_getRD($rq, $request_id, true));
                } else {
                    $request_id = $rq->request_id;
                    // ・「依頼ID」が0以外（一時保存）の場合、以下を実施
                    $dataR = $this->_getR($rq, false, true);
                    t_Requests::where("request_id", $request_id)->update($dataR);

                    //－依頼IDが合致する依頼内容データを削除して再作成
                    RequestsDetail::where("request_id", $request_id)->delete();

                    //  新規に依頼内容データを作成
                    RequestsDetail::insert($this->_getRD($rq, $request_id, true));
                }
                //依頼内容データの原料区分コードを更新
                RequestsDetail::query()
                    ->join($this->_t_requests, "R.request_id", "t_request_details.request_id")
                    ->join("m_material_divisions AS mDiv", function ($join) {
                        $join->on("R.CUSTCD", "mDiv.CUSTCD");
                        $join->on("t_request_details.BRNDCD", "mDiv.BRNDCD");
                    })
                    ->where("t_request_details.request_id", $request_id)
                    ->update(['t_request_details.material_division' => DB::raw("`mDiv`.`material_division`")]);

                // ・画面のメールアドレスに対して、メールを送信
                $this->_sendMail($request_id);
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

            //【確認】ボタン
            case 'btnConfirme':
                $btnName = "確認";
                $request_id = $rq->request_id;
                //依頼内容データの状態を更新
                $getRD = array(
                    "status_id"      => config("const.RDStatus.CONFIRM"),               //状態：確定をセット
                    "confirmed_at"   => Carbon\Carbon::now()->format("Y/m/d H:i:s"),    //顧客確認日時：現在日時をセット
                    "confirmed_user" => Auth()->user()->user_id                         //顧客確認者：ログインユーザIDをセット
                );
                $getRD = array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $getRD);

                RequestsDetail::query()
                    ->where("request_detail_id", $rq->request_detail_id)
                    ->update($getRD);
                break;
            }
            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            $Errmsg = GetMessage::getMessageByID("error009");
            return redirect()->back()->withErrors($Errmsg);
        }
        if ($rq->btnOk == "btnTemp" || $rq->btnOk == "btnConfirme") {
            return redirect()->route("r.detail", ["id" => $request_id])->withSuccess(str_replace("{p}", $btnName, GetMessage::getMessageByID("error007")));
        } else {
            return redirect()->route("r.history")->withSuccess(str_replace("{p}", $btnName, GetMessage::getMessageByID("error007")));
        }
    }

    /**
     *　依頼申込ボタンをクリック時、メールを送信
     * @param  $id 依頼ID
     * @return メール送信
     */
    private function _sendMail($id)
    {
        // 依頼テーブルデータ
        $data = t_Requests::select("t_requests.*")
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
            $dataRD = DB::table($this->_t_request_details)
                ->select("RD.information", "fmst.BRNDNM")
                ->selectRaw("DATE_FORMAT(RD.hoped_on, '%Y年%m月%d日') AS hoped_on")
                ->leftJoin($this->fmst, "fmst.BRNDCD", "RD.BRNDCD")
                ->where("RD.request_id", $id)->get();

            // 会社の情報
            $company = array(
                "COMPANY_PHONE" => config('const.CompanyInfo.phone'),
                "COMPANY_MAIL" =>  config('const.CompanyInfo.mail'),
                "COMPANY_MANAGER" => config('const.CompanyInfo.manager'),
            );

            //複数のメールアドレス対応
            $mailtolist = "";
            if (!empty($data->mail_address)) $mailtolist = explode(",", $data->mail_address);
            $mailcclist = "";
            if ($data->contact_type && !empty($data->ccmail_address)) $mailcclist = explode(",", $data->ccmail_address);
            if ($mailcclist == ""){
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
            $dataMail = ['data' => $data, "dataRD" => $dataRD, "company" => $company, "mailsetting" => $mailsetting];

            // メール送信
            Mail::send(new \App\Mail\AppMail($dataMail));
        }
    }

    /**
     *　依頼申込／依頼詳細画面
     *   依頼データを作成
     * @param  Request $rq
     * @param   bool $insert 新規追加フラグ
     * @param   bool $app 【依頼申込】ボタンフラグ
     * @return \Illuminate\View\View
     */
    private function _getR(Request $rq, bool $insert = false, bool $app = false)
    {
        $data =  array(
            "CUSTCD"       => Auth::user()->CUSTCD, //取引先コード：ログインユーザIDで取得したユーザマスタの「取引先コード」の値をセット
            "request_type" => $rq->request_type,    //依頼種別：画面の「引取／持込」の選択値のIDをセット
            "request_note" => $rq->request_note,    //依頼備考：画面の「依頼備考」の値をセット
            "applied_user" => Auth::user()->user_id //申込者  ：ログインユーザをセット
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
            $data["applied_at"]   = Carbon\Carbon::now()->format("Y/m/d H:i:s");//申込日時：現在日時をセット
        }
        return $data;
    }

    /**
     *　依頼申込／依頼詳細画面
     *   依頼内容データを作成
     * @param  Request $rq
     * @param   int $request_id 依頼ID
     * @param   bool $app 【依頼申込】ボタンフラグ
     * @return \Illuminate\View\View
     */
    private function _getRD(Request $rq, int $request_id, bool $app = false)
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
     *　登録情報画面
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    public function client(Request $rq)
    {
        // 保存ボタン
        if ($rq->btnOk) {
            $data = array(
                "password"       => $rq->password,
                "family_name"    => $rq->family_name,
                "first_name"     => $rq->first_name,
                "tel_no"         => $rq->tel_no,
                "mail_address"   => $rq->mail_address,
                "ccmail_address" => $rq->ccmail_address,
                "contact_type"   => $rq->contact_type,
            );
            User::where("user_id", Auth::user()->user_id)
                ->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data));
            return redirect()->route("r.client")->with('success', '更新が完了しました。');
        }
        $data = $this->_getUser();
        return view("request.collect.client", compact("data"));
    }
}
