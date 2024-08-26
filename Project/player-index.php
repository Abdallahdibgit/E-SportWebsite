<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Add your CSS styling here */
        .content {
            margin-left: 250px;
            padding: 20px;
        }
        .sidebar {
            position: fixed;
            width: 250px;
            height: 100%;
            background-color: #333;
            color: #fff;
            overflow: auto;
            transition: all 0.3s;
        }
        .sidebar .logo {
            padding: 15px;
            text-align: center;
            background-color: #222;
        }
        .sidebar ul.menu {
            list-style-type: none;
            padding: 0;
        }
        .sidebar ul.menu li {
            padding: 10px;
        }
        .sidebar ul.menu li a {
            color: #fff;
            text-decoration: none;
            display: block;
        }
        .sidebar ul.menu li a:hover {
            background-color: #575757;
        }
        #toggle-sidebar {
            background: none;
            border: none;
            color: red;
            font-size: 18px;
            cursor: pointer;
        }
        .main-content {
            padding: 20px;
        }
        .hide {
            display: none;
        }
    </style>
</head>
<body>

<div class="sidebar" id="sidebar">
    <div class="logo">
        <h2>Player Panel</h2>
    </div>
    <ul class="menu">
        <li><a href="profile.php" id="profile-link"><i class="fa fa-user-circle"></i> Profile</a></li>
        <li><a href="join_team.php" id="join-team-link"><i class="fas fa-users"></i> Team</a></li>
        <li><a href="join_tournament.php" id="join-tournament-link"><i class="fas fa-trophy"></i> Tournaments</a></li>
        <li><a href="view_news.php" id="viewN-link"><i class="fa-solid fa-laptop-code"></i> News</a></li>
        <li><a href="feedback.php" id="feedback-link"><i class="fa-solid fa-envelope"></i> Feedback</a></li>
        <div class="navbar"></div>
        <form action="login.php" method="post" style="display: inline;">
            <div class="button-container">
            <button type="submit" class="logout-button">Logout</button>
            </div>
        </form>
    </ul>
</div>

<div class="content">
    <div class="header">
        <button id="toggle-sidebar"><i class="fas fa-bars"></i></button>
        <h1>Dashboard</h1>
    </div>
    <div class="main-content" id="main-content">
        <!-- Dynamic content will be loaded here -->
    </div>
</div>

<script>
document.getElementById('toggle-sidebar').addEventListener('click', function() {
    var sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('hide');
});

document.getElementById('profile-link').addEventListener('click', function() {
    loadPage('profile.php');
});

document.getElementById('join-tournament-link').addEventListener('click', function() {
    loadPage('join_tournament.php');
});

document.getElementById('join-team-link').addEventListener('click', function() {
    loadPage('join_teams.php');
});

document.getElementById('viewN-link').addEventListener('click', function() {
    loadPage('view_news.php');
});

document.getElementById('feedback-link').addEventListener('click', function() {
    loadPage('feedback.php');
});


function loadPage(page) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', page, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4 && xhr.status == 200) {
            document.getElementById('main-content').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}
</script>

</body>
</html>
