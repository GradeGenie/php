<!doctype html>
<html>
<head>
<title>Grady | Home</title>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
<style>
/* General Styles */
body {
    margin: 0; 
    font-family: Arial, sans-serif;
}

/* Header Styles */
header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 20px;
    background-color: #f8f9fa; /* Adjust the background color as needed */
    border-bottom: 1px solid #dee2e6; /* Optional: Add a border for separation */
}

#logo {
    font-size: 24px;
    font-weight: bold;
}

#right {
    font-size: 16px;
}

/* Nav Styles */
nav {
    width: 200px;
    background-color: #343a40;
    height: 100vh;
    position: fixed;
    top: 48px;
    left: 0;
    display: flex;
    flex-direction: column;
    padding-top: 20px;
}

#navItems {
    display: flex;
    flex-direction: column;
    align-items: flex-start; /* Align items to the start */
}

.navItem {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    width: 100%;
    color: #fff;
    text-decoration: none;
    cursor: pointer;
}

.navItem:hover {
    background-color: #495057; /* Adjust the hover color as needed */
}

.navIcon {
    width: 20px;
    height: 20px;
    background-color: #adb5bd; /* Placeholder for icon, adjust as needed */
    margin-right: 10px;
}

.navLabel {
    font-size: 16px;
}
.button {
    display: inline-block;
    padding: 10px 20px;
    background-color: #007bff;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    cursor: pointer;
}

.button:hover {
    background-color: #0056b3;
}

.button:active {
    background-color: #003d80;
}
div#mainContent {
    position: absolute;
    left: 230px;
    top: 70px;
    right: 30px;
}
.createRubricButton {
    display: inline-block;
    padding: 12px 24px;
    background-color: #28a745;
    color: #fff;
    text-decoration: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    margin-bottom: 15px;
    border: none;
}

.createRubricButton:hover {
    background-color: #218838;
}

.createRubricButton:active {
    background-color: #1e7e34;
}

</style>
</head>

<body>
<header>
<div id="logo">Gradie</div>
<div id="right">Serene</div>
</header>
<nav>
<div id="navItems">
    <a class="navItem">
        <div class="navIcon"></div>
        <div class="navLabel">Home</div>
    </a>
    <a class="navItem">
        <div class="navIcon"></div>
        <div class="navLabel">Rubrics</div>
    </a>
    <a class="navItem">
        <div class="navIcon"></div>
        <div class="navLabel">Grading Assistant</div>
    </a>
</div>
</nav>
<div id="mainContent">
    <h1>Rubrics</h1>

<a href="rubric_create.php"><button class="createRubricButton">Generate Rubric</button></a>
<a href="rubric_upload.php"><button class="createRubricButton">Upload Rubric</button></a>

<table id="rubricTable" class="dataTable">
    <thead>
        <tr>
            <th>Rubric Name</th>
            <th>Subject Area</th>
            <th>Assignment Type</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
<tr>
    <td>Rubric 1</td>
    <td>Math</td>
    <td>Homework</td>
    <td>
        <button>View</button>
        <button>Delete</button>
    </td>
</tr>
        
    </tbody>
</table>
</div>

</body>
<script>
$(document).ready(function() {
    $('#rubricTable').DataTable();
});
</script>


</script>
</head>
</html>