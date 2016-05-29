@extends('home')
@section('content')
<div class="row">
  <div class="col-md-12">
    <!-- AREA CHART -->
    <div class="box box-primary">
      <div class="box-header with-border">
        <h3 class="box-title">Datos por provincia</h3>
        <div class="box-tools pull-right">
          <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
          <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
        </div>
      </div>
      <div class="box-body">
        <div class="chart">
          <canvas id="provincesChart" style="height:250px"></canvas>
        </div>
      </div><!-- /.box-body -->
    </div><!-- /.box -->

  </div><!-- /.col (LEFT) -->
</div>
<div class="row">
  <div class="col-md-6">
    <!-- LINE CHART -->
    <!-- BAR CHART -->
    <div class="box box-success">
      <div class="box-header with-border">
        <h3 class="box-title">Estadística Gral.</h3>
        <div class="box-tools pull-right">
          <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
          <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
        </div>
      </div>
      <div class="box-body">
        <div class="chart">
          <canvas id="barChart" style="height:230px"></canvas>
        </div>
      </div><!-- /.box-body -->
    </div><!-- /.box -->
  </div><!-- /.col (RIGHT) -->

  <div class="col-md-6">
    <!-- LINE CHART -->
    <!-- BAR CHART -->
    <div class="box box-success">
      <div class="box-header with-border">
        <h3 class="box-title">Estadística Gral.</h3>
        <div class="box-tools pull-right">
          <button class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
          <button class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
        </div>
      </div>
      <div class="box-body">
        <div class="chart">
          <canvas id="agesChart" style="height:230px"></canvas>
        </div>
      </div><!-- /.box-body -->
    </div><!-- /.box -->
  </div><!-- /.col (RIGHT) -->

</div><!-- /.row -->
@endsection
@section('scripts')
@parent
    <!-- ChartJS 1.0.1 -->
    <script src="{{ asset("/bower_components/AdminLTE/plugins/chartjs2/Chart.min.js")}}"></script>
    <!-- FastClick -->
    <script src="{{ asset("/bower_components/AdminLTE/plugins/fastclick/fastclick.min.js")}}"></script>

    <!-- AdminLTE for demo purposes -->
    <script src="{{ asset("/bower_components/AdminLTE/dist/js/demo.js")}}"></script>
    <!-- page script -->

    <script>
      var ctx = $("#barChart");

      Chart.defaults.global.tooltips.callbacks.label = function(tooltipItems, data) {
          var index= tooltipItems.index;
          var datasetIndex= tooltipItems.datasetIndex;

          console.log(tooltipItems);
          console.log(data);

          return data.datasets[datasetIndex].data[index] + "%";
      }

      var myChart = new Chart(ctx, {
        type: 'bar',

        tooltipTemplate: function (d) {
          console.log(d);
          if (d.value === null)
            throw '';
          else
          // else return the normal tooltip text
            return d.label + ': ' + d.value;
        },

        data: {
          labels: ["Datos Generales"],
          datasets: <?=$datasets?>,

        },
        options: {
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero:true
              }
            }]
          }
        }
      });

      //CHART PROVINCIAS

      var ctx_provincias = $("#provincesChart");

      Chart.defaults.global.tooltips.callbacks.label = function(tooltipItems, data) {
        var index= tooltipItems.index;
        var datasetIndex= tooltipItems.datasetIndex;

        console.log(tooltipItems);
        console.log(data);

        return data.datasets[datasetIndex].data[index] + "%";
      }

      var myChart = new Chart(ctx_provincias, {
        type: 'bar',

        tooltipTemplate: function (d) {
          console.log(d);
          if (d.value === null)
            throw '';
          else
          // else return the normal tooltip text
            return d.label + ': ' + d.value;
        },

        data: <?=$dataProvincias?>,
        options: {
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero:true
              }
            }]
          }
        }
      });


      //CHART AGES

      var ctx_ages = $("#agesChart");

      Chart.defaults.global.tooltips.callbacks.label = function(tooltipItems, data) {
        var index= tooltipItems.index;
        var datasetIndex= tooltipItems.datasetIndex;

        console.log(tooltipItems);
        console.log(data);

        return data.datasets[datasetIndex].data[index] + "%";
      }

      var myChart = new Chart(ctx_ages, {
        type: 'bar',

        tooltipTemplate: function (d) {
          console.log(d);
          if (d.value === null)
            throw '';
          else
          // else return the normal tooltip text
            return d.label + ': ' + d.value;
        },

        data: <?=$dataAges?>,
        options: {
          scales: {
            yAxes: [{
              ticks: {
                beginAtZero:true
              }
            }]
          }
        }
      });



    </script>

@endsection
