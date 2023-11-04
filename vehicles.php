<?php
require_once 'php/db_connect.php';

session_start();

if(!isset($_SESSION['userID'])){
  echo '<script type="text/javascript">';
  echo 'window.location.href = "login.html";</script>';
}
else{
  $user = $_SESSION['userID'];
  $transporters = $db->query("SELECT * FROM transporters WHERE deleted = '0'");
}
?>

<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
			<div class="col-sm-6">
				<h1 class="m-0 text-dark">Vehicles</h1>
			</div><!-- /.col -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<!-- Main content -->
<section class="content">
	<div class="container-fluid">
        <div class="row">
			<div class="col-12">
				<div class="card">
					<div class="card-header">
                        <div class="row">
                            <div class="col-9"></div>
                            <div class="col-3">
                                <button type="button" class="btn btn-block bg-gradient-warning btn-sm" id="addVehicles">Add Vehicles</button>
                            </div>
                        </div>
                    </div>
					<div class="card-body">
						<table id="vehicleTable" class="table table-bordered table-striped">
							<thead>
								<tr>
									<th>No.</th>
									<th>Vehicle No</th>
                                    <th>Driver</th>
                                    <th>Attandence 1</th>
                                    <th>Attandence 2</th>
									<th>Actions</th>
								</tr>
							</thead>
						</table>
					</div><!-- /.card-body -->
				</div><!-- /.card -->
			</div><!-- /.col -->
		</div><!-- /.row -->
	</div><!-- /.container-fluid -->
</section><!-- /.content -->

<div class="modal fade" id="vehicleModal">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <form role="form" id="vehicleForm">
            <div class="modal-header">
              <h4 class="modal-title">Add Vehicles</h4>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <div class="card-body">
                    <div class="form-group">
    					<input type="hidden" class="form-control" id="id" name="id">
    				</div>
    				<div class="form-group">
    					<label for="vehicleNumber">Vehicles No. *</label>
    					<input type="type" class="form-control" name="vehicleNumber" id="vehicleNumber" placeholder="Enter Vehicle Number" required>
    				</div>
                    <div class="form-group">
                        <label>Driver *</label>
                        <select class="form-control" style="width: 100%;" id="driver" name="driver" required>
                            <option selected="selected">-</option>
                            <?php while($rowCustomer2=mysqli_fetch_assoc($transporters)){ ?>
                                <option value="<?=$rowCustomer2['id'] ?>"><?=$rowCustomer2['transporter_name'] ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="form-group">
    					<label for="attendance1">Attendance 1</label>
    					<input type="type" class="form-control" name="attendance1" id="attendance1" placeholder="Enter Attendance 1">
    				</div>
                    <div class="form-group">
    					<label for="attendance2">Attendance 2</label>
    					<input type="type" class="form-control" name="attendance2" id="attendance2" placeholder="Enter Attendance 2">
    				</div>
    			</div>
            </div>
            <div class="modal-footer justify-content-between">
              <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
              <button type="submit" class="btn btn-primary" name="submit" id="submitVehicle">Submit</button>
            </div>
        </form>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<script>
$(function () {
    $("#vehicleTable").DataTable({
        "responsive": true,
        "autoWidth": false,
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'order': [[ 1, 'asc' ]],
        'columnDefs': [ { orderable: false, targets: [0] }],
        'ajax': {
            'url':'php/loadVehicles.php'
        },
        'columns': [
            { data: 'counter' },
            { data: 'veh_number' },
            { data: 'transporter_name' },
            { data: 'attandence_1' },
            { data: 'attandence_2' },
            { 
                data: 'id',
                render: function ( data, type, row ) {
                    return '<div class="row"><div class="col-3"><button type="button" id="edit'+data+'" onclick="edit('+data+')" class="btn btn-success btn-sm"><i class="fas fa-pen"></i></button></div><div class="col-3"><button type="button" id="deactivate'+data+'" onclick="deactivate('+data+')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></div></div>';
                }
            }
        ],
        "rowCallback": function( row, data, index ) {

            $('td', row).css('background-color', '#E6E6FA');
        },
    });
    
    $.validator.setDefaults({
        submitHandler: function () {
            $('#spinnerLoading').show();
            $.post('php/vehicles.php', $('#vehicleForm').serialize(), function(data){
                var obj = JSON.parse(data); 
                
                if(obj.status === 'success'){
                    $('#vehicleModal').modal('hide');
                    toastr["success"](obj.message, "Success:");
                    $('#vehicleTable').DataTable().ajax.reload();
                    $('#spinnerLoading').hide();
                }
                else if(obj.status === 'failed'){
                    toastr["error"](obj.message, "Failed:");
                    $('#spinnerLoading').hide();
                }
                else{
                    toastr["error"]("Something wrong when edit", "Failed:");
                    $('#spinnerLoading').hide();
                }
            });
        }
    });

    $('#addVehicles').on('click', function(){
        $('#vehicleModal').find('#id').val("");
        $('#vehicleModal').find('#vehicleNumber').val("");
        $('#vehicleModal').find('#driver').val("");
        $('#vehicleModal').find('#attendance1').val("");
        $('#vehicleModal').find('#attendance2').val("");
        $('#vehicleModal').modal('show');
        
        $('#vehicleForm').validate({
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
    });
});

function edit(id){
    $('#spinnerLoading').show();
    $.post('php/getVehicles.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            $('#vehicleModal').find('#id').val(obj.message.id);
            $('#vehicleModal').find('#vehicleNumber').val(obj.message.veh_number);
            $('#vehicleModal').find('#driver').val(obj.message.driver);
            $('#vehicleModal').find('#attendance1').val(obj.message.attandence_1);
            $('#vehicleModal').find('#attendance2').val(obj.message.attandence_2);
            $('#vehicleModal').modal('show');
            
            $('#vehicleForm').validate({
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
            toastr["error"]("Something wrong when activate", "Failed:");
        }
        $('#spinnerLoading').hide();
    });
}

function deactivate(id){
    $('#spinnerLoading').show();
    $.post('php/deleteVehicle.php', {userID: id}, function(data){
        var obj = JSON.parse(data);
        
        if(obj.status === 'success'){
            toastr["success"](obj.message, "Success:");
            $('#vehicleTable').DataTable().ajax.reload();
            $('#spinnerLoading').hide();
        }
        else if(obj.status === 'failed'){
            toastr["error"](obj.message, "Failed:");
            $('#spinnerLoading').hide();
        }
        else{
            toastr["error"]("Something wrong when activate", "Failed:");
            $('#spinnerLoading').hide();
        }
    });
}
</script>