@extends('home')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{ asset("/bower_components/bootstrap-fileinput/css/fileinput.min.css")}}">

@endsection

@section('title', 'Nueva Pregunta')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Rellene los siquientes datos</h3>
                </div>

                <div class="box-body">


                    <form id="form" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="questionTitle">Título pregunta</label>
                            <input type="text" value="{{ old('questionTitle') }}" name="questionTitle" class="form-control" minlength="10" id="questionTitle" required>
                        </div>


                        <div class="form-group">
                            <label>Fecha y hora de inicio (24h)</label>

                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input name="datetime" value="{{ old('datetime') }}" type="text" class="form-control" data-inputmask="'alias': 'dd/mm/yyyy'" data-mask>
                            </div>
                            <!-- /.input group -->
                        </div>
                        <!-- /.form group -->

                        <div class="form-group">
                            <label>Duración (segundos)</label>
                            <select name="duration" value="{{ old('duration') }}" class="form-control">
                                <option value="30">30s</option>
                                <option value="60">60s</option>
                                <option value="90">90s</option>
                                <option value="120">120s</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Premio</label>
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <input type="checkbox" name="activatePrize">
                                </span>
                                <input type="text" class="form-control" name="prizeTitle" placeholder="Ej. Iphone 6">
                            </div>
                        </div>

                        <h3 class="page-header">Posibles respuestas</h3>

                        <div class="row" id="answersForm">
                            <div class="answerRow">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <input type="text" name="answer_title[0]" class="form-control" placeholder="Título respuesta">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <input type="file" name="answer_image[0]" class="form-control fileinput" >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="button" id="btnAddAnswer" class="btn btn-primary">+</button>
                        </div>


                        <div class="box-footer">
                            <button type="submit" class="btn btn-primary">Crear pregunta</button>
                        </div>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
        <!-- InputMask -->
    <script src="{{asset("/bower_components/AdminLTE/plugins/input-mask/jquery.inputmask.js")}}"></script>
    <script src="{{asset("/bower_components/AdminLTE/plugins/input-mask/jquery.inputmask.date.extensions.js")}}"></script>
    <script src="{{asset("/bower_components/AdminLTE/plugins/input-mask/jquery.inputmask.extensions.js")}}"></script>
    <script src="{{asset("/bower_components/AdminLTE/plugins/validation/jquery.validate.min.js")}}"></script>
    <script src="{{asset("/bower_components/AdminLTE/plugins/validation/additional-methods.min.js")}}"></script>
    <script src="{{asset("/bower_components/AdminLTE/plugins/validation/localization/messages_es.min.js")}}"></script>
    <script src="{{asset("/bower_components/bootstrap-fileinput/js/plugins/canvas-to-blob.min.js")}}"></script>
    <script src="{{asset("/bower_components/bootstrap-fileinput/js/fileinput.min.js")}}"></script>
    <script src="{{asset("/bower_components/bootstrap-fileinput/js/fileinput_locale_es.js")}}"></script>


    <script>
        $(function() {
            $("[data-mask]").inputmask({
                mask: "1/2/y h:s",
                placeholder: "dd/mm/yyyy hh:mm",
                alias: "datetime",
                hourFormat: "24"
            });

            $.validator.addMethod(
                    "spanishDate",
                    function(value, element) {
                        // put your own logic here, this is just a (crappy) example
                        return value.match(/^\d\d?\/\d\d?\/\d\d\d\d \d\d:\d\d$/);
                    },
                    "Inserte una fecha en el formato dd/mm/yyyy hh:mm"
            );


            $('#form').validate({
                rules : {
                    datetime : {
                        spanishDate : true
                    }
                },


            });

            $('.fileinput').fileinput({
                allowedFileTypes:['image'],
                language:'es',
                showUpload:false
            });

            $('body').on('click', '#btnAddAnswer', function(){

                var nextIndex = $('.fileinput').length;

                $('#answersForm').append('<div class="answerRow">\
                    <div class="col-md-8">\
                    <div class="form-group">\
                    <input type="text" name="answer_title['+nextIndex+']" class="form-control" placeholder="Título respuesta">\
                    </div>\
                    </div>\
                    <div class="col-md-4">\
                    <div class="form-group">\
                    <input type="file" name="answer_image['+nextIndex+']" class="form-control fileinput" >\
                    </div>\
                    </div>\
                    </div>');

                $('.fileinput').fileinput({
                    allowedFileTypes:['image'],
                    language:'es',
                    showUpload:false
                });
            });




        });

    </script>
@endsection