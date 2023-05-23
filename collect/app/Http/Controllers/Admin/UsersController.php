<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon;
use App\GetMessage;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Models\UserAsU;
use App\Models\LoginLog;
use App\Models\m_enums;
use Auth;
use Session;
use DB;
use App\Models\m_user_authorities;
use App\Http\Controllers\InsertUpdateController;

class UsersController extends Controller
{

    public $_timetoday;
    protected $_mysystemname = "admin";
    protected $mmst = "pldtcustmmst as mmst"; //取引先マスタ
    protected $enums = "m_enums as E"; //汎用マスタ
    protected $enums2 = "m_enums as E2"; //汎用マスタ
    protected $enums3 = "m_enums as E3"; //汎用マスタ
    protected $tmst = "pldtcusttmst as tmst"; //取引先担当者マスタ
    protected $m_user_authorities = "m_user_authorities as ua"; //管理権限マスタ
    public $_loginlogtb = "t_login_logs";
    public function __construct()
    {
        $this->_timetoday = Carbon\Carbon::now()->format("Y/m/d H:i:s");
    }
    // ログイン
    public function login(Request $request)
    {
        //ログインした時ホームページに展開する
        if (Auth::check()) {
            return redirect()->route("a.dcalender");
        }
        //「ログイン」ボタンをクリックする時
        if ($request->has('BtnLogin')) {

            //エラーフォーマット
            $rulus = [
                'UserID' => 'required',
                'PassWord' => 'required',
            ];

            //エラーメッセージの取得
            $message = [
                'UserID.required' => "ユーザーIDを入力してください。",
                'PassWord.required' => "パスワードを入力してください。",
            ];

            // return $message;
            if ($request->has('BtnLogin')) {
                $validator = Validator::make($request->all(), $rulus, $message);


                //エラー検知
                if ($validator->fails()) {
                    return redirect()->back()->withInput()->withErrors($validator);
                } else {
                    $UserID = $request->UserID;
                    $flag = true;
                    //ユーザー確認
                    // －アカウントロックエラー：以下条件の全てに合致したデータがログインログテーブルに３件以上存在する	
                    // ・作成機能：“admin”
                    // ・ユーザID：入力したユーザIDと合致
                    // ・ログインステータス：“0”(ログイン失敗)
                    // ・カウント対象外：“0”(カウント対象)
                    $countLog = LoginLog::where("created_system", $this->_mysystemname)
                        ->where("user_id", $UserID)
                        ->where("is_status", 0)
                        ->where("is_nocount", 0)->count();
                    if ($countLog > 2) {
                        $flag = false;
                        $ErrorCode = "ACCOUNT LOCK";
                    }

                    // －ユーザIDエラー：入力したユーザIDと合致するユーザIDがユーザマスタに存在しない	
                    // ＜検索条件＞
                    // ・ユーザマスタ.ユーザID：入力したユーザIDと合致
                    // ・ユーザマスタ.ユーザ種別：0
                    if ($flag) {
                        $count = UserAsU::where("u.user_id", $UserID)
                            ->where("u.user_type", 0)
                            ->count();
                        if (!$count) {
                            $ErrorCode = "UserID ERR";
                            $flag = false;
                        }
                    }
                    // 　－パスワードエラー：入力したユーザIDのユーザマスタデータのパスワードと入力したパスワードの値が合致しない
                    if ($flag) {
                        $password = User::select("password")
                            ->where("user_id", $UserID)
                            ->value("password");
                        if ($password != $request->PassWord) {
                            $ErrorCode = "Password ERR";
                            $flag = false;
                        }
                    }

                    // 　－削除済みエラー：入力したユーザIDのユーザマスタデータが削除済み（is_deletedが0以外）
                    if ($flag) {
                        $count = User::where("user_id", $UserID)
                            ->where("is_deleted", 0)
                            ->count();
                        if (!$count) {
                            $ErrorCode = "UserID DELETE";
                            $flag = false;
                        }
                    }

                    if ($flag) {
                        $user = User::select("*")
                            ->where("user_id", $UserID)
                            ->where("password", $request->PassWord)
                            ->where("is_deleted", 0)
                            ->get()->first();
                        //  ・ログインユーザチェックでエラーが存在しなかった場合、以下を実施	
                        // 　－ログイン認証をセット	

                        Auth::login($user);

                        // 　－ログインログテーブルの以下条件に合致したデータの「カウント対象外」に“1”（カウント対象外）をセット	
                        // 	・作成機能：“admin”
                        // 	・ユーザID：入力したユーザIDと合致
                        // 	・カウント対象外：“0”（カウント対象）
                        DB::table($this->_loginlogtb)->where("created_system", $this->_mysystemname)
                            ->where("user_id", $UserID)
                            ->where("is_nocount", 0)
                            ->update(["is_nocount" => 1]);

                        // 　－ログインログテーブルに以下データを追加	
                        // 	・ユーザID：入力したユーザIDをセット
                        // 	・ログインステータス：“1”（ログイン成功）をセット
                        // 	・エラー種別：空欄
                        // 	・カウント対象外：“1”（カウント対象外）をセット
                        // 	・作成機能：“admin”をセット
                        // 	・作成日時：現在日時をセット
                        $data = array(
                            "user_id" => $UserID,
                            "is_status" => 1,
                            "err_type" => "",
                            "is_nocount" => 1,
                            "created_system" => $this->_mysystemname,
                            "created_at" => $this->_timetoday
                        );
                        LoginLog::insert($data);


                        //一覧画面に展開する
                        return redirect()->route("a.rsearch");
                    } else {
                        // ・ログインユーザチェックでエラーが存在した場合、以下を実施		
                        // 　－ログインログテーブルに以下データを追加		
                        // 	・ユーザID：入力したユーザIDをセット	
                        // 	・ログインステータス：“0”（ログイン失敗）をセット	
                        // 	・エラー種別：以下のいずれかをセット	
                        // 	　アカウントロックエラーの場合：ACCOUNT LOCK／ユーザIDエラーの場合：UserID ERR／パスワードエラーの場合：Password ERR／削除済みエラーの場合：UserID DELETE	
                        // 	・カウント対象外：“0”（カウント対象）をセット	
                        // 	・作成機能：“admin”をセット	
                        // 	・作成日時：現在日時をセット	

                        $data = array(
                            "user_id" => $UserID,
                            "is_status" => 0,
                            "err_type" => $ErrorCode,
                            "is_nocount" => 0,
                            "created_system" => $this->_mysystemname,
                            "created_at" => $this->_timetoday
                        );
                        LoginLog::insert($data);


                        //   －以下エラーメッセージを表示して処理を中止する	
                        $ErrMsg = GetMessage::getMessageByID("error003");
                        return redirect()->back()->withInput()->withErrors(["ErrMsg" =>  $ErrMsg]);
                    }
                }
            }
        }
        //お知らせ
        $datasend = array();
        return view("admin.users.login")->with($datasend);
    }


    //ログアウト
    public function logout()
    {
        Auth::logout();
        Session::flush();

        //ログイン画面に展開する
        return redirect()->route("a.home");
    }


    /**
     *　 ログインユーザーの権限種別を取得
     * @param  $user_id ユーザID 
     * @return 権限種別
     */
    public static function authority_type($user_id = null)
    {
        $type = [];
        if (!$user_id){
            if(Auth::check()){
                 $user_id = Auth::user()->user_id;
            }
        }
           
        if ($user_id) {
            $data =  m_user_authorities::select("authority_type")
                ->where("user_id", $user_id)
                ->get()->toArray();
            foreach ($data as $d) {
                $type[$d["authority_type"]] = 1;
            }
        }
        return $type;
    }



    /**
     *　ユーザー一覧
     * @return view
     */
    public  function list(Request $rq)
    {
        $titleheader = "アカウント管理";
        $listUtype = m_enums::select("enum_key", "enum_value")
            ->where("enum_id", "user_type")
            ->get();

        $btnSearch = $rq->btnSearch;
        $list = UserAsU::select("mmst.CUSTNM", "u.user_id", "u.first_name", "u.family_name", "u.mail_address", "u.ccmail_address", "u.tel_no", "u.is_deleted")
            ->selectRaw("E.enum_value as TypeNM")
            ->selectRaw("E3.enum_value as contact_typeNM")
            ->selectRaw(DB::raw('GROUP_CONCAT(E2.enum_value,"<br>")  as authority_typeNM'))
            ->leftJoin($this->mmst, "mmst.CUSTCD", "u.CUSTCD")
            ->leftJoin($this->enums, function ($join) {
                $join->on("E.enum_key", "u.user_type");
                $join->where("E.enum_id", "user_type");
            })
            ->leftJoin($this->m_user_authorities, "ua.user_id", "u.user_id")
            ->leftJoin($this->enums2, function ($join) {
                $join->on("E2.enum_key", "ua.authority_type");
                $join->where("E2.enum_id", "authority_type");
            })
            ->leftJoin($this->enums3, function ($join) {
                $join->on("E3.enum_key", "u.contact_type");
                $join->where("E3.enum_id", "contact_type");
            });

        //検索条件を追加
        $list = $this->_getSearch($list, $rq);

        $list = $list->groupBy("mmst.CUSTNM", "u.user_id", "u.first_name", "u.family_name", "u.mail_address", "u.ccmail_address", "u.tel_no", "u.is_deleted", "E.enum_value", "E3.enum_value")
            ->orderBy("u.user_id")->orderBy("u.user_id")->paginate(20);

        session()->flashInput($rq->input());
        return view("admin.users.list", compact(
            "list",
            "titleheader",
            "listUtype",
            "btnSearch"
        ));
    }

    /**
     * 検索条件を追加
     * @param  $list 表示一覧 
     * @param  $rq input 
     * @return  表示一覧
     */
    private function _getSearch($list, Request $rq)
    {
        // 削除
        if ($rq->filled("DelFlag")) {
        } else {
            $list = $list->where("u.is_deleted",  0);
        }
        // 種別
        if ($rq->filled("request_type")) {
            $list = $list->whereIn("u.user_type",  $rq->request_type);
        }
        // ユーザID
        if ($rq->filled("user_id")) {
            $list = InsertUpdateController::getWhereLike($list, $rq->user_id, "u.user_id");
        }
        // 取引先
        if ($rq->filled("CUSTNM")) {
            $list = InsertUpdateController::getWhereLike($list, $rq->CUSTNM, "mmst.CUSTNM");
        }
        // 氏名
        if ($rq->filled("name")) {
            $list = InsertUpdateController::getWhereLikeConcat($list, $rq->name);
        }
        return $list;
    }
    /**
     *　アカウント編集画面
     * @param  Request $rq
     * @return \Illuminate\View\View
     */
    public function edit(Request $rq)
    {
        $user_id = $rq->id;
        // タイトル表示
        if ($user_id) {
            $title = "アカウント編集";
            $new_flag = false;
        } else {
            $title = "アカウント編集（新規作成）";
            $new_flag = true;
        }
        // 保存ボタン
        if ($rq->btnOk) {
            // 入力画面データを取得
            $data = $this->_getData($rq, $new_flag);
            // ユーザマスタデータ
            if ($new_flag) {
                // 新規
                User::insert(array_merge(InsertUpdateController::datainsert($this->_mysystemname), $data));
            } else {
                // 更新
                User::where("user_id", $rq->user_id)
                    ->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data));
            }

            // 権限マスタを削除
            m_user_authorities::where("user_id", $rq->user_id)->delete();
            // －ユーザ種別が“引取管理用”で権限マスタを登録
            if ($rq->user_type == 0) {
                m_user_authorities::insert($this->_getDataAuthorType($rq));
            }

            return redirect()->route("a.uedit", ["id" => $rq->user_id])->with("success", str_replace("{p}", "保存", GetMessage::getMessageByID("error010")));
        }
        // 削除ボタン
        if ($rq->btnDel) {
            $data = ["is_deleted" => 1];
            User::where("user_id", $rq->user_id)
                ->update(array_merge(InsertUpdateController::dataupdate($this->_mysystemname), $data));
            return redirect()->route("a.ulist")->with('success', str_replace("{p}", "削除", GetMessage::getMessageByID("error010")));
        }
        $data = $this->_getUser($user_id);
        $listsup = $listContact = $authority_types = null;
        // 取引先一覧
        $listsup = $this->getSupplier();
        if ($data) {
            if ($data->is_deleted) {
                // ・パラメータで渡された「ユーザID」が空欄以外の場合、「ユーザID」が合致するユーザマスタのデータを取得して、画面に表示する
                $title = "アカウント編集（削除済）";
            }
            // お取引先担当者	一覧
            $listContact = $this->getContact($data->CUSTCD);
            // ユーザ権限ID
            $authority_types = self::authority_type($user_id);
        }
        // ユーザ種別一覧
        $list_user_type = m_enums::select("enum_key", "enum_value")->where("enum_id", "user_type")->orderBy("enum_key")->get();
        // ユーザ権限
        $list_authority_type = m_enums::select("enum_key", "enum_value")->where("enum_id", "authority_type")->orderBy("enum_key")->get();
        return view("admin.users.edit", compact("data", "user_id", "title", "list_user_type", "list_authority_type", "authority_types", "listsup", "listContact"));
    }


    /**
     *　ユーザ権限を取得
     * @param  $rq　すべてデータ画面
     * @return　ユーザ
     */
    private function _getDataAuthorType(Request $rq)
    {
        $data = [];
        if ($rq->filled("authority_type")) {
            foreach ($rq->authority_type as $t) {
                $temp = [
                    "user_id" => $rq->user_id,
                    "authority_type" => $t
                ];
                $data[] = array_merge(InsertUpdateController::datainsert($this->_mysystemname), $temp);
            }
        }
        return $data;
    }

    /**
     *　入力画面データを取得
     * @param  $rq　すべてデータ画面
     * @param  $new_flag　新規フラグ
     * @return　ユーザ
     */
    private function _getData(Request $rq, bool $new_flag)
    {
        $data = array(
            "password"       => $rq->password,
            "family_name"    => $rq->family_name,
            "first_name"     => $rq->first_name,
            "tel_no"         => $rq->tel_no,
            "mail_address"   => $rq->mail_address,
            "ccmail_address" => $rq->ccmail_address,
            "contact_type"   => $rq->contact_type,
        );
        if ($rq->user_type) {
            // 引取依頼用
            $data["CUSTCD"] = $rq->CUSTCD;
            $data["TCSTNO"] = $rq->TCSTNO;
        }
        if ($new_flag) {
            // 新規
            if ($rq->user_id) {
                $data["user_id"] = $rq->user_id;
                $data["user_type"] = $rq->user_type;
            }
        }
        return $data;
    }
    /**
     *　お取引先一覧の取得
     * @return　一覧
     */
    public function getSupplier()
    {
        $now = Carbon\Carbon::now()->format("Ymd");
        $list = DB::table($this->mmst)
            ->select("CUSTCD",  "OUTPOS")
            ->selectRaw("CUSTNM")
            ->selectRaw("0 as sortno")
            ->where("SRTDAY", "<=", $now)
            ->where("ENDDAY", ">=", $now);
        $list = DB::table($this->mmst)
            ->select("CUSTCD",  "OUTPOS")
            ->selectRaw("CONCAT('（有効期間外）',CUSTNM) AS CUSTNM")
            ->selectRaw("1 as sortno")
            ->where("SRTDAY", ">", $now)
            ->orWhere("ENDDAY", "<", $now)
            ->union($list)
            ->orderBy("sortno")
            ->orderBy("OUTPOS")
            ->orderBy("CUSTCD")
            ->get();
        return $list;
    }

    /**
     *　お取引先担当者一覧の取得
     * @param  $CUSTCD 取引先コード
     * @return　一覧
     */
    public function getContact($CUSTCD)
    {
        $now = Carbon\Carbon::now()->format("Ymd");
        $list = DB::table($this->tmst)
            ->select("TCSTNO",  "OUTPOS")
            ->selectRaw("TCSIKJ")
            ->selectRaw("0 as sortno")
            ->where("CUSTCD", $CUSTCD) //・取引先担当者マスタ．取引先コード ＝ 「お取引先」の選択値
            ->where("SRTDAY", "<=", $now)
            ->where("ENDDAY", ">=", $now);
        $list = DB::table($this->tmst)
            ->select("TCSTNO",  "OUTPOS")
            ->selectRaw("CONCAT('（有効期間外）',TCSIKJ) AS TCSIKJ")
            ->selectRaw("1 as sortno")
            ->where("CUSTCD", $CUSTCD) //・取引先担当者マスタ．取引先コード ＝ 「お取引先」の選択値
            ->where("SRTDAY", ">", $now)
            ->orWhere("ENDDAY", "<", $now)
            ->union($list)
            ->orderBy("sortno", "DESC")
            ->orderBy("OUTPOS")
            ->orderBy("TCSTNO")
            ->get();
        return $list;
    }

    /**
     *　お取引先担当者一覧の取得
     * @return　html
     */
    public function getContactHtml(Request $rq)
    {
        $html = '<option value=""></option>';
        $list = $this->getContact($rq->CUSTCD);
        if ($list) {
            foreach ($list as $l) {
                $html .= '<option value="' . $l->TCSTNO . '" >' . $l->TCSIKJ . '</option>';
            }
        }
        return $html;
    }

    /**
     *　お取引先担当者情報の取得
     * @return　配列
     */
    public function getContactValue(Request $rq)
    {
        $data = DB::table($this->tmst)->select("TCSIKJ", "TCMEKJ", "MLADRS", "RTELNO")
            ->where("CUSTCD", $rq->CUSTCD)
            ->where("TCSTNO", $rq->TCSTNO)
            ->first();
        return $data;
    }
    /**
     *　ユーザデータの取得
     * @param  $id ユーザID
     * @return　ユーザ
     */
    private function _getUser($id)
    {
        if ($id) {
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
                ->where("u.user_id", $id)
                ->get()->first();
        } else {
            return false;
        }
    }

    /**
     *　新規作成の場合、入力した「ユーザID」に合致するデータがユーザマスタに存在した
     * @param  $rq　すべてデータ画面
     * @return　エラーメッセージ
     */
    public function checkUserID(Request $rq)
    {
        if ($rq->user_id) {
            $data = User::where("user_id", $rq->user_id)->count();
            if ($data) {
                return GetMessage::getMessageByID("error022");
            }
        }
    }
}
