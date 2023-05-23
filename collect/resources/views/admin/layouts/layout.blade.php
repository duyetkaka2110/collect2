<!DOCTYPE html>
<html lang="ja">

<head>
    <title>@yield('title') </title>
    <meta charset="UTF-8">
    <link rel="icon" type="image/x-icon" href="{{ URL::asset('img/favicon.ico') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.18.1/moment-with-locales.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/locale/ja.js" type="text/javascript"></script>
    <script src="{{ URL::asset('js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ URL::asset('js/main.js') }}"></script>
    <script src="{{ URL::asset('adminpublic/js/menu.js') }}"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ URL::asset('css/main.css') }}">
    <link href="{{ URL::asset('css/bootstrap-datetimepicker.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('adminpublic/css/menu.css') }}">
    <link href="{{ URL::asset('css/app.css') }}" rel="stylesheet">
    @yield("css")
    @yield("js")
</head>

<body>
    <div class="mg-body">
        <header class="p-2 pt-3">
            <img width="200" class="float-left pl-2" src="{!! URL::asset('/img/logo.png') !!}" alt="" />
            <div class="float-right">
                <label class="mb-0">ユーザー名：</label><span>{{ @Auth::user()->family_name . "　" . @Auth::user()->first_name }}</span>
                <a href="{{ route('a.logout') }}" class="btn btn-primary pt-0 pb-0 ml-3">ログアウト</a>
            </div>
            <div class="clear-both"></div>
        </header>
        <main class="p-2 pt-3 position-relative">
            @if(Session::has('success') || @$success)
            <div class="alert alert-success" role="alert">
                {{Session::get('success')}}
                {{ @$success }}
            </div>
            <script>
                $(".alert-success").show().delay(3000).fadeOut(100);
            </script>
            @endif
            @include("admin.layouts.menu")
            @yield("content")
            <div class="clear-both"></div>
        </main>
    </div>
    @yield("content2")
    @include("admin.layouts.modal")
    @yield("modal")
</body>

</html>