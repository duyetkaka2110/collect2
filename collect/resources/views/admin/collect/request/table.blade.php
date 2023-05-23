<table class="table w-100 mt-1 mb-1 tb-detail">
    <thead>
        <tr>
            <th class="w-4em text-nowrap">状態</th>
            <th class="w-7em text-nowrap">銘柄</th>
            <th class="w-8em text-nowrap">希望日</span></th>
            <th class="w-8em text-nowrap">回収日</th>
            <th class="w-7em text-nowrap">物流会社</th>
            <th class="w-5em text-nowrap">車両種別</th>
            <th class="w-13em text-nowrap">車両No：運転手</th>
            <th class="w-8em text-nowrap">連絡事項</th>
            <th class="w-7em text-nowrap">受付</th>
            <th class="w-7em text-nowrap">配車</th>
            <th class="w-7em text-nowrap">顧客確認</th>
            <th class="w-7em text-nowrap">受入</th>
            <th class="w-3em text-nowrap">取差戻</th>
            <th class="w-3em text-nowrap">行複写</th>
            <th class="w-3em text-nowrap">削除</th>
        </tr>
    </thead>
    <tbody style="overflow-y: scroll; overflow-x: hidden; height: 100px;">
        <tr class="d-none tr-default">
            <td class="text-center">
                <input name="status[]" type="hidden" class="form-control input-1 status" value="{{ config('const.RDStatus.RECEPT') }}">
                <p class="btn-color btn mb-0" style="color: #ffffff; background-color: #d9534f;">受付</p>
            </td>
            <td>
                <select name="BRNDCD[]" class="form-control input-1 BRNDCD">
                    <option value=""></option>
                    @foreach($listproducts as $p)
                    <option value="{{ $p->BRNDCD }}">{{ $p->BRNDNM  }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="request_detail_id[]" class="request_detail_id" value="">
            </td>
            <td class="text-center position-relative"><input name="hoped_on[]" type="text" autocomplete="off" value="" class="hoped_on text-center datenotsun form-control w-7em pl-1 pr-1"> </td>

            <td class="text-center position-relative">
                <input name="recovered_on[]" value="" class="recovered_on text-center datenotsun form-control w-7em pl-1 pr-1" type="text" autocomplete="off">
            </td>
            <td>
                <select name="PDCPCD[]" class="form-control input-1 PDCPCD">
                    <option value=""></option>
                </select>
            </td>
            <td>
                @if($data->request_type == 0)
                <select name="car_type[]" class="form-control  car_type">
                    <option value=""></option>
                </select>
                @else
                <input name="" class="form-control " readonly value="">
                <input name="car_type[]" type="hidden" value="">
                @endif
            </td>
            <td>
                @if($data->request_type == 0)
                <select name="car_detail[]" class="form-control car_detail">
                    <option value=""></option>
                </select>
                @else
                <input name="car_detail[]" class="car_detail" type="hidden" value="">
                @endif
            </td>
            <td><textarea class="form-control information" name="information[]" maxlength="1024"></textarea></td>
            <td class="text-center tdRECEPT"><button type="button" class="btn btn-primary text-nowrap btnRECEPT">受付</button></td>
            <td class="text-center"></td>
            <td class="text-center"></td>
            <td class="text-center"></td>
            <td class="text-center">
            </td>
            <td class="text-center">
                <i class="fa fa-copy cursor-pointer btnCopy" title="行複写" aria-hidden="true"></i>
            </td>
            <td class="text-center">
                <i class="fa fa-trash cursor-pointer btnDelete" title="削除" aria-hidden="true"></i>
            </td>
        </tr>
        @if(!$dataRD)
        <tr>
            <td class="text-center">
                <input name="status[]" type="hidden" class="form-control input-1 status" value="">
                －
            </td>
            <td>
                <select name="BRNDCD[]" class="form-control input-1 BRNDCD">
                    <option value=""></option>
                    @foreach($listproducts as $p)
                    <option value="{{ $p->BRNDCD }}">{{ $p->BRNDNM  }}</option>
                    @endforeach
                </select>
                <input type="hidden" name="request_detail_id[]" class="request_detail_id" value="">
            </td>
            <td class="text-center position-relative"><input name="hoped_on[]" type="text" autocomplete="off" value="" class="hoped_on text-center datenotsun form-control w-7em pl-1 pr-1"> </td>

            <td class="text-center position-relative">
                <input name="recovered_on[]" value="" class="recovered_on text-center datenotsun form-control w-7em pl-1 pr-1" type="text" autocomplete="off">
            </td>
            <td>
                <select name="PDCPCD[]" class="form-control input-1 PDCPCD">
                    <option value=""></option>
                </select>
            </td>
            <td>
                <select name="car_type[]" class="form-control  car_type">
                    <option value=""></option>
                </select>
            </td>
            <td>
                <select name="car_detail[]" class="form-control car_detail">
                    <option value=""></option>
                </select>
            </td>
            <td><textarea class="form-control information" name="information[]" maxlength="1024"></textarea></td>
            <td class="text-center tdRECEPT"></td>
            <td class="text-center"></td>
            <td class="text-center"></td>
            <td class="text-center"></td>
            <td class="text-center"></td>
            <td class="text-center">
                @if(array_key_exists(1, $authority_type ))
                <i class="fa fa-copy cursor-pointer btnCopy" title="行複写" aria-hidden="true"></i>
                @endif
            </td>
            <td class="text-center">
                @if(array_key_exists(1, $authority_type ))
                <i class="fa fa-trash cursor-pointer btnDelete" title="削除" aria-hidden="true"></i>
                @endif
            </td>
        </tr>
        @endif
        @foreach($dataRD as $key => $rd)
        <tr class='{{ ($rd->status_id == config("const.RDStatus.CONFIRM") || $rd->status_id == config("const.RDStatus.COMP")) ? "tr-done" : ""  }}'>
            <td class="text-center">
                <input name="status[]" type="hidden" class="form-control input-1 status" value="{{ $rd->status_id }}">
                <p class="btn-color btn mb-0" style="color: {{ $rd->font_color}}; background-color: {{ $rd->background_color}};">{{ $rd->status_name }}</p>
            </td>
            <td>
                <!-- ログインユーザに“受付担当”の権限（管理権限マスタから取得）があり、状態が“受付”または空欄の場合のみ表示 -->
                @if(array_key_exists(1, $authority_type ) && ($rd->status_id == config("const.RDStatus.RECEPT") || !$rd->status_id))
                <select name="BRNDCD[]" class="form-control input-1 BRNDCD">
                    <option value=""></option>
                    @foreach($listproducts as $p)
                    <option value="{{ $p->BRNDCD }}" {{ $rd->BRNDCD == $p->BRNDCD ? "selected": "" }}>{{ $p->BRNDNM  }}</option>
                    @endforeach
                </select>
                @else
                @if(!$rd->BRNDCD)
                <input name="" class="form-control input-1 " value="" readonly>
                @endif
                <input name="BRNDCD[]" type="hidden" class="BRNDCD" value="{{$rd->BRNDCD}}">
                <input name="" class="form-control input-1 " value="{{$rd->BRNDNM}}" readonly>
                @endif
                <input type="hidden" name="request_detail_id[]" class="request_detail_id" value="{{ @$rd->request_detail_id }}">
            </td>
            <td class="text-center position-relative">
                <input name="hoped_on[]" type="text" autocomplete="off" value="{{ $rd->hoped_on }}" {{ (array_key_exists(1, $authority_type ) && ($rd->status_id == config("const.RDStatus.RECEPT") || !$rd->status_id)) ? "":"readonly"}} class="hoped_on text-center {{ (array_key_exists(1, $authority_type ) && ($rd->status_id == config("const.RDStatus.RECEPT") || !$rd->status_id)) ? "datenotsun":""}} form-control w-7em pl-1 pr-1">
            </td>

            <td class="text-center position-relative">
                <!-- 　 ログインユーザに“受付担当”の権限（管理権限マスタから取得）があり、状態が“受付”“依頼”“配車”または空欄の場合のみ変更可
　              または“配車担当”の権限があり、状態が“配車”の場合に変更可 -->
                @if((array_key_exists(1, $authority_type ) && ($rd->status_id == config("const.RDStatus.RECEPT") ||$rd->status_id == config("const.RDStatus.REQUEST") ||$rd->status_id == config("const.RDStatus.DISP") || !$rd->status_id))
                || (array_key_exists(2, $authority_type ) && $rd->status_id == config("const.RDStatus.DISP")))
                <input name="recovered_on[]" value="{{ $rd->recovered_on  }}" class="recovered_on text-center datenotsun form-control w-7em pl-1 pr-1" type="text" autocomplete="off">
                @else
                <input name="recovered_on[]" value="{{ $rd->recovered_on  }}" readonly class="recovered_on text-center  form-control w-7em pl-1 pr-1" type="text" autocomplete="off">
                @endif
            </td>
            <td>
                <!-- 　 ログインユーザに“受付担当”の権限（管理権限マスタから取得）があり、状態が“完了”以外または空欄の場合のみ表示 -->
                @if(array_key_exists(1, $authority_type ) && ($rd->status_id != config("const.RDStatus.COMP")))
                <select name="PDCPCD[]" class="form-control input-1 PDCPCD">
                    <option value=""></option>
                    @foreach($rd->list_smst as $p)
                    <option value="{{ $p->CUSTCD }}" {{ $rd->PDCPCD == $p->CUSTCD ? "selected": "" }}>{{ $p->CUSTNM  }}</option>
                    @endforeach
                </select>
                @else
                <input name="" class="form-control input-1 PDCPCD" readonly value="{{ $rd->PDCPNM  }}">
                <input name="PDCPCD[]" type="hidden" value="{{ $rd->PDCPCD }}">
                @endif
            </td>
            <td>
                <!-- 　 ログインユーザに“受付担当”の権限（管理権限マスタから取得）があり、状態が“完了”以外または空欄の場合							
　                      または“配車担当”の権限があり、状態が“配車”の場合に表示							 -->
                @if(((array_key_exists(1, $authority_type ) && ($rd->status_id != config("const.RDStatus.COMP"))) || (array_key_exists(2, $authority_type ) && ($rd->status_id == config("const.RDStatus.DISP"))) )&& ($data->request_type == 0))
                <select name="car_type[]" class="form-control  car_type">
                    <option value=""></option>
                    @foreach($rd->list_car_type as $p)
                    <option value="{{ $p->enum_key }}" {{ $rd->car_type == $p->enum_key ? "selected": "" }}>{{ $p->enum_value  }}</option>
                    @endforeach
                </select>
                @else
                <input name="" class="form-control " readonly value="{{ $rd->car_typeNM  }}">
                <input name="car_type[]" type="hidden" value="{{ $rd->car_type }}">
                @endif
            </td>
            <td>
                <!-- 　 ログインユーザに“受付担当”の権限（管理権限マスタから取得）があり、状態が“完了”以外または空欄の場合							
　                      または“配車担当”の権限があり、状態が“配車”の場合に表示							 -->
                @if(((array_key_exists(1, $authority_type ) && ($rd->status_id != config("const.RDStatus.COMP"))) || (array_key_exists(2, $authority_type ) && ($rd->status_id == config("const.RDStatus.DISP")))) && ($data->request_type == 0))
                <select name="car_detail[]" class="form-control car_detail">
                    <option value=""></option>
                    @foreach($rd->list_car as $p)
                    <option value="{{ $p->id }}" {{ $rd->car_detail_id == $p->id ? "selected": "" }}>{!! $p->title !!}</option>
                    @endforeach
                </select>
                @else
                <div class="text-center">{!! $rd->title2 != "0：" ? $rd->title2 : "" !!}</div>
                <input name="car_detail[]" class="car_detail" type="hidden" value="{{  $rd->title2 != '0：' ? $rd->car_detail_id : '' }}">
                @endif
            </td>
            <td>
                <?php
                // 受付担当で状態が“受付”の場合に変更可能
                if (array_key_exists(1, $authority_type) && ($rd->status_id == config("const.RDStatus.RECEPT"))) {
                    $readonly = "";
                } else {
                    $readonly = "readonly";
                }
                ?>
                <textarea class="form-control information" {{ $readonly }} name="information[]" maxlength="1024">{{ $rd->information  }}</textarea>
            </td>
            <td class="text-center tdRECEPT">
                @if($rd->status_id == config("const.RDStatus.RECEPT") && array_key_exists(1, $authority_type ))
                <button type="button" class="btn btn-primary text-nowrap btnRECEPT btnlock">受付</button>
                @else
                {!! $rd->receptionNM !!}
                @endif
            </td>
            <td class="text-center">
                @if((array_key_exists(1, $authority_type )&& $rd->status_id == config("const.RDStatus.REQUEST")) || (array_key_exists(2, $authority_type )&& $rd->status_id == config("const.RDStatus.DISP")))
                <button type="button" class="btn btn-primary text-nowrap btnDISP btnlock">配車</button>
                @else
                {!! $rd->dispatchedNM !!}
                @endif
            </td>
            <td class="text-center">
                @if(array_key_exists(1, $authority_type )&& $rd->status_id == config("const.RDStatus.CUSTOMER"))
                <button type="button" class="btn btn-primary text-nowrap btnCONFIRM btnlock">確認</button>
                @else
                {!! $rd->confirmedNM !!}
                @endif
            </td>
            <td class="text-center">
                @if(array_key_exists(1, $authority_type )&& $rd->status_id == config("const.RDStatus.CONFIRM"))
                <button type="button" class="btn btn-primary  text-nowrap btnACCEPT btnlock">受入</button>
                @else
                {!! $rd->acceptancedNM !!}
                @endif
            </td>
            <td class="text-center">
                @if(((array_key_exists(1, $authority_type ) && ($rd->status_id == config("const.RDStatus.CUSTOMER") || $rd->status_id == config("const.RDStatus.CONFIRM")))) ||
                (array_key_exists(2, $authority_type ) && $rd->status_id == config("const.RDStatus.DISP")))
                <i class="fa fa-history cursor-pointer btnREVERT btnlock" aria-hidden="true" title="取差戻"></i>
                @endif
            </td>
            <td class="text-center">
                @if(array_key_exists(1, $authority_type ))
                <i class="fa fa-copy cursor-pointer btnCopy" title="行複写" aria-hidden="true"></i>
                @endif
            </td>
            <td class="text-center">
                @if(array_key_exists(1, $authority_type ) && ($rd->status_id == config("const.RDStatus.RECEPT") || $rd->status_id == ""))
                <i class="fa fa-trash cursor-pointer btnDelete" title="削除" aria-hidden="true" data-rdid="{{ $rd->request_detail_id  }}"></i>
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>