<?php
// Open the file
$myfile = fopen("weight_Details.json", "r") or die("Unable to open file!");

// Read the file contents
$data = fread($myfile, filesize("weight_Details.json"));

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
    // Check if 'symbols' key exists and it's an array
    if (isset($item['description'])) {
        $desc = (float)$item['description'] / 10;
        
        $weightdetails[] = array(
            "grossWeight" => (string)$desc,
            "tareWeight" => "16.40", 
            "reduceWeight" => "0.0", 
            "netWeight" => (string)($desc - 16.40), 
            "birdsPerCages" => "12", 
            "numberOfBirds" => "24", 
            "numberOfCages" => "2", 
            "grade" => "S", 
            "sex" => "Mixed", 
            "houseNumber" => "1", 
            "groupNumber" => "1", 
            "remark" => ""
        );
    }
}

echo json_encode($weightdetails);
?>
