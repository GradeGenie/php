<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Rubrics | GradeGenie</title>
    <link href="https://fonts.googleapis.com/css2?family=Albert+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="style.css?v=<?php echo rand(111111, 999999); ?>" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <?php include 'header.php'; ?>
    <?php include 'menu.php'; ?>
</head>
<style>
/* Include your existing styles */

.classCard {
            flex: 1 1 250px;
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            margin: 15px;
            box-shadow: 0 0 10px #e2e2e2;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

.rightCTA {
    background: #19A37E;
    width: 120px;
    text-align: center;
    padding: 10px 5px;
    color: #fff;
    font-weight: bold;
    border-radius: 5px;
    cursor: pointer;
    float: right;
    margin-top: -45px;
}
.formCTA {
    background: #19A37E;
    text-align: center;
    padding: 10px 5px;
    color: #fff;
    font-weight: bold;
    border-radius: 5px;
    cursor: pointer;
    font-size: 16px;
    display: inline-block;
    margin: 10px 5px;
}
div#classCardParent {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
    margin-left: -10px;
}
p.headingSubtitle {
    margin-top: -10px;
    color: #777;
}
#overlay{
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    display: none;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}
.modal {
    background-color: #fefefe;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 0 10px #5d5d5d;
    width: 400px;
    display: none;
    z-index: 20000;
    position: fixed;
    left: calc(50% - 200px);
    top: 30%;
}
.modalInput{
    margin-top: 5px;
    width: 100%;
}
.hidden {
    display: none;
}
.closeModal{
    float: right;
    cursor: pointer;
}
span.classCard_level, span.classCard_subject {
    color: #CCC;
    font-size: 14px;
}
span.classCard_name {
    font-weight: bold;
}
.cardButton {
    background-color: #28a745;
    color: white;
    padding: 5px 10px;
    text-align: center;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    margin-top: 10px;
}
.topLink{
    color: #28a745;
    text-decoration: none;
    display: block;
}
.secondaryHeading{
    margin-top: 40px;
}
.editBtn {
    color: #898989;
    font-size: 13px;
    background: #d4d4d4;
    padding: 5px 7px;
    border-radius: 21px;
    vertical-align: 3px;
    margin-left: 5px;
    cursor: pointer;
}
.pageHeading{
    display: inline-block;
}
.buttonLink {
    text-decoration: none;
}
</style>
<body>
    <div id="mainContent">
        <h2 class="pageHeading">My Rubrics</h2>
        <p class="headingSubtitle">Here, you'll find all your created and uploaded rubrics</p>

        <div class="rightCTA" onclick="location.href='create_rubric.php'" style="width:170px;margin-top: -45px;">+ Create Rubric</div>
        <div class="rightCTA" onclick="location.href='upload_rubric.php'" style="width:170px;margin-top: -45px; margin-right: 185px;">+ Upload Rubric</div>
        <div id="classCardParent">
            <!-- Rubrics will be dynamically loaded here as cards -->
        </div>
    </div>

    <script>
$(document).ready(function() {
    fetchRubrics();
});

function fetchRubrics() {
    $.ajax({
        type: 'GET',
        url: 'api/fetch_rubrics.php',
        success: function(response) {
            if (response.success) {
                $('#classCardParent').empty(); // Clear existing rubrics
                
                response.rubrics.forEach(function(rubric) {
                    var rubricCard = '<div class="classCard">' +
                        '<span class="classCard_name">' + rubric.title + '</span><br>' + // Use title instead of name
                        '<span class="classCard_subject">' + rubric.subject + '</span><br>' +
                        '<span class="classCard_level">' + rubric.level + '</span><br>' +
                        '<p>' + rubric.description + '</p>' +
                        '<button class="cardButton" onclick="viewRubric(' + rubric.rid + ')">View</button>' +
                        '</div>';
                    $('#classCardParent').prepend(rubricCard);
                });
            } else {
                // alert('Failed to load rubrics: ' + response.message);
            }
        },
        error: function() {
            alert('An error occurred while fetching the rubrics.');
        }
    });
}

function viewRubric(rubricId) {
    // Redirect to the rubric view page
    window.location.href = 'view_rubric.php?id=' + rubricId;
}
</script>

</body>
</html>
