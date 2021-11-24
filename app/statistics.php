<?php include_once "../views/app_view.php"?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>STATISTICS</title>
<!-- Bootstrap CSS file -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-iBBXm8fW90+nuLcSKlbmrPcLa0OT92xO1BIsZ+ywDWZCvqsWgccV3gFoRBv0z+8dLJgyAHIhR35VZc2oM/gI1w==" crossorigin="anonymous" />
</head>
<body>
<div class="container">
    <div class="row mt-3">
        <div class="col-md-12 col-lg-12 col-xl-12">
            <div class="row justify-content-center">
                <h4>TABLE OF EMPLOYEES </h4>
            </div>
            <table class="table table-striped" id="table2">
                <thead>
                <tr>
                    <th>NO</th>
                    <th>NAME</th>
                    <th>POSITION</th>
                    <th>OFFICE</th>
                    <th>AGE</th>
                    <th>START DATE</th>
                    <th>SALARY</th>
                </tr>
                </thead>
                <tbody>
                    <?php $no= 0; foreach ($employees as $key => $emp): $no++;?>
                    <tr>
                        <td><?= $no; ?></td>
                        <td><?= $emp["name"] ?></td>
                        <td><?= $emp["position"] ?></td>
                        <td><?= $emp["office"] ?></td>
                        <td><?= $emp["age"] ?></td>
                        <td><?= $emp["start_date"] ?></td>
                        <td>$<?= $emp["salary"] ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                <tr>
                    <th>NO</th>
                    <th>NAME</th>
                    <th>POSITION</th>
                    <th>OFFICE</th>
                    <th>AGE</th>
                    <th>START DATE</th>
                    <th>SALARY</th>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12 col-lg-6 col-xl-6">
            <div class="card card-body shadow">
                <div class='row justify-content-between'>
                    <h5>MAXIMUM SALARY </h5><h5 class='text-success'>$<?= $simpleStat["max_salary"] ?></h5>
                </div><br/>
                <div class='row justify-content-between'>
                    <h5>MINIMUM SALARY </h5><h5 class='text-success'>$<?= $simpleStat["min_salary"] ?></h5>
                </div><br/>
                <div class='row justify-content-between'>
                    <h5>AVERAGE SALARY </h5> <h5 class='text-success'>$<?= $simpleStat["agv_salary"] ?></h5>
                </div><br/>
                <div class='row justify-content-between'>
                    <h5>HIGHLY EARNING EMPLOYEE </h5> <h5 class='text-success'><?= $simpleStat["most_payed"] ?></h5>
                </div><br/>
                <div class='row justify-content-between'>
                    <h5>LEAST EARNING EMPLOYEE </h5> <h5 class='text-success'><?= $simpleStat["least_payed"] ?></h5>
                </div><br/>
                <div class='row justify-content-between'>
                    <h5>YOUNGEST EMPLOYEE</h5> <h5 class='text-success'><?= $simpleStat["youngest"] ?></h5>
                </div><br/>
                <div class='row justify-content-between'>
                    <h5>ELDEST EMPLOYEE</h5> <h5 class='text-success'><?= $simpleStat["eldest"] ?></h5>
                </div><br/>
                <div class='row justify-content-between'>
                    <h5>AVERAGE EMPLOYEE AGE</h5> <h5 class='text-success'> <?= $simpleStat["avg_age"] ?></h5>
                </div>

            </div>
        </div>
        <div class="col-md-12 col-lg-6 col-xl-6 mt-3">
            <div id="chartContainer1" style="height: 370px; width: 100%;"></div>
        </div>
    </div>
    <div id="chartContainer" class="mt-3" style="height: 370px; width: 100%;"></div>
</div>

    <!-- JS files: jQuery first, then Popper.js, then Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/canvasjs/1.7.0/canvasjs.min.js" integrity="sha512-FJ2OYvUIXUqCcPf1stu+oTBlhn54W0UisZB/TNrZaVMHHhYvLBV9jMbvJYtvDe5x/WVaoXZ6KB+Uqe5hT2vlyA==" crossorigin="anonymous"></script>
<script>
    $(document).ready(function(){
        $("#table2").DataTable();
    })
</script>
<script>
    window.onload = function () {

            let chart2 = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                exportEnabled: true,
                theme: "light1", // "light1", "light2", "dark1", "dark2"
                title:{
                    text: "EMPLOYEES' AGE AND SALARY DISTRIBUTION"
                },
                axisY:{
                    includeZero: true
                },
                data: [{
                    type: "area", //change type to bar, line, area, pie, etc
                    indexLabel: "{y}", //Shows y value on all Data Points
                    indexLabelFontColor: "#5A5757",
                    indexLabelPlacement: "outside",
                    dataPoints: <?php echo json_encode($dataPoints, JSON_NUMERIC_CHECK); ?>
                }]
            });
        let chart1 = new CanvasJS.Chart("chartContainer1", {
            animationEnabled: true,
            theme: "light2",
            title:{
                text: "EMPLOYEES' SALARY DISTRIBUTION"
            },
            axisY: {
                title: "salary (in dollars)"
            },
            data: [{
                type: "column",
                yValueFormatString: "#,##0.## dollars",
                dataPoints: <?php echo json_encode($salaryDistribution, JSON_NUMERIC_CHECK); ?>
            }]
        });
        chart1.render();
        chart2.render();

        }
</script>
</body>
</html>