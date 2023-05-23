<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>依頼検索一覧</title>
    <style>
        @font-face {
            font-family: ipag;
            font-style: normal;
            font-weight: normal;
            src:url('{{ storage_path("fonts/ipag.ttf")}}');
        }

        @font-face {
            font-family: ipag;
            font-style: bold;
            font-weight: bold;
            src:url('{{ storage_path("fonts/ipag.ttf")}}');
        }

        body {
            font-family: ipag;
        }
        .w-info {
    width: 400px;
}
.wrapper {
    overflow: auto;
    width: 100%;
    max-height: calc(100vh - 220px);
}

.wrapper table {
    margin: 0;
    border-spacing: 0;

}

.wrapper td {
    white-space: nowrap;
    background: #FFF;
    padding: 5px;
}

.wrapper th {
    white-space: nowrap;
    background: #f2f2f2;
    position: sticky;
    top: -1px;
    left: 0;
}

.wrapper tr:first-child th:first-child {
    z-index: 1;
}

@media screen and (max-width: 1075px) {

    .bottom-table {
        overflow-x: scroll;
    }

    .table-list {
        min-width: 880px;
    }
}

.mg-content {
    width: calc(100% - 11em);
}

nav ul li {
    padding: 10px;

}

nav ul li span {
    padding-left: 5px;

}

.table thead {
    background: #eaeaea;
    text-align: center;
}

.table {
    border-collapse: collapse;
}

.table th {

    border-bottom: none;
}

.table th,
.table td {
    padding: 5px;
    border: 1px solid #ccc;
}

.a-link {
    color: #007bff;
    text-decoration: underline;
}

.mg-checkbox {
    text-align: right;
    width: 60px;
    position: relative;
    margin-right: 10px;
}

.mg-checkbox input {
    top: 5px;
    position: absolute;
    left: 0;
}

.form-control {
    padding: 0;
    height: 20px;
    font-size: inherit;
    width: max-content;
    display: inline-block;
    border-color: #ccc;
    border-radius: 3px;
}

.mg-select-time {
    height: 19px;
    vertical-align: middle;
    position: relative;
}
.w-1em {
    width: 1em !important;
}

.w-2em {
    width: 2em !important;
}

.w-3em {
    width: 3em !important;
}

.w-4em {
    width: 4em !important;
}

.w-5em {
    width: 5em !important;
}

.w-6em {
    width: 6em !important;
}

.w-7em {
    width: 7em !important;
}

.w-8em {
    width: 8em !important;
}

.w-9em {
    width: 9em !important;
}

.w-10em {
    width: 10em !important;
}

.w-11em {
    width: 11em !important;
}

.w-12em {
    width: 12em !important;
}

.w-13em {
    width: 13em !important;
}

.w-14em {
    width: 14em !important;
}

.w-15em {
    width: 15em !important;
}

.w-16em {
    width: 16em !important;
}

.w-17em {
    width: 17em !important;
}

.clear-both {
    clear: both;
}

.mg-errors {
    color: red;
    margin-bottom: 10px;
}
.text-center{
    text-align: center;
}
.w-100{
   width: 98%;
}
.white-space-inherit{
    white-space: unset !important;
}
.mw-em{
    min-width: 7em;
    max-width: 8em;
}
    </style>
</head>

<body>
<div class="bottom-table mt-1 wrapper w-100">
        <table class="table table-history w-100 mt-0 table-list  table-hover table_sticky">
            <thead>
                <tr>
                    <th scope="col" class="w-5em text-nowrap">依頼No.</th>
                    <th scope="col" class="w-7em text-nowrap">申込日</th>
                    <th scope="col" class="w-7em text-nowrap">取引先／担当者／連絡先</th>
                    <th scope="col" class="w-4em text-nowrap">状態</th>
                    <th scope="col" class="w-4em text-nowrap">種別</th>
                    <th scope="col" class="w-5em text-nowrap">銘柄</th>
                    <th scope="col" class="w-6em text-nowrap">希望日</th>
                    <th scope="col" class="w-6em text-nowrap">回収日</th>
                    <th scope="col" class="w-6em text-nowrap">物流会社</th>
                    <th scope="col" class="w-5em text-nowrap">車両種別</th>
                    <th scope="col" class="w-4em text-nowrap">車両No</th>
                    <th scope="col" class="w-5em text-nowrap">運転手</th>
                    <th scope="col" class="mw-em text-nowrap">連絡事項</th>
                    <th scope="col" class="w-6em text-nowrap">受付</th>
                    <th scope="col" class="w-6em text-nowrap">配車</th>
                    <th scope="col" class="w-6em text-nowrap">顧客確認</th>
                    <th scope="col" class="w-6em text-nowrap">受入</th>
                </tr>
            </thead>
            <tbody>
                @foreach($list as $l)
                <tr>
                    <td class="text-center">{{ $l->request_no }}</td>
                    <td class="text-center">{{ $l->applied_at }}</td>
                    <td class="">{!! $l->CFU !!}</td>
                    <td class="text-center">
                        {{ $l->status_name }}
                    </td>
                    <td class="">{{ $l->RTypeNM }}</td>
                    <td class="text-">{{ $l->BRNDNM }}</td>
                    <td class="text-center">{{ $l->hoped_on }}</td>
                    <td class="text-center"><b>{{ $l->recovered_on }}</b></td>
                    <td class="white-space-inherit" style="width: 5em;">{{ $l->PDCPCDNM }}</td>
                    <td>{{ $l->car_typeNM }}</td>
                    <td><span style="{{ $l->PCARNO_bg ? 'color: red' : '' }}">{{ $l->PCARNO }}</span></td>
                    <td  class="text-center" style="{{ $l->PCARNO_bg ? 'color: red' : '' }}">{!! $l->DVSTEL !!}</td>
                    <td  class="white-space-inherit" style="width: 10em;" >{{ $l->information }}</td>
                    <td class="text-center">{!! $l->receptionNM !!}</td>
                    <td class="text-center">{!! $l->dispatchedNM !!}</td>
                    <td class="text-center">{!! $l->confirmedNM !!}</td>
                    <td class="text-center">{!! $l->acceptancedNM !!}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>