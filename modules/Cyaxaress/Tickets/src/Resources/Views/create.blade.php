@extends('Dashboard::master')
@section('breadcrumb')
    <li><a href="{{ route('tickets.index') }}" title="تیکت ها">تیکت ها</a></li>
    <li><a href="#" title="ایجاد تیکت">ایجاد تیکت</a></li>
@endsection
@section('content')
    <div class="main-content padding-0">
        <p class="box__title">ایجاد تیکت جدید</p>
        <div class="row no-gutters bg-white">
            <div class="col-12">
                <form action="" class="padding-30">
                    <x-input type="text" class="text" name="title" placeholder="عنوان تیکت" required />
                    <x-textarea placeholder="متن تیکت" name="body" class="text" required/>

                    <x-file name="attachment" placeholder="آپلود فایل پیویست" />
                    <button class="btn btn-webamooz_net">ایجاد مقاله</button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script src="/panel/js/tagsInput.js?v=12"></script>
@endsection
