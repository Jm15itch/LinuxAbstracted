<?php
require 'PackageLUT.php';

http_response_code(200);


// The package manager to translate to
$ManagerTarget = $_GET["PackageManager"];

switch ($ManagerTarget) {
        case "pacman":
        case "apt":
        case "apt-get":
            break;
        default: // Exit if not a valid package manager
            http_response_code(501);
            echo "Error: The target package manager that you've provided, ";
            echo htmlspecialchars($ManagerTarget);
            echo ", Isn't available for translation.";
            exit;
};

// The packages we want to translate
$InputPackages = $_GET["Packages"];

$ManagerInput = "NULL";


if (empty($InputPackages)) {
    http_response_code(400);
    echo "Error: No packages were provided!";
    exit;
}


// Package detection, we need to figure out what the input is...
if (str_contains(strtolower($InputPackages), "apt") || str_contains(strtolower($InputPackages), "apt-get")) {
    $ManagerInput = "apt";
}
else if (str_contains(strtolower($InputPackages), "pacman") ) {
    $ManagerInput = "pacman";
}


// If no valid manager was found then ignore this request
if ($ManagerInput == "NULL") {
    exit;
}


// Split the package input into tokens to iterate over
$InputPackages = preg_split("/[\s,]+/", $InputPackages);


// This is the output string that we will use as a foundation to construct the output
$WorkingPackages = "";
$FailedPackages = "";



// Lets translate now!
foreach ($InputPackages as $Tokens) {

    if (empty($Tokens)) {
        continue; // if token invalid = skip
    }

    // filter stuff that we don't need to check
    switch (strtolower($Tokens)) {
        case "sudo":
        case "install":
        case "pacman":
        case "apt":
        case "apt-get":
        case "-S":
        case "-Sy":
        case "-Syu":
            continue 2; // skip this token
    };


    foreach ($PackageLUT as $PackageToCheck) { // check the lookup table for the token

        if (empty($PackageToCheck[$ManagerTarget][0])) { // if the manager target has a value in this row in the LUT, then..
            $FailedPackages += $PackageToCheck[$ManagerTarget][0] + "<br>";
            continue;
        }

        if (!in_array($Tokens, $PackageToCheck[$ManagerInput])) { // if the package manager input isn't in the row of the LUT then skip.
            continue;
        }

        switch ($ManagerTarget) { // set formatting for desired package manager
            case "apt":
            case "apt-get":
                $WorkingPackages = $WorkingPackages . "sudo apt install " . $PackageToCheck[$ManagerTarget][0] . "<br>";
                break;
            case "pacman":
                $WorkingPackages = $WorkingPackages . "sudo pacman -S " . $PackageToCheck[$ManagerTarget][0] . "<br>";
                break;
        }

    }

}


if (isset($_GET["SIMPLE"])) {
    echo $WorkingPackages;
} else {
    echo $WorkingPackages;
    echo "<br><br><br>";
    echo $FailedPackages;
}


?>
