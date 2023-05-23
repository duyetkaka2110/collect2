<!DOCTYPE html>
<html lang="ja">

<head>
    <title>引取管理システム</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=10; IE=9; IE=8; IE=7; IE=EDGE" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="{{ URL::asset('css/login.css') }}">
</head>

<body>
    <main>
        <div class="container">
            <div class="mg-login-form">
                <img class="title-fixed mb-3" src="{{ URL::asset('/img/logo.png') }}" alt="" />
                <h6 class="title-fixed">引取管理システム</h6>
                <form method="POST" action="{{ route('a.login') }}">
                    <input type="hidden" name="_token" value="{!! csrf_token() !!}">
                    <div class="login-margin">
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-user" aria-hidden="true"></i></span>
                            </div>
                            <input type="text" value="{{ old('UserID') }}" autocomplete="off" class="form-control" maxlength="32" name="UserID" placeholder="ユーザーID" aria-label="Username">
                        </div>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-key" aria-hidden="true"></i></span>
                            </div>
                            <input type="password" name="PassWord" value="" class="form-control" placeholder="パスワード" aria-label="PassWord">
                        </div>
                        <div class="input-group mb-3">
                            <button name="BtnLogin" class="btn btn-primary">ログイン</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
    @if(($errors->any()))
    <!-- Modal Error Message -->
    <div class="modal fade" id="MessageModal" role="dialog" data-backdrop="static" tabindex="-1">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header modal-header-error bg-danger">
                    <h5 class="modal-title text-white">エラーメッセージ</h5>
                </div>
                <div class="modal-body">
                    @foreach ($errors->all() as $error)
                    <p>{!! $error !!}</p>
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger close-modal" data-dismiss="modal">閉じる</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            $("#MessageModal").modal();
        });
    </script>
    @endif
</body>

</html>