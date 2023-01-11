<?php
require 'connection.php';

$message = $errorMessage =
$countryIDUpdate = $countryIDDelete =
$currentCountryName = $currentRegionID =
$newCountryName = $newRegionID =
$singleRow = $tableRows = $paginationBtn = '';
$showUpdateForm = $showDeleteForm = $showRecords = 'none';

if (isset($_POST)) {
    // Insert record
    if (isset($_POST['country_name']) && isset($_POST['region_id'])) {
        $countryName = mysqli_escape_string($conn, $_POST['country_name']);
        $regionID = mysqli_escape_string($conn, $_POST['region_id']);

        $selectBoth = mysqli_query($conn, 'SELECT country_name, region_id FROM country WHERE country_name = "' . $countryName . '" AND region_id = "' . $regionID . '"');
        $selectCountryName = mysqli_query($conn, 'SELECT country_name FROM country WHERE country_name = "' . $countryName . '"');
        $selectRegionID = mysqli_query($conn, 'SELECT region_id FROM country WHERE region_id = "' . $regionID . '"');

        if (mysqli_num_rows($selectBoth))
            $errorMessage = '<div class="alert alert-danger text-center" id="errorMsg" >Country Name and Region ID already exist.</div>';
        else if (mysqli_num_rows($selectCountryName))
            $errorMessage = '<div class="alert alert-danger text-center" id="errorMsg" >Country Name already exist.</div>';
        else if (mysqli_num_rows($selectRegionID))
            $errorMessage = '<div class="alert alert-danger text-center" id="errorMsg" >Region ID already exist.</div>';
        else {
            $stmt = $conn->prepare("INSERT INTO country (country_name, region_id) VALUES (?, ?)");
            $stmt->bind_param("sd", $countryName, $regionID);
            $stmt->execute();
            $stmt->close();
            $message = '<div class="alert alert-success mt-5 text-center" id="successMsg">Successfuly inserted a record.</div>';
        }

        mysqli_free_result($selectBoth);
        mysqli_free_result($selectCountryName);
        mysqli_free_result($selectRegionID);
    }

    // Search country id when updating and deleting
    if (isset($_POST['country_id'])) {
        $countryID = mysqli_escape_string($conn, $_POST['country_id']);
        $selectCountryID = mysqli_query($conn, 'SELECT country_id FROM country WHERE country_id = "' . $countryID . '"');

        if (!mysqli_num_rows($selectCountryID))
            $errorMessage = '<div class="alert alert-danger text-center" id="errorMsg" >Country ID does not exist.</div>';
        else {
            if (isset($_POST['updateForm'])) { // Show update form
                $result = mysqli_query($conn, 'SELECT country_name, region_id FROM country WHERE country_id = "' . $countryID . '"');
                $record = mysqli_fetch_assoc($result);

                $countryIDUpdate = $countryID;
                $newCountryName = $currentCountryName = $record['country_name'];
                $newRegionID = $currentRegionID = $record['region_id'];
                $showUpdateForm = 'block';
            } else {
                if (isset($_POST['deleteForm'])) { // Show delete form
                    $result = mysqli_query($conn, 'SELECT * FROM country WHERE country_id = "' . $countryID . '"');
                    $row = mysqli_fetch_assoc($result);

                    $singleRow = '<tr>
                                    <td>' . $row["country_id"] . '</td>
                                    <td>' . $row["country_name"] . '</td>
                                    <td>' . $row["region_id"] . '</td>
                                  </tr>';

                    $countryIDDelete = $countryID;
                    $showDeleteForm = 'block';
                }
            }

            mysqli_free_result($result);
        }

        mysqli_free_result($selectCountryID);
    }

    // Updating record
    if (isset($_POST['new_country_name']) && isset($_POST['new_region_id'])) {
        $update = true;
        $newCountryName = mysqli_escape_string($conn, $_POST['new_country_name']);
        $newRegionID = mysqli_escape_string($conn, $_POST['new_region_id']);

        if ($_POST['new_country_name'] != $_POST['current_country_name'] && $_POST['new_region_id'] != $_POST['current_region_id']) {
            $selectBoth = mysqli_query($conn, 'SELECT country_name, region_id FROM country WHERE country_name = "' . $newCountryName . '" AND region_id = "' . $newRegionID . '"');

            if (mysqli_num_rows($selectBoth)) {
                $errorMessage = '<div class="alert alert-danger text-center" id="errorMsg" >Country Name and Region ID already exist.</div>';
                $update = false;
            }

            mysqli_free_result($selectBoth);
        } else if ($_POST['new_country_name'] != $_POST['current_country_name']) {
            $selectCountryName = mysqli_query($conn, 'SELECT country_name FROM country WHERE country_name = "' . $newCountryName . '"');

            if (mysqli_num_rows($selectCountryName)) {
                $errorMessage = '<div class="alert alert-danger text-center" id="errorMsg" >Country Name already exist.</div>';
                $update = false;
            }

            mysqli_free_result($selectCountryName);
        } else if ($_POST['new_region_id'] != $_POST['current_region_id']) {
            $selectRegionID = mysqli_query($conn, 'SELECT region_id FROM country WHERE region_id = "' . $newRegionID . '"');

            if (mysqli_num_rows($selectRegionID)) {
                $errorMessage = '<div class="alert alert-danger text-center" id="errorMsg" >Region ID already exist.</div>';
                $update = false;
            }

            mysqli_free_result($selectRegionID);
        }

        if ($update) {
            $stmt = $conn->prepare("UPDATE country SET country_name = ? , region_id = ? WHERE country_id = ?");
            $stmt->bind_param("sdi", $newCountryName, $newRegionID, $newIDUpdate);
            $newIDUpdate = mysqli_escape_string($conn, $_POST['country_id_update']);
            $stmt->execute();
            $stmt->close();
            $message = '<div class="alert alert-success mt-5 text-center" id="successMsg">Successfuly updated a record.</div>';
        }
    }

    // Deleting record
    if (isset($_POST['country_id_delete'])) {
        mysqli_query($conn, 'DELETE FROM country WHERE country_id = "' . mysqli_escape_string($conn, $_POST['country_id_delete']) . '"');
        $message = '<div class="alert alert-success mt-5 text-center" id="successMsg">Successfuly deleted a record.</div>';
    }

    // Viewing records
    $result = mysqli_query($conn, "SELECT * FROM country ORDER BY country_id");
    $allRecordCount = mysqli_num_rows($result);
    $limit = 5;
    $totalButton = ceil($allRecordCount / $limit);

    if (mysqli_num_rows($result) > 5) 
        for ($btn = 1; $btn <= $totalButton; $btn++)
            $paginationBtn .= '<button class="btn btn-primary" style="margin: 4 3px 0 4px" onclick="ShowRow(' . "'group" . $btn . "'" . ')">' . $btn . '</button>';

    if (mysqli_num_rows($result)) {
        $counter = 1;
        $tableNumber = 1;

        while ($row = mysqli_fetch_assoc($result)) {
            $tableRows .= '<tr class="group' . $tableNumber . '">
                               <td>' . $row["country_id"] . '</td>
                               <td>' . $row["country_name"] . '</td>
                               <td>' . $row["region_id"] . '</td>
                           </tr>';

            $counter++;

            if ($counter > 5) {
                $tableNumber++;
                $counter = 1;
            }
        }
    } else
        $tableRows = "";

    mysqli_free_result($result);

    $conn->close();
    unset($_POST);
}
?>

<head>
    <title>CRUD Application</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
</head>

<body class="user-select-none">
    <!-- Menu div that consist of three buttons to show operation forms -->
    <div class="container py-5 text-center">
        <div class="row d-flex justify-content-center">
            <div class="col-8 col-md-7 col-lg-7 col-xl-6">
                <div class="card" style="border-radius: 1rem;">
                    <div class="card-body">
                        <h1>Select Operation</h1>
                        <hr>
                        <button class="btn btn-success mb-2" id="showInsertForm">Insert Record</button><br>
                        <button class="btn btn-warning mb-2" id="showUpdateForm">Update Record</button><br>
                        <button class="btn btn-danger mb-2" id="showDeleteForm">Delete Record</button><br>
                        <button class="btn btn-primary" id="showViewForm">View Records</button>
                    </div>
                </div>
                <?php echo $message; // Display success message ?>
            </div>
        </div>
    </div>
    <div class="container pb-5 text-left">
        <div class="row d-flex justify-content-center">
            <div class="col-8 col-md-7 col-lg-7 col-xl-6">
                <!-- Insert form -->
                <div class="card" id="insertDiv" style="border-radius: 1rem; display: none">
                    <div class="card-body">
                        <h1 class="text-center">Insert Record</h1>
                        <form class="form-control" id="insertForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                            <label>Input Country Name</label>
                            <input class="form-control mt-2" type="text" name="country_name" required>
                            <label class="mt-2">Input Region ID</label>
                            <input class="form-control mt-2" type="text" name="region_id" placeholder="ex. 531624.8719" maxlength="11" required>
                            <button class="btn btn-primary mt-2" onclick="CheckUserInput(0)">Insert</button>
                        </form>
                    </div>
                </div>
                <!-- Form for inputing the country id before showing the update form -->
                <div class="card" id="updateInputIDDiv" style="border-radius: 1rem; display: none">
                    <div class="card-body">
                        <h1 class="text-center">Update Record</h1>
                        <form class="form-control" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                            <label>Input Country ID</label>
                            <input class="form-control mt-2" type="text" name="country_id" required>
                            <input class="form-control mt-2" type="text" name="updateForm" hidden>
                            <button class="btn btn-primary mt-2" onclick="CheckUserInput(1)">Search</button>
                        </form>
                    </div>
                </div>
                <!-- Update form -->
                <div class="card" id="updateDiv" style="border-radius: 1rem; display: <?php echo $showUpdateForm ?>">
                    <div class="card-body">
                        <h1 class="text-center">Update Record</h1>
                        <form class="form-control" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                            <input class="form-control" type="text" name="country_id_update" hidden value="<?php echo $countryIDUpdate; ?>">
                            <input class="form-control" type="text" name="current_country_name" hidden value="<?php echo $currentCountryName; ?>">
                            <input class="form-control" type="text" name="current_region_id" hidden value="<?php echo $currentRegionID; ?>">
                            <label>Input New Country Name</label>
                            <input class="form-control mt-2" type="text" name="new_country_name" required value="<?php echo $newCountryName; ?>">
                            <label class="mt-2">Input New Region ID</label>
                            <input class="form-control mt-2" type="text" name="new_region_id" placeholder="ex. 531624.8719" maxlength="11" required value="<?php echo $newRegionID; ?>">
                            <button class="btn btn-primary mt-2" onclick="CheckUserInput(2)">Update</button>
                        </form>
                    </div>
                </div>
                <!-- Form for inputing the country id before showing the delete form -->
                <div class="card" id="inputDeleteDiv" style="border-radius: 1rem; display: none">
                    <div class="card-body">
                        <h1 class="text-center">Delete Record</h1>
                        <form class="form-control" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                            <label>Input Country ID</label>
                            <input class="form-control mt-2" type="text" name="country_id" required>
                            <input class="form-control mt-2" type="text" name="deleteForm" hidden>
                            <button class="btn btn-primary mt-2" onclick="CheckUserInput(3)">Search</button>
                        </form>
                    </div>
                </div>
                <!-- Delete form -->
                <div class="card" id="deleteDiv" style="border-radius: 1rem; display: <?php echo $showDeleteForm; ?>">
                    <div class="card-body">
                        <h1 class="text-center">Delete Record</h1>
                        <div class="table-responsive-sm">
                            <table class="table table-hover">
                                <thead class="table table-primary">
                                    <tr>
                                        <th scope="col">Country ID</th>
                                        <th scope="col">Country Name</th>
                                        <th scope="col">Region ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php echo $singleRow; ?>
                                </tbody>
                            </table>
                        </div>
                        <div>
                            <form class="form-control" id="deleteFinalForm" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST" hidden>
                                <input class="form-control" type="text" name="country_id_delete" value="<?php echo $countryIDDelete; ?>">
                            </form>
                            <button class="btn btn-danger mt-2" id="deleteBtn" onclick="document.forms[4].submit();">Delete</button>
                            <button class="btn btn-primary mt-2" style="float: right;" onclick="$('#deleteDiv').hide();">Cancel</button>
                        </div>
                    </div>
                </div>
                <!-- Viewing form that display the table -->
                <div class="card" id="viewDiv" style="border-radius: 1rem; display: <?php echo $showRecords; ?>">
                    <div class="card-body">
                        <h1 class="text-center">View Records</h1>
                        <?php
                        if ($tableRows != '')
                            echo '<div class="table-responsive-sm" id="dataTable">
                                    <table class="table table-hover">
                                        <thead class="table table-primary">
                                            <tr class="tableHead">
                                                <th scope="col">Country ID</th>
                                                <th scope="col">Country Name</th>
                                                <th scope="col">Region ID</th>
                                            </tr>
                                        </thead>
                                        <tbody style="user-select: text">
                                        ' . $tableRows . '
                                        </tbody>
                                    </table>
                                  </div>' . $paginationBtn;
                        else
                            echo '<div class="alert alert-danger text-center">No records exist.</div>';
                        ?>
                    </div>
                </div>
                <?php echo $errorMessage; // Display error message ?>
            </div>
        </div>
    </div>
    <script>
        // Handles the showing of the form using jquery
        const buttons = ['#showInsertForm', '#showUpdateForm', '#showDeleteForm', '#showViewForm'];
        const operationDivs = ["#insertDiv", "#updateInputIDDiv", "#inputDeleteDiv", "#viewDiv", "#updateDiv", "#deleteDiv"];

        for (let indexBtn in buttons)
            $(buttons[indexBtn]).on('click', () => {
                $(operationDivs[indexBtn]).show();

                for (let indexDiv in operationDivs)
                    if (operationDivs[indexDiv] != operationDivs[indexBtn])
                        $(operationDivs[indexDiv]).hide();

                $('input').val('');
                $('#errorMsg').hide();
                $('#successMsg').hide();

                if (buttons[indexBtn] == '#showUpdateForm')
                    $('input[name=updateForm]').val('set');

                if (buttons[indexBtn] == '#showDeleteForm')
                    $('input[name=deleteForm]').val('set');

                if (buttons[indexBtn] == '#showViewForm')
                    ShowRow("group1");
            });

        // Checking user input before submiting
        function CheckUserInput(formIndex) {
            if (formIndex == 0 || formIndex == 2) {
                let countryName, regionID;

                if (formIndex == 0) {
                    countryName = document.forms[formIndex].elements['country_name'];
                    regionID = document.forms[formIndex].elements['region_id'];
                } else {
                    countryName = document.forms[formIndex].elements['new_country_name'];
                    regionID = document.forms[formIndex].elements['new_region_id'];
                }

                if (!/^[A-Za-z\s]+$/.test(countryName.value.trim())) {
                    countryName.setCustomValidity("Please input a valid country name.");
                    return;
                } else
                    countryName.setCustomValidity("");

                if (!/^\d{6}(?=.{5}$)\d*\.\d*\d{4}$/.test(regionID.value.trim())) {
                    regionID.setCustomValidity("Please input a valid region id. consist of 6 digits only before decimal and 4 digits only after decimal.");
                    return;
                } else
                    regionID.setCustomValidity("");
            } else {
                let countryID = document.forms[formIndex].elements['country_id'];

                if (!/^\d+$/.test(countryID.value.trim())) {
                    countryID.setCustomValidity("Please input a whole numbers only.");
                    return;
                } else
                    countryID.setCustomValidity("");
            }

            document.forms[formIndex].submit();
        }

        // Handles the pagination event
        function ShowRow(tableNumber) {
            let rows = document.getElementsByTagName('tr');

            for (let row of rows) {
                if (row.className != tableNumber && row.className != "tableHead")
                    $(row).hide();
                else
                    $(row).show();
            }
        }
    </script>
</body>