<html>

<head>
    <title>依頼履歴</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
</head>

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
        font-size: 14px;
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

    .text-center {
        text-align: center;
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

    .w-20em {
        width: 20em !important;
    }

    .w-100 {
        width: 98%;
    }

    .information {
        white-space: normal;
        text-overflow: ellipsis;
        word-wrap: break-word;
    }
</style>

<body>
    <table class="table  w-100 ">
        <thead>
            <tr>
                <th class="w-3em">依頼No.</th>
                <th class="w-5em">申込日</th>
                <th class="w-2em">状態</th>
                <th class="w-5em">作成日</th>
                <th class="w-3em">引取／持込</th>
                <th class="w-7em">銘柄</th>
                <th class="w-5em">希望日</th>
                <th class="w-5em">回収日</th>
                <th class="w-20em">連絡事項</th>
            </tr>
        </thead>
        <tbody>
            @foreach($list as $l)
            <tr>
                <td class="text-center">{{ $l->request_no }}</td>
                <td class="text-center">{{ $l->applied_at }}</td>
                <td class="text-center">{{ $l->status_name }}</td>
                <td class="text-center">{{ $l->created_at }}</td>
                <td class="text-center">{{ $l->RTypeNM }}</td>
                <td>{{ $l->BRNDNM }}</td>
                <td class="text-center">{{ $l->hoped_on }}</td>
                <td class="text-center">{{ $l->recovered_on }}</td>
                <td><span class="information ">{{ $l->information }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>