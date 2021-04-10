<?php
include_once 'scraping.php';
/**
 * Loop through this data
 */
$data = Scrapping::getData("epl_stats");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PREMIER LEAGUE STATISTICS</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
    <br/>
    <div class="container">
<table class="table table-stripped table-success table-bordered">
    <thead> 
        <th>NO</th>
        <th>PLAYER NAME</th>
        <th>GAMES PLAYED</th>
        <th>GOALS</th>        
    </thead>

    <tbody> 
        <?php foreach($data as $player): ?>
            <tr> 
                <td><?= $player['id'] ?></td>
                <td><?= $player['name'] ?></td>
                <td><?= $player['games'] ?></td>
                <td><?= $player['goals'] ?></td>
            </t>
        <?php endforeach; ?>
    </tbody>
</table>    
<div class="jumbotron mt-3"> 
    <h5>TOP SCORERE:  <span class="text-success"> <?= Scrapping::topScorer() ?> </span> </h5>
</div>
        </div>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>
</body>
</html>
