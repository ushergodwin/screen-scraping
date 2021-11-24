<?php
include 'load_models.php';
$user = new User_Model();
$employees = $user->fetch_data();
$dataPoints = $user->get_graph();
$simpleStat = $user->get_statistics();
$salaryDistribution = $user->salary_distribution();