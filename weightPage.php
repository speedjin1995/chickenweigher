<?php
require_once 'php/db_connect.php';

session_start();

if(!isset($_SESSION['userID'])){
  echo '<script type="text/javascript">';
  echo 'window.location.href = "login.html";</script>';
}
else{
  $user = $_SESSION['userID'];
  $stmt = $db->prepare("SELECT * from users where id = ?");
	$stmt->bind_param('s', $user);
	$stmt->execute();
	$result = $stmt->get_result();
	
	if(($row = $result->fetch_assoc()) !== null){
    $role = $row['role_code'];
  }

  $lots = $db->query("SELECT * FROM lots WHERE deleted = '0'"); // Groups
  $vehicles = $db->query("SELECT * FROM vehicles WHERE deleted = '0'"); // Vehicles
  $products = $db->query("SELECT * FROM products WHERE deleted = '0'"); // Products
  $packages = $db->query("SELECT * FROM packages WHERE deleted = '0'"); // Farms
  $customers = $db->query("SELECT * FROM customers WHERE deleted = '0'"); // Customers
  $units = $db->query("SELECT * FROM units WHERE deleted = '0'"); // Grades
  $users = $db->query("SELECT * FROM users WHERE deleted = '0'"); // Users
  $transporters = $db->query("SELECT * FROM transporters WHERE deleted = '0'"); // Drivers
}
?>

<style>
  @media screen and (min-width: 676px) {
    .modal-dialog {
      max-width: 1800px; /* New width for default modal */
    }
  }
</style>

<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0 text-dark">Weight Weighing</h1>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<div class="content">
  <div class="container-fluid">
    <!--div div class="row">
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box" id="saleCard">
          <span class="info-box-icon bg-info">
            <i class="fas fa-shopping-cart"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Sales</span>
            <span class="info-box-number" id="salesInfo">0</span>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box" id="purchaseCard">
          <span class="info-box-icon bg-success">
            <i class="fas fa-shopping-basket"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Purchase</span>
            <span class="info-box-number" id="purchaseInfo">0</span>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box" id="miscCard">
          <span class="info-box-icon bg-warning">
            <i class="fas fa-warehouse" style="color: white;"></i>
          </span>
          <div class="info-box-content">
            <span class="info-box-text">Miscellaneous</span>
            <span class="info-box-number" id="localInfo">0</span>
          </div>
        </div>
      </div>

      <div class="col-md-3 col-sm-6 col-12">
        <div class="input-group-text color-palette" id="indicatorConnected"><i>Indicator Connected</i></div>
        <div class="input-group-text bg-danger color-palette" id="checkingConnection"><i>Checking Connection</i></div>
      </div>
    </div-->

    <div class="row">

      <!-- <div class="col-lg-12">
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-4">
                <div class="input-group-text color-palette" id="indicatorConnected"><i>Indicator Connected</i></div>
              </div>
              <div class="col-4">
                <div class="input-group-text bg-danger color-palette" id="checkingConnection"><i>Checking Connection</i></div>
              </div>
              <div class="col-4">
                <button type="button" class="btn btn-block bg-gradient-primary"  onclick="setup()">
                  Setup
                </button>
              </div>
            </div>
          </div>
        </div>
      </div> -->

      <div class="col-lg-12">
        <div class="card card-primary">
          <div class="card-header">
            <div class="row">
              <div class="col-6"></div>
              <div class="col-3">
                <button type="button" class="btn btn-block bg-gradient-warning btn-sm" id="refreshBtn">Refresh</button>
              </div>
              <div class="col-3">
                <button type="button" class="btn btn-block bg-gradient-warning btn-sm" onclick="newEntry()">Add New Weight</button>
              </div>
            </div>
          </div>

          <div class="card-body">
            <table id="weightTable" class="table table-bordered table-striped display">
              <thead>
                <tr>
                  <th>No</th>
                  <th>Serial No</th>
                  <th>Customers</th>
                  <th>Product</th>
                  <th>Vehicle No</th>
                  <th>Driver Name</th>
                  <th>Farm Id</th>
                  <th></th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="extendModal">
  <div class="modal-dialog modal-xl" style="max-width: 90%;">
    <div class="modal-content">
      <form role="form" id="extendForm">
        <div class="modal-header bg-gray-dark color-palette">
          <h4 class="modal-title">Add New Jobs</h4>
          <button type="button" class="close bg-gray-dark color-palette" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <input type="hidden" class="form-control" id="id" name="id">
          <div class="row">
            <div class="col-4">
              <div class="form-group">
                <label class="labelStatus">Customer No *</label>
                <select class="form-control" style="width: 100%;" id="customerNo" name="customerNo" required>
                  <option value="" selected disabled hidden>Please Select</option>
                  <?php while($rowCustomer=mysqli_fetch_assoc($customers)){ ?>
                    <option value="<?=$rowCustomer['customer_name'] ?>"><?=$rowCustomer['customer_name'] ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="col-4">
              <div class="form-group">
                <label>Product *</label>
                <select class="form-control" style="width: 100%;" id="product" name="product" required>
                  <option selected="selected">-</option>
                  <?php while($row5=mysqli_fetch_assoc($products)){ ?>
                    <option value="<?=$row5['product_name'] ?>"><?=$row5['product_name'] ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="col-4">
              <div class="form-group">
                <label class="vehicleNo">Vehicle No *</label>
                <select class="form-control" id="vehicleNo" name="vehicleNo" required>
                  <option selected="selected">-</option>
                  <?php while($row2=mysqli_fetch_assoc($vehicles)){ ?>
                    <option value="<?=$row2['veh_number'] ?>" ><?=$row2['veh_number'] ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="form-group col-4">
              <label>Driver *</label>
              <select class="form-control" style="width: 100%;" id="driver" name="driver">
                  <option selected="selected">-</option>
                  <?php while($row5=mysqli_fetch_assoc($transporters)){ ?>
                    <option value="<?=$row5['transporter_name'] ?>"><?=$row5['transporter_name'] ?></option>
                  <?php } ?>
              </select>
            </div>
            <div class="col-4">
              <div class="form-group">
                <label>Farm *</label>
                <select class="form-control" style="width: 100%;" id="farm" name="farm" required>
                  <option selected="selected">-</option>
                  <?php while($row6=mysqli_fetch_assoc($packages)){ ?>
                    <option value="<?=$row6['packages'] ?>"><?=$row6['packages'] ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="form-group col-4">
              <label>Average Bird Weight</label>
              <input class="form-control" type="number" placeholder="Average Bird Weight" id="aveBird" name="aveBird">
            </div>
            <div class="form-group col-4">
              <label>Average Cage Weight</label>
              <input class="form-control" type="number" placeholder="Average Cage Weight" id="aveCage" name="aveCage">
            </div>
            <div class="form-group col-4">
              <label>Min Weight </label>
              <input class="form-control" type="number" placeholder="Min Weight" id="minWeight" name="minWeight">
            </div>
            <div class="form-group col-4">
              <label>Max Weight </label>
              <input class="form-control" type="number" placeholder="Max Weight" id="maxWeight" name="maxWeight">
            </div>
            <div class="col-4">
              <div class="form-group">
                <label>Assigned To *</label>
                <select class="form-control" style="width: 100%;" id="assignTo" name="assignTo" required> 
                  <option selected="selected">-</option>
                  <?php while($rowusers=mysqli_fetch_assoc($users)){ ?>
                    <option value="<?=$rowusers['id'] ?>"><?=$rowusers['name'] ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
            <div class="col-12">
              <div class="form-group">
                <label>Remark</label>
                <textarea class="form-control" rows="1" placeholder="Enter ..." id="remark" name="remark"></textarea>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between bg-gray-dark color-palette">
          <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary" id="saveButton">Save changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Values
var controlflow = "None";
var indicatorUnit = "kg";
var weightUnit = "1";
var rate = 1;
var currency = "1";

$(function () {
  $('#customerNoHidden').hide();
  $('#supplierNoHidden').hide();

  var table = $("#weightTable").DataTable({
    "responsive": true,
    "autoWidth": false,
    'processing': true,
    'serverSide': true,
    'serverMethod': 'post',
    'searching': true,
    'order': [[ 1, 'asc' ]],
    'columnDefs': [ { orderable: false, targets: [0] }],
    'ajax': {
        'url':'php/loadWeights.php'
    },
    'columns': [
      { data: 'no' },
      { data: 'serial_no' },
      { data: 'customer' },
      { data: 'product' },
      { data: 'lorry_no' },
      { data: 'driver_name' },
      { data: 'farm_id' },
      { 
        className: 'dt-control',
        orderable: false,
        data: null,
        render: function ( data, type, row ) {
          return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
        }
      }
    ],
    "rowCallback": function( row, data, index ) {
      $('td', row).css('background-color', '#E6E6FA');
    },
    "drawCallback": function(settings) {
      
    }
  });

  // Add event listener for opening and closing details
  $('#weightTable tbody').on('click', 'td.dt-control', function () {
    var tr = $(this).closest('tr');
    var row = table.row( tr );

    if ( row.child.isShown() ) {
      // This row is already open - close it
      row.child.hide();
      tr.removeClass('shown');
    }
    else {
      // Open this row
      <?php 
        if($role == "ADMIN"){
          echo 'row.child( format(row.data()) ).show();tr.addClass("shown");';
        }
        else{
          echo 'row.child( formatNormal(row.data()) ).show();tr.addClass("shown");';
        }
      ?>
    }
  });
  
  //Date picker
  $('#fromDate').datetimepicker({
      icons: { time: 'far fa-clock' },
      format: 'DD/MM/YYYY hh:mm:ss A'
  });

  $('#toDate').datetimepicker({
      icons: { time: 'far fa-clock' },
      format: 'DD/MM/YYYY hh:mm:ss A'
  });

  /*$.post('http://127.0.0.1:5002/', $('#setupForm').serialize(), function(data){
    if(data == "true"){
      $('#indicatorConnected').addClass('bg-primary');
      $('#checkingConnection').removeClass('bg-danger');
      //$('#captureWeight').removeAttr('disabled');
    }
    else{
      $('#indicatorConnected').removeClass('bg-primary');
      $('#checkingConnection').addClass('bg-danger');
      //$('#captureWeight').attr('disabled', true);
    }
  });
  
  setInterval(function () {
    $.post('http://127.0.0.1:5002/handshaking', function(data){
      if(data != "Error"){
        console.log("Data Received:" + data);
        var text = data.split(" ");
        $('#indicatorWeight').html(text[text.length - 1]);
        $('#indicatorConnected').addClass('bg-primary');
        $('#checkingConnection').removeClass('bg-danger');
      }
      else{
        $('#indicatorConnected').removeClass('bg-primary');
        $('#checkingConnection').addClass('bg-danger');
      }
    });
  }, 500);*/

  $.validator.setDefaults({
    submitHandler: function () {
      if($('#extendModal').hasClass('show')){
        $('#spinnerLoading').show();

        $.post('php/insertWeight.php', $('#extendForm').serialize(), function(data){
          var obj = JSON.parse(data); 
          if(obj.status === 'success'){
            $('#extendModal').modal('hide');
            toastr["success"](obj.message, "Success:");
            $('#weightTable').DataTable().ajax.reload();
          }
          else if(obj.status === 'failed'){
            toastr["error"](obj.message, "Failed:");
          }
          else{
            toastr["error"]("Something wrong when edit", "Failed:");
          }

          $('#spinnerLoading').hide();
        });
      }
    }
  });

  $('#refreshBtn').on('click', function(){
    var fromDateValue = '';
    var toDateValue = '';
    var statusFilter = '';
    var customerNoFilter = '';
    var vehicleFilter = '';
    var invoiceFilter = '';
    var batchFilter = '';
    var productFilter = '';

    //Destroy the old Datatable
    $("#weightTable").DataTable().clear().destroy();

    table = $("#weightTable").DataTable({
      "responsive": true,
      "autoWidth": false,
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'searching': true,
      'order': [[ 1, 'asc' ]],
      'columnDefs': [ { orderable: false, targets: [0] }],
      'ajax': {
          'url':'php/loadWeights.php'
      },
      'columns': [
        { data: 'no' },
        { data: 'serial_no' },
        { data: 'customer' },
        { data: 'product' },
        { data: 'lorry_no' },
        { data: 'driver_name' },
        { data: 'farm_id' },
        { 
          className: 'dt-control',
          orderable: false,
          data: null,
          render: function ( data, type, row ) {
            return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
          }
        }
      ],
      "rowCallback": function( row, data, index ) {
        $('td', row).css('background-color', '#E6E6FA');
      },
      "drawCallback": function(settings) {
        
      }
    });

    //Create new Datatable
    /*table = $("#weightTable").DataTable({
      "responsive": true,
      "autoWidth": false,
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'searching': true,
      'order': [[ 1, 'asc' ]],
      'columnDefs': [ { orderable: false, targets: [0] }],
      'ajax': {
        'type': 'POST',
        'url':'php/filterWeight.php',
        'data': {
          fromDate: fromDateValue,
          toDate: toDateValue,
          status: statusFilter,
          customer: customerNoFilter,
          vehicle: vehicleFilter,
          invoice: invoiceFilter,
          batch: batchFilter,
          product: productFilter,
        } 
      },
      'columns': [
        { data: 'no' },
        { data: 'pStatus' },
        { data: 'status' },
        { data: 'serialNo' },
        { data: 'veh_number' },
        { data: 'product_name' },
        { data: 'currentWeight' },
        { data: 'inCDateTime' },
        { data: 'tare' },
        { data: 'outGDateTime' },
        { data: 'totalWeight' },
        { 
          className: 'dt-control',
          orderable: false,
          data: null,
          render: function ( data, type, row ) {
            return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
          }
        }
      ],
      "rowCallback": function( row, data, index ) {
        $('td', row).css('background-color', '#E6E6FA');
      },
      "drawCallback": function(settings) {
        $('#salesInfo').text(settings.json.salesTotal);
        $('#purchaseInfo').text(settings.json.purchaseTotal);
        $('#localInfo').text(settings.json.localTotal);
      }
    });*/
  });

  $('#saleCard').on('click', function(){
    var fromDateValue = '';
    var toDateValue = '';
    var statusFilter = '1';
    var customerNoFilter = '';
    var vehicleFilter = '';
    var invoiceFilter = '';
    var batchFilter = '';
    var productFilter = '';

    //Destroy the old Datatable
    $("#weightTable").DataTable().clear().destroy();

    //Create new Datatable
    table = $("#weightTable").DataTable({
      "responsive": true,
      "autoWidth": false,
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'searching': true,
      'order': [[ 1, 'asc' ]],
      'columnDefs': [ { orderable: false, targets: [0] }],
      'ajax': {
        'type': 'POST',
        'url':'php/filterWeight.php',
        'data': {
          fromDate: fromDateValue,
          toDate: toDateValue,
          status: statusFilter,
          customer: customerNoFilter,
          vehicle: vehicleFilter,
          invoice: invoiceFilter,
          batch: batchFilter,
          product: productFilter,
        } 
      },
      'columns': [
        { data: 'no' },
        { data: 'pStatus' },
        { data: 'status' },
        { data: 'serialNo' },
        { data: 'veh_number' },
        { data: 'product_name' },
        { data: 'currentWeight' },
        { data: 'inCDateTime' },
        { data: 'tare' },
        { data: 'outGDateTime' },
        { data: 'totalWeight' },
        { 
          className: 'dt-control',
          orderable: false,
          data: null,
          render: function ( data, type, row ) {
            return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
          }
        }
      ],
      "rowCallback": function( row, data, index ) {
        $('td', row).css('background-color', '#E6E6FA');
      }
    });
  });

  $('#purchaseCard').on('click', function(){
    var fromDateValue = '';
    var toDateValue = '';
    var statusFilter = '2';
    var customerNoFilter = '';
    var vehicleFilter = '';
    var invoiceFilter = '';
    var batchFilter = '';
    var productFilter = '';

    //Destroy the old Datatable
    $("#weightTable").DataTable().clear().destroy();

    //Create new Datatable
    table = $("#weightTable").DataTable({
      "responsive": true,
      "autoWidth": false,
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'searching': true,
      'order': [[ 1, 'asc' ]],
      'columnDefs': [ { orderable: false, targets: [0] }],
      'ajax': {
        'type': 'POST',
        'url':'php/filterWeight.php',
        'data': {
          fromDate: fromDateValue,
          toDate: toDateValue,
          status: statusFilter,
          customer: customerNoFilter,
          vehicle: vehicleFilter,
          invoice: invoiceFilter,
          batch: batchFilter,
          product: productFilter,
        } 
      },
      'columns': [
        { data: 'no' },
        { data: 'pStatus' },
        { data: 'status' },
        { data: 'serialNo' },
        { data: 'veh_number' },
        { data: 'product_name' },
        { data: 'currentWeight' },
        { data: 'inCDateTime' },
        { data: 'tare' },
        { data: 'outGDateTime' },
        { data: 'totalWeight' },
        { 
          className: 'dt-control',
          orderable: false,
          data: null,
          render: function ( data, type, row ) {
            return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
          }
        }
      ],
      "rowCallback": function( row, data, index ) {
        $('td', row).css('background-color', '#E6E6FA');
      }
    });
  });

  $('#miscCard').on('click', function(){
    var fromDateValue = '';
    var toDateValue = '';
    var statusFilter = '3';
    var customerNoFilter = '';
    var vehicleFilter = '';
    var invoiceFilter = '';
    var batchFilter = '';
    var productFilter = '';

    //Destroy the old Datatable
    $("#weightTable").DataTable().clear().destroy();

    //Create new Datatable
    table = $("#weightTable").DataTable({
      "responsive": true,
      "autoWidth": false,
      'processing': true,
      'serverSide': true,
      'serverMethod': 'post',
      'searching': true,
      'order': [[ 1, 'asc' ]],
      'columnDefs': [ { orderable: false, targets: [0] }],
      'ajax': {
        'type': 'POST',
        'url':'php/filterWeight.php',
        'data': {
          fromDate: fromDateValue,
          toDate: toDateValue,
          status: statusFilter,
          customer: customerNoFilter,
          vehicle: vehicleFilter,
          invoice: invoiceFilter,
          batch: batchFilter,
          product: productFilter,
        } 
      },
      'columns': [
        { data: 'no' },
        { data: 'pStatus' },
        { data: 'status' },
        { data: 'serialNo' },
        { data: 'veh_number' },
        { data: 'product_name' },
        { data: 'currentWeight' },
        { data: 'inCDateTime' },
        { data: 'tare' },
        { data: 'outGDateTime' },
        { data: 'totalWeight' },
        { 
          className: 'dt-control',
          orderable: false,
          data: null,
          render: function ( data, type, row ) {
            return '<td class="table-elipse" data-toggle="collapse" data-target="#demo'+row.serialNo+'"><i class="fas fa-angle-down"></i></td>';
          }
        }
      ],
      "rowCallback": function( row, data, index ) {
        $('td', row).css('background-color', '#E6E6FA');
      }
    });
  });

  $('#status').on('change', function(){
    if($(this).val() == 'Sales'){
      $('#extendModal').find('#customerNo').html($('select#customerNoHidden').html());
      $('#extendModal').find('.labelStatus').text('Customer *');
    }
    else{
      $('#extendModal').find('#customerNo').html($('select#supplierNoHidden').html());
      $('#extendModal').find('.labelStatus').text('Supplier *');
    }
  });
});

function updatePrices(isFromCurrency, rat){
  var totalPrice;
  var unitPrice = $('#unitPrice').val();
  var totalWeight = $('#totalWeight').val();

  if(isFromCurrency == 'Y'){
    unitPrice = (unitPrice / rate) * parseFloat(rat);
    $('#extendModal').find('#unitPrice').val(unitPrice.toFixed(2));
    rate = parseFloat(rat).toFixed(2);
  }
  else{
    unitPrice = unitPrice * parseFloat(rat);
    $('#extendModal').find('#unitPrice').val(unitPrice.toFixed(2));
    rate = parseFloat(rat).toFixed(2);
  }
  

  if(unitPrice != '' &&  moq != '' && totalWeight != ''){
    totalPrice = unitPrice * totalWeight;
    $('#totalPrice').val(totalPrice.toFixed(2));
  }
  else(
    $('#totalPrice').val((0).toFixed(2))
  )
}

function updateWeights(){
  var tareWeight =  0;
  var currentWeight =  0;
  var reduceWeight = 0;
  var moq = $('#moq').val();
  var totalWeight = 0;
  var actualWeight = 0;

  if($('#currentWeight').val()){
    currentWeight =  $('#currentWeight').val();
  }

  if($('#tareWeight').val()){
    tareWeight =  $('#tareWeight').val();
  }

  if($('#reduceWeight').val()){
    reduceWeight =  $('#reduceWeight').val();
  }

  if(tareWeight == 0){
    actualWeight = currentWeight - reduceWeight;
    actualWeight = Math.abs(actualWeight);
    $('#actualWeight').val(actualWeight.toFixed(2));
  }
  else{
    actualWeight = tareWeight - currentWeight - reduceWeight;
    actualWeight = Math.abs(actualWeight);
    $('#actualWeight').val(actualWeight.toFixed(2));
  }

  if(actualWeight != '' &&  moq != ''){
    totalWeight = actualWeight * moq;
    $('#totalWeight').val(totalWeight.toFixed(2));
  }
  else{
    $('#totalWeight').val((0).toFixed(2))
  };
}

function format (row) {
  return '<div class="row"><div class="col-md-3"><p>Average Cage Weight: '+row.average_cage+
  ' kg</p></div><div class="col-md-3"><p>Average Bird Weight: '+row.average_bird+
  ' kg</p></div><div class="col-md-3"><p>Minimum Weight: '+row.minimum_weight+
  ' kg</p></div><div class="col-md-3"><p>Maximum Weight: '+row.maximum_weight+
  ' kg</p></div></div><div class="row"><div class="col-3"><button type="button" class="btn btn-danger btn-sm" onclick="deactivate('+row.id+
  ')"><i class="fas fa-trash"></i></button></div><div class="col-3"><button type="button" class="btn btn-info btn-sm" onclick="print('+row.id+
  ')"><i class="fas fa-print"></i></button></div></div></div></div>'+
  '</div>';
}

function formatNormal (row) {
  return '<div class="row"><div class="col-md-3"><p>Average Cage Weight: '+row.average_cage+
  ' kg</p></div><div class="col-md-3"><p>Average Bird Weight: '+row.average_bird+
  ' kg</p></div><div class="col-md-3"><p>Minimum Weight: '+row.minimum_weight+
  ' kg</p></div><div class="col-md-3"><p>Maximum Weight: '+row.maximum_weight+
  ' kg</p></div></div><div class="row"><div class="col-3"><button type="button" class="btn btn-danger btn-sm" onclick="deactivate('+row.id+
  ')"><i class="fas fa-trash"></i></button></div><div class="col-3"><button type="button" class="btn btn-info btn-sm" onclick="print('+row.id+
  ')"><i class="fas fa-print"></i></button></div></div></div></div>'+
  '</div>';
}

function newEntry(){
  var date = new Date();
  $('#extendModal').find('#id').val("");
  $('#extendModal').find('#customerNo').val('');
  $('#extendModal').find('#product').val('');
  $('#extendModal').find('#vehicleNo').val('');
  $('#extendModal').find('#driver').val('');
  $('#extendModal').find('#farm').val('');
  $('#extendModal').find('#aveBird').val("");
  $('#extendModal').find('#aveCage').val('');
  $('#extendModal').find('#minWeight').val('');
  $('#extendModal').find('#maxWeight').val("");
  $('#extendModal').find('#assignTo').val("");
  $('#extendModal').find('#remark').val("");
  $('#extendModal').modal('show');
  
  $('#extendForm').validate({
    errorElement: 'span',
    errorPlacement: function (error, element) {
      error.addClass('invalid-feedback');
      element.closest('.form-group').append(error);
    },
    highlight: function (element, errorClass, validClass) {
      $(element).addClass('is-invalid');
    },
    unhighlight: function (element, errorClass, validClass) {
      $(element).removeClass('is-invalid');
    }
  });
}

function numberWithCommas(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function edit(id) {
  $('#spinnerLoading').show();
  $.post('php/getWeights.php', {userID: id}, function(data){
    var obj = JSON.parse(data);
    
    if(obj.status === 'success'){
      $('#extendModal').find('#id').val(obj.message.id);
      $('#extendModal').find('#status').val(obj.message.status);
      $('#extendModal').find('#product').val(obj.message.product);
      $('#extendModal').find('#vehicleNo').val(obj.message.lorry_no);
      $('#extendModal').find('#driver').val(obj.message.driver_name);
      $('#extendModal').find('#farm').val(obj.message.farm_id);
      $('#extendModal').find('#aveBird').val(obj.message.average_bird);
      $('#extendModal').find('#aveCage').val(obj.message.average_cage);
      $('#extendModal').find('#minWeight').val(obj.message.minimum_weight);
      $('#extendModal').find('#maxWeight').val(obj.message.maximum_weight);
      $('#extendModal').find('#assignTo').val(obj.message.weighted_by);
      $('#extendModal').find('#remark').val(obj.message.remark);

      /*if($('#extendModal').find('#status').val() == 'Sales'){
        $('#extendModal').find('#customerNo').html($('select#customerNoHidden').html());
        $('#extendModal').find('.labelStatus').text('Customer *');
        $('#extendModal').find('#customerNo').val(obj.message.customer);
      }
      else{
        $('#extendModal').find('#customerNo').html($('select#supplierNoHidden').html());
        $('#extendModal').find('.labelStatus').text('Supplier *');
        $('#extendModal').find('#customerNo').val(obj.message.supplier);
      }*/

      $('#extendModal').modal('show');
      $('#extendForm').validate({
        errorElement: 'span',
        errorPlacement: function (error, element) {
          error.addClass('invalid-feedback');
          element.closest('.form-group').append(error);
        },
        highlight: function (element, errorClass, validClass) {
          $(element).addClass('is-invalid');
        },
        unhighlight: function (element, errorClass, validClass) {
          $(element).removeClass('is-invalid');
        }
      });
    }
    else if(obj.status === 'failed'){
      toastr["error"](obj.message, "Failed:");
    }
    else{
      toastr["error"]("Something wrong when pull data", "Failed:");
    }
    $('#spinnerLoading').hide();
  });
}

function deactivate(id) {
  if (confirm('Are you sure you want to delete this items?')) {
    $('#spinnerLoading').show();
    $.post('php/deleteWeight.php', {userID: id}, function(data){
      var obj = JSON.parse(data);

      if(obj.status === 'success'){
        toastr["success"](obj.message, "Success:");
        $('#weightTable').DataTable().ajax.reload();
        /*$.get('weightPage.php', function(data) {
          $('#mainContents').html(data);
        });*/
      }
      else if(obj.status === 'failed'){
        toastr["error"](obj.message, "Failed:");
      }
      else{
        toastr["error"]("Something wrong when activate", "Failed:");
      }
      $('#spinnerLoading').hide();
    });
  }
}

function print(id) {
  $.post('php/print.php', {userID: id, file: 'weight'}, function(data){
    var obj = JSON.parse(data);

    if(obj.status === 'success'){
      var printWindow = window.open('', '', 'height=400,width=800');
      printWindow.document.write(obj.message);
      printWindow.document.close();
      setTimeout(function(){
        printWindow.print();
        printWindow.close();
      }, 500);

      /*$.get('weightPage.php', function(data) {
        $('#mainContents').html(data);
      });*/
    }
    else if(obj.status === 'failed'){
      toastr["error"](obj.message, "Failed:");
    }
    else{
      toastr["error"]("Something wrong when activate", "Failed:");
    }
  });
}

function portrait(id) {
  $.post('php/printportrait.php', {userID: id, file: 'weight'}, function(data){
    var obj = JSON.parse(data);

    if(obj.status === 'success'){
      var printWindow = window.open('', '', 'height=400,width=800');
      printWindow.document.write(obj.message);
      printWindow.document.close();
      setTimeout(function(){
        printWindow.print();
        printWindow.close();
      }, 500);
    }
    else if(obj.status === 'failed'){
      toastr["error"](obj.message, "Failed:");
    }
    else{
      toastr["error"]("Something wrong when activate", "Failed:");
    }
  });
}
</script>