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
        <h2>Admin Panel</h2>
    </div>
    <ul class="menu">
        <li><a href="admin_profile.php" id="adminP-link"><i class="fa-solid fa-bath"></i> Profile</a></li>
        <li><a href="users.php" id="users-link"><i class="fa fa-user-circle"></i> Users</a></li>
        <li><a href="teams.php" id="teams-link"><i class="fas fa-users"></i> Teams</a></li>
        <li><a href="tournaments.php" id="tournaments-link"><i class="fas fa-trophy"></i> Tournaments</a></li>
        <li><a href="manage_match.php" id="manage-match-link"><i class="fas fa-calendar-alt"></i> Schedule Match</a></li>
        <li><a href="manage_news.php" id="manageN-link"><i class="fa-solid fa-laptop-code"></i> News</a></li>
        <li><a href="list_feedback.php" id="listF-link"><i class="fab fa-weixin"></i> Feedback</a></li>
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
    </div>
</div>

<script>
document.getElementById('toggle-sidebar').addEventListener('click', function() {
    var sidebar = document.getElementById('sidebar');
    sidebar.classList.toggle('hide');
});

document.getElementById('users-link').addEventListener('click', function() {
    loadPage('users.php');
});

document.getElementById('adminP-link').addEventListener('click', function() {
    loadPage('admin_profile.php');
});

document.getElementById('tournaments-link').addEventListener('click', function() {
    loadPage('tournaments.php');
});

document.getElementById('manage-match-link').addEventListener('click', function() {
    loadPage('manage_match.php');
});

document.getElementById('teams-link').addEventListener('click', function() {
    loadPage('teams.php');
});

document.getElementById('manageN-link').addEventListener('click', function() {
    loadPage('manage_news.php');
});

document.getElementById('listF-link').addEventListener('click', function() {
    loadPage('list_feedback.php');
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
