<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Book Our Bus</title>
    <link rel="stylesheet" href="bush.css">
</head>
<body>

    <!-- Main Interface -->
  
        <header>
            <div class="logo">
                <img src="gub.jpg" style="border-radius: 5px;" alt="Company Logo">
                <span class="brand-name">Green University of Bangladesh</span>
            </div>
            <nav>
                <ul>
                    <li><a href="https://green.edu.bd/">GUB official site</a></li>
                    <li><a href="#schedule-bus-section">Schedule Your Bus</a></li>
                    <li><a href="#services">Services</a></li>
                    <li><a href="#buses">FAQ</a></li>
                    <li><a href="#pages">Complaint</a></li>
                </ul>
            </nav>
            <div class="contact-info">
                <span>Working Hours: Mon-Fri 6.30am-5pm</span>
                <span>Call Center: 01771257194</span>
            </div>
        </header>
        <main>
            <section class="hero">
                <h1 style="font-size: 50px;">Book Our Bus</h1>
                <p style="font-size: 40px;">Escorting you hassle free is our duty.</p>
                <a href="#schedule-bus-section" class="cta-button">Book your slot</a>
            </section>
    
            <div class="image-stack">
                <img src="busgoodview.jpg" alt="Bus Image 1" class="image active">
                <img src="transport2.jpg" alt="Bus Image 2" class="image">
            </div>

        </main>
       

        <footer>
            <div class="footer-container">
                <div class="find-us">
                    <h3>Find Us</h3>
                    <p>Purbachal American City, Kanchan, Rupganj, Narayanganj-1461, Dhaka, Bangladesh</p>
                    <p>+880 9614482482</p>
                    <p>01324713503, 01324713502, 01324713504, 01324713505, 01324713506, 01324713507, 01324713508</p>
                    <p><a href="mailto:admission@green.edu.bd">admission@green.edu.bd</a></p>
                </div>
                <div class="departmental-sites">
                    <h3>Departmental Sites</h3>
                    <ul>
                        
                        <li>Computer Science And Engineering</li>
                        <li>Software Engineering</li>
                        <li>Electrical And Electronic Engineering</li>
                        <li>English</li>
                        <li>Journalism And Media Communication</li>
                        
                    </ul>
                </div>
                <div class="useful-links">
                    <h3>Useful Links</h3>
                    <ul>
                       
                        <li>Forms</li>
                    </ul>
                </div>
                <div class="get-in-touch">
                    <h3>Get in touch</h3>
                    <ul>
                        <li>Contact Us</li>
                        <li>Campus Map</li>
                        <li>Photo Gallery</li>
                    </ul>
                </div>
                <div class="social-links">
                    <h3>Follow Us</h3>
                    <ul>
                        <li><a href="https://www.facebook.com/greenuniversitybd/">Facebook</a></li>
                        <li><a href="https://x.com/i/flow/login?redirect_after_login=%2Fgreenvarsity">Twitter</a></li>
                        <li><a href="https://www.linkedin.com/school/greenuniversity">LinkedIn</a></li>
                        
                </div>
            </div>
            <p>&copy; 2003-2024 Green University of Bangladesh. All Rights Reserved.</p>
            <p>&copy; Developed by TM group & corporation</p>
        </footer>
    

    <!-- Schedule Bus Section -->
    <div id="schedule-bus-section"  style="display: none;">
        <header>
            <div class="logo">
                <img src="gub.jpg" style="border-radius: 5px;" alt="Company Logo">
                <span class="brand-name">Green University of Bangladesh</span>
            </div>
            <nav>
                
            </nav>
        </header>
        <section id="schedule-body">
    <div id="arrival-section">
        <h3>Choose your route</h3>
        <select id="arrival-route">
            <option value="Shewrapara">Shewrapara</option>
            <option value="Jatrabari">Jatrabari</option>
            <option value="Azimpur">Azimpur</option>
            <option value="Shonirakhra">Shonirakhra</option>
            <option value="Chashara">Chashara</option>
            <option value="Mohakhali">Mohakhali</option>
            <option value="Gulistan">Gulistan</option>
            <option value="Uttara">Uttara</option>
            <option value="Badda">Badda</option>
            <option value="Mohammadpur">Mohammadpur</option>
            <option value="Mirpur-1">Mirpur-1</option>
            <option value="Gazipur">Gazipur</option>
            <option value="Gausia">Gausia</option>
        </select>
        <h3>Choose your time slot of arrival</h3>
        <select id="arrival-time">
            <option value="7:00 AM">7:00 AM</option>
            <option value="8:00 AM">8:00 AM</option>
            <option value="9:00 AM">9:00 AM</option>
        </select>
        <button id="confirm-arrival">Confirm Arrival</button>
    </div>

    <div id="departure-section" style="display: none;">
        <h3>Choose your route</h3>
        <select id="departure-route">
        <option value="Shewrapara">Shewrapara</option>
            <option value="Jatrabari">Jatrabari</option>
            <option value="Azimpur">Azimpur</option>
            <option value="Shonirakhra">Shonirakhra</option>
            <option value="Chashara">Chashara</option>
            <option value="Mohakhali">Mohakhali</option>
            <option value="Gulistan">Gulistan</option>
            <option value="Uttara">Uttara</option>
            <option value="Badda">Badda</option>
            <option value="Mohammadpur">Mohammadpur</option>
            <option value="Mirpur-1">Mirpur-1</option>
            <option value="Gazipur">Gazipur</option>
            <option value="Gausia">Gausia</option>
        </select>
        <h3>Choose your time slot of departure</h3>
        <select id="departure-time">
            <option value="1:40 PM">1:40 PM</option>
            <option value="4:15 PM">4:15 PM</option>
            <option value="3:15 PM">3:15 PM</option>
        </select>
        <button id="confirm-departure">Confirm Departure</button>
    </div>
</section>




    <script src="bush.js"></script>
</body>
</html>

