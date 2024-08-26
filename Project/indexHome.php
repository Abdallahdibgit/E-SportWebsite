<?php
require_once 'Connection.php';

try {
    $tournamentsQuery = $pdo->query("SELECT * FROM tournaments ORDER BY start_date DESC");
    $tournaments = $tournamentsQuery->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error fetching tournaments: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Your Website Title</title>
    <link rel="stylesheet" href="styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .footer-section.social-media {
            display: flex;
            gap: 15px;
        }

        .social-icon {
            display: inline-block;
            text-decoration: none;
            color: #000;
            font-size: 24px;
            padding: 0 10px;
        }

        .social-icon:hover {
            color: #007bff;
        }


        .tournament-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .tournament-item h3, .tournament-item p {
            color: black;
        }

        .tournament-item {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body onload="slider()">
    <main>
        <section id="home">
            <div class="banner">
                <div class="slider">
                    <img src="images/img1.jpg" id="slideImg" alt="Slider Image" />
                    <div class="qt">
                        <p>Road To Your Mythic!</p>
                    </div>
                    <div class="overlay">
                        <div class="logo">
                            <img src="images/logo.png" alt="Logo" width="150" height="80" />
                        </div>
                        <button class="hamburger" aria-label="Toggle navigation">
                            <span class="bar"></span>
                            <span class="bar"></span>
                            <span class="bar"></span>
                        </button>
                        <nav class="main-nav">
                            <ul>
                                <li><a href="#home">Home</a></li>
                                <li><a href="#tournament">Tournaments</a></li>
                                <li><a href="#contact">Contact Us</a></li>
                                <li><a href="login.php" id="loginLink">Login</a></li>
                            </ul>
                        </nav>
                        <div class="nav-menu">
                            <ul>
                                <li><a href="#home">Home</a></li>
                                <li><a href="#tournament">Tournaments</a></li>
                                <li><a href="#contact">Contact Us</a></li>
                                <li><a href="login.php" id="loginLink">Login</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="section tournament" aria-labelledby="tournament-label" id="tournament">
            <div class="container">
                <p class="section-subtitle" id="tournament-label">Upcoming Matches</p>
                <h2 class="h2 section-title">Epic Battles and Ultimate Victory Tournament</h2>
                <p class="section-text">Get ready for an exhilarating showcase of skill and strategy!</p>
                <div id="tournament-container" class="tournament-wrapper">
                    <?php foreach ($tournaments as $tournament): ?>
                        <div class="tournament-item">
                            <img src="uploads/<?php echo htmlspecialchars($tournament['image']); ?>" alt="<?php echo htmlspecialchars($tournament['tournament_name']); ?>" class="tournament-image">
                            <h3><?php echo htmlspecialchars($tournament['tournament_name']); ?></h3>
                            <p>Starts on: <?php echo htmlspecialchars(date('F j, Y', strtotime($tournament['start_date']))); ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        
        <section id="contact" class="contact_section">
            <div class="contact_inner">
                <div class="footer-top">
                    <div class="footer-section contact-info">
                        <h3>Contact Us</h3>
                        <p>Email: Abdallah.dib313@gmail.com</p>
                        <p>Phone: +961 76368153</p>
                        <p>Address: Beirut, Lebanon</p>
                    </div>
                    <div class="footer-section social-media">
                        <h3>Follow Us</h3>
                        <a href="#" class="social-icon" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                    <div class="footer-section quick-links">
                        <h3>Quick Links</h3>
                        <ul>
                            <li><a href="#">Home</a></li>
                            <li><a href="#">About Us</a></li>
                            <li><a href="#">Services</a></li>
                            <li><a href="#">Contact</a></li>
                        </ul>
                    </div>
                </div>
                <div class="footer-bottom">
                    <p>&copy; 2024 Deeb Company. All rights reserved.</p>
                </div>
            </div>
        </section>

        <div id="newsletter" class="newsletter">
            <h2>Subscribe to Our Newsletter</h2>
            <form>
                <input type="email" placeholder="Enter your email" required />
                <button type="submit">Subscribe</button>
            </form>
        </div>
    </main>

    <script>
        const navToggle = document.querySelector(".hamburger");
        const navMenu = document.querySelector(".nav-menu");
        navToggle.addEventListener("click", () => {
            navMenu.classList.toggle("active");
        });

        var slideImg = document.getElementById("slideImg");
        var images = ["images/img1.png", "images/img2.png", "images/img3.png", "images/img4.png", "images/img5.png"];
        var i = 0;
        function slider() {
            if (i >= images.length) {
                i = 0;
            }
            slideImg.src = images[i];
            i++;
            setTimeout(slider, 3000);
        }

    </script>
</body>
</html>
