@extends('home')

@section('styles')
    @parent
    <link rel="stylesheet" href="{{asset('/bower_components/AdminLTE/plugins/datatables/dataTables.bootstrap.css')}}">
@endsection

@section('title', 'Listado Preguntas')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-header with-border">
                    <h3 class="box-title">Próximas preguntas</h3>
                </div>
                <div class="box-body">
                    <div id="table-toolbar">
                        <div class="row">
                            <div class="col-md-2">
                                <button type="button" id="deleteQuestions" class="btn btn-block btn-danger disabled">Borrar</button>
                            </div>
                            <div class="col-md-2">
                                <a href="{{url("/questions/add")}}"><button type="button" id="addQuestion" class="btn btn-block btn-primary">Añadir nueva pregunta</button></a>
                            </div>
                        </div>
                    </div>
                    <table id="nextQuestionsTable" class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>
                                    <input type="checkbox" class="cb_select_all" data-table-id="nextQuestionsTable">
                                </th>
                                <th>Título</th>
                                <th>Fecha de lanzamiento (UTC)</th>
                                <th>Duración</th>
                            </tr>

                        </thead>
                        <tbody>
                            @foreach($questions as $question)
                            <tr>
                                <td>
                                    <input type="checkbox" data-selectable name="cb_nextquestions[]" value="1">
                                </td>
                                <td>
                                    {{$question->title}}
                                </td>
                                <td>
                                    {{\Carbon\Carbon::createFromFormat("Y-m-d H:i:s", $question->time_ini, 'Europe/Madrid')->format('d/m/Y - H:i:s')}}
                                </td>
                                <td>
                                    {{$question->duration}}s
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    @parent
    <script src="{{asset('/bower_components/AdminLTE/plugins/datatables/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('/bower_components/AdminLTE/plugins/datatables/dataTables.bootstrap.min.js')}}"></script>
    <script>
        $(function () {
            $('#nextQuestionsTable').DataTable({
                "aoColumnDefs": [
                    { 'bSortable': false, 'aTargets': [ 0 ] }
                ],
                "dom": '<"toolbar">frtip',
                "paging": true,
                "lengthChange": true,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": false
            });
            $("div.toolbar").html($("#table-toolbar").html());
            $("#table-toolbar").remove();

            $('body').on('click', 'input[type="checkbox"]', function() {
                if($('input:checkbox[data-selectable]:checked').length > 0) {
                    $('#deleteQuestions').removeClass('disabled');
                }
                else {
                    $('#deleteQuestions').addClass('disabled');
                }
            });
        });
    </script>
@endsection