<?php
// Open the file
$myfile = fopen("weight.json", "r") or die("Unable to open file!");

// Read the file contents
$data = fread($myfile, filesize("weight.json"));

// Close the file
fclose($myfile);

// Decode the JSON data
$decoded_data = json_decode($data, true);

// Check if decoding was successful
if ($decoded_data === null) {
    die("Error decoding JSON");
}

$weightdetails = array();

foreach ($decoded_data as $item) {
    $weightdetails[] = array(
        "grossWeight" => (string)$item['grossWeight'],
        "tareWeight" => (string)$item['tareWeight'], 
        "reduceWeight" => "0.0", 
        "netWeight" => (string)((float)$item['grossWeight'] - (float)$item['tareWeight']), 
        "birdsPerCages" => (string)$item['birdsPerCages'], 
        "numberOfBirds" => (string)$item['numberOfBirds'], 
        "numberOfCages" => (string)$item['numberOfCages'], 
        "grade" => (string)$item['grade'], 
        "sex" => (string)$item['sex'], 
        "houseNumber" => (string)$item['houseNumber'], 
        "groupNumber" => (string)$item['groupNumber'], 
        "remark" => ""
    );
}

echo json_encode($weightdetails);
?>
