<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UniLinker - Educational Mobile App</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #8e44ad;
            /* Replaces #10b981 (main purple) */
            --secondary: #f39c12;
            /* Replaces #3b82f6 (orange) */
            --accent: #e74c3c;
            /* Replaces #06d6a0 (red) */
            --dark: #2c3e50;
            /* Replaces #1f2937 (black) */
            --light: #eee;
            /* Replaces #f8fafc (light background) */
            --gray: #888;
            /* Replaces #64748b (light text) */
            --white: #fff;
            /* Same as #ffffff */
            --border: 0.1rem solid rgba(0, 0, 0, 0.2);
            /* Additional */
        }


        [data-theme="dark"] {
            --light: #1f2937;
            --white: #111827;
            --dark: #f8fafc;
            --gray: #e2e8f0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--light);
            color: var(--dark);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Mobile-First Navigation */
        .nav {
            position: fixed;
            top: 0;
            width: 100%;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            z-index: 1000;
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
        }

        [data-theme="dark"] .nav {
            background: rgba(31, 41, 55, 0.95);
            border-bottom-color: #374151;
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .nav-menu {
            position: fixed;
            top: 0;
            right: -100%;
            width: 70%;
            height: 100vh;
            background: var(--white);
            padding: 2rem;
            transition: right 0.3s ease;
            z-index: 1001;
        }

        .nav-menu.active {
            right: 0;
        }

        .nav-menu ul {
            list-style: none;
            margin-top: 3rem;
        }

        .nav-menu li {
            margin: 1.5rem 0;
        }

        .nav-menu a {
            color: var(--dark);
            text-decoration: none;
            font-weight: 500;
            font-size: 1.1rem;
        }

        .nav-toggle {
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--dark);
            cursor: pointer;
            z-index: 1002;
            position: relative;
        }

        .theme-btn {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: var(--dark);
            cursor: pointer;
            margin-right: 1rem;
        }

        /* Hero Section - Mobile Optimized */
        .hero {
            padding: 6rem 1rem 3rem;
            text-align: center;
            background: linear-gradient(135deg, #ecfdf5, #dbeafe);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .hero-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .hero h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .hero .highlight {
            color: var(--primary);
        }

        .hero p {
            font-size: 1rem;
            color: var(--gray);
            margin-bottom: 2rem;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .cta-btn {
            display: inline-block;
            background: linear-gradient(135deg, #cc89e8, #8e44ad);
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            box-shadow: 0 10px 30px rgba(142, 68, 173, 0.3);
            /* Soft shadow with #8e44ad tint */
            transition: transform 0.3s ease;
        }

        .cta-btn:hover {
            transform: translateY(-3px);
        }


        .hero-visual {
            margin-top: 3rem;
            position: relative;
        }
        


        /* Mobile specific adjustments */
        @media (max-width: 480px) {
            .phone-mockup {
                width: 260px;
                height: 480px;
                max-width: 85vw;
                max-height: 60vh;
            }

            .phone-screen {
                background-size: contain;
                background-position: center center;
            }
        }

        .floating-features {
            position: absolute;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        .feature-bubble {
            position: absolute;
            background: var(--white);
            padding: 0.5rem 1rem;
            border-radius: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            font-size: 0.8rem;
            font-weight: 500;
            animation: float 3s ease-in-out infinite;
        }

        .feature-bubble:nth-child(1) {
            top: 10%;
            left: -10%;
            animation-delay: 0s;
        }

        .feature-bubble:nth-child(2) {
            top: 20%;
            right: -15%;
            animation-delay: 1s;
        }

        .feature-bubble:nth-child(3) {
            bottom: 30%;
            left: -15%;
            animation-delay: 2s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-15px);
            }
        }

        /* About Section */
        .about {
            padding: 3rem 1rem;
            background: var(--white);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .section-subtitle {
            text-align: center;
            color: var(--gray);
            margin-bottom: 2rem;
        }

        .about-content {
            font-size: 1rem;
            color: var(--gray);
            margin-bottom: 2rem;
            text-align: center;
        }

        .features-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
            margin-top: 2rem;
        }

        .feature-item {
            background: var(--light);
            padding: 1.5rem;
            border-radius: 15px;
            border-left: 4px solid var(--primary);
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .feature-item i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        .feature-item h4 {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 0.5rem;
        }

        .feature-item p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Goals Section */
        .goals {
            padding: 3rem 1rem;
            background: var(--light);
        }

        .goals-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .goal-card {
            background: var(--white);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .goal-card:hover {
            transform: translateY(-5px);
        }

        .goal-card i {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .goal-card h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: var(--dark);
        }

        .goal-card p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Team Section */
        .team {
            padding: 3rem 1rem;
            background: var(--white);
        }

        .team-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        .team-card {
            background: var(--light);
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            transition: all 0.3s ease;
            animation: slideInUp 0.8s ease-out;
        }

        .team-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        }

        .team-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            overflow: hidden;
            border: 4px solid var(--primary);
            transition: transform 0.3s ease;
        }

        .team-card:hover .team-avatar {
            transform: scale(1.1);
        }

        .team-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .team-avatar.supervisor {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
        }

        .team-avatar.frontend {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        .team-avatar.backend {
            background: linear-gradient(135deg, #6c5ce7, #a29bfe);
        }

        .team-avatar.manager {
            background: linear-gradient(135deg, #fd79a8, #fdcb6e);
        }

        .team-card h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .team-card .role {
            color: var(--primary);
            font-weight: 500;
            margin-bottom: 1rem;
            font-size: 0.9rem;
        }

        .team-card .role.supervisor {
            color: #ff6b6b;
        }

        .team-card p {
            color: var(--gray);
            font-size: 0.9rem;
        }

        /* Footer */
        .footer {
            background:white;
            color: var(--light);
            padding: 2rem 1rem 1rem;
        }

        .footer-content {
            max-width: 1200px;
            margin: 0 auto;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .footer h3 {
            color: var(--primary);
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .footer p {
            margin-bottom: 1rem;
            color: var(--gray);
        }

        .footer-links {
            display: flex;
            justify-content: center;
            gap: 2rem;
            margin: 1rem 0;
            flex-wrap: wrap;
        }

        .footer-links a {
            color: var(--light);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--primary);
        }

        .footer-bottom {
            border-top: 1px solid #374151;
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 0.8rem;
            color: var(--gray);
            text-align: center;
            width: 100%;
        }

        .nav__btns .btn {
            padding: 0.75rem 1.5rem;
            outline: none;
            border: none;
            font-size: 1rem;
            white-space: nowrap;
            border-radius: 25px;
            transition: all 0.4s ease;
            cursor: pointer;
            background: linear-gradient(135deg, #cc89e8, #8e44ad);
            color: rgb(255, 255, 255);
            font-weight: 600;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(97, 154, 246, 0.3);
            transform: translateY(0);
        }

        .nav__btns .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s ease;
        }

        .nav__btns .btn:hover::before {
            left: 100%;
        }

        .nav__btns .btn:hover {
            background: linear-gradient(135deg, #cc89e8, #8e44ad);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(97, 154, 246, 0.4);
        }

        .nav__btns .btn:active {
            transform: translateY(0);
            box-shadow: 0 4px 15px rgba(97, 154, 246, 0.3);
        }

        /* Animations */
        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Tablet Styles */
        @media (min-width: 768px) {
            .nav-menu {
                position: static;
                width: auto;
                height: auto;
                background: none;
                padding: 0;
                right: 0;
            }

            .nav-menu ul {
                display: flex;
                gap: 2rem;
                margin: 0;
            }

            .nav-menu li {
                margin: 0;
            }

            .nav-toggle {
                display: none;
            }

            .hero h1 {
                font-size: 2.5rem;
            }

            .phone-mockup {
                width: 300px;
                height: 600px;
                max-width: none;
                max-height: none;
            }

            .phone-screen {
                font-size: 2.5rem;
                /* background-size: cover;   */
            }

            .features-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .goals-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .team-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Desktop Styles */
        @media (min-width: 1024px) {
            .hero {
                padding: 8rem 1rem 4rem;
            }

            .hero h1 {
                font-size: 3rem;
            }

            .hero-container {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 4rem;
                align-items: center;
            }

            .hero-content {
                text-align: left;
            }

            .hero p {
                margin-left: 0;
                margin-right: 0;
            }

            .phone-mockup {
                width: 320px;
                height: 640px;
            }

            .phone-screen {
                font-size: 3rem;
            }

            .about-content {
                text-align: left;
                max-width: 800px;
                margin: 0 auto 2rem;
            }

            .features-grid {
                grid-template-columns: repeat(3, 1fr);
            }

            .goals-grid {
                grid-template-columns: repeat(4, 1fr);
            }

            .team-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Extra large screens */
        @media (min-width: 1200px) {
            .phone-mockup {
                width: 350px;
                height: 700px;
            }

            .phone-screen {
                font-size: 3.5rem;
            }
        }
    </style>
</head>

<body>
    <nav class="nav">
        <div class="nav-container">
            <a href="#" class="logo">UniLinker</a>
            <div class="nav-actions">
                <button class="theme-btn" id="theme-btn">

                </button>
                <button class="nav-toggle" id="nav-toggle">

                </button>
            </div>
            <div class="nav__btns">
                <button class="btn sign__in" id="loginBtn">Login</button>
            </div>
        </div>

    </nav>


    <!-- Hero Section -->
    <section class="hero" id="home">
        <div class="hero-container">
            <div class="hero-content fade-in">
                <h1><span class="highlight">UniLinker</span><br>is Educational Mobile App</h1>
                <p>Connect students and teachers instantly. Share resources, collaborate on projects, and learn together
                    on any device.</p>

            </div>
            <div class="hero-visual fade-in">
               <img src="image/home screen (2).png" height="550px"/>
            



            </div>
        </div>
    </section>

    <!-- About Section -->
    <section class="about" id="about">
        <div class="container">
            <h2 class="section-title fade-in">About UniLinker</h2>
            <p class="section-subtitle fade-in">A mobile-first educational platform for universities</p>

            <div class="about-content fade-in">
                <p>UniLinker is a web-based mobile app designed specifically for university students and teachers.
                    Access your courses, assignments, and study materials from anywhere. Join study groups, chat with
                    classmates, and track your academic progress - all in one app.</p>
            </div>

            <div class="features-grid">
                <div class="feature-item fade-in">
                    <i class="fas fa-mobile-alt"></i>
                    <div>
                        <h4>Mobile-First Design</h4>
                        <p>Optimized for smartphones and laptop</p>
                    </div>
                </div>
                <div class="feature-item fade-in">
                    <i class="fas fa-users"></i>
                    <div>
                        <h4>Study Material</h4>
                        <p>To Search a content Related to This Field</p>
                    </div>
                </div>
                <div class="feature-item fade-in">
                    <i class="fas fa-comments"></i>
                    <div>
                        <h4>Real-time Chat</h4>
                        <p>Instant messaging with teachers and peers</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Goals Section -->
    <section class="goals" id="goals">
        <div class="container">
            <h2 class="section-title fade-in">Key Features</h2>
            <p class="section-subtitle fade-in">Everything you need for mobile learning</p>

            <div class="goals-grid">
                <div class="goal-card fade-in">
                    <i class="fas fa-graduation-cap"></i>
                    <h3>Course Management</h3>
                    <p>Organize all your courses and assignments in one place</p>
                </div>
                <div class="goal-card fade-in">
                    <i class="fas fa-share-alt"></i>
                    <h3>Easy Sharing</h3>
                    <p>Share notes, files, and resources instantly</p>
                </div>
                <div class="goal-card fade-in">
                    <i class="fas fa-wifi"></i>
                    <h3>Using mobile App And Web</h3>
                    <p>Your both Are using with help to internet connection</p>
                </div>
                <div class="goal-card fade-in">
                    <i class="fas fa-shield-alt"></i>
                    <h3>Secure Registration</h3>
                    <p>Your data is protected with enterprise security</p>
                </div>
            </div>
        </div>
    </section>


    <!-- Team Section -->
    <section class="team" id="team">
        <div class="container">
            <h2 class="section-title fade-in">Development Team</h2>

            <div class="team-grid">
                <div class="team-card fade-in">
                    <div class="team-avatar supervisor">
                        <img src="image/sir1.jpg"
                            onerror="this.parentElement.innerHTML='<i class=\'fas fa-user-tie\'></i>'">
                    </div>
                    <h3>Dr.Aijaz Ahmed Arain</h3>
                    <div class="role supervisor"> Project Supervisor</div>
                    <p>To Develop a UniLinker with the help of Great And most Respectable Supervisor</p>
                </div>
                <div class="team-card fade-in">
                    <div class="team-avatar frontend">
                        <img src="image/leader.webp" alt="Sarah Kim"
                            onerror="this.parentElement.innerHTML='<i class=\'fas fa-code\'></i>'">
                    </div>
                    <h3>Bisma Malik</h3>
                    <div class="role">21BCS79</div>
                    <p>Mobile UI/UX specialist focused on responsive design and user experience</p>
                </div>
                <div class="team-card fade-in">
                    <div class="team-avatar backend">
                        <img src="image/naeempic.jpg" alt="Alex Chen"
                            onerror="this.parentElement.innerHTML='<i class=\'fas fa-server\'></i>'">
                    </div>
                    <h3>Naeem Ahmed ghouri</h3>
                    <div class="role">21BCS60</div>
                    <p>Backend Developer and database optimization for coding performance</p>
                </div>
                <div class="team-card fade-in">
                    <div class="team-avatar manager">
                        <img src="image/shahpic.jpg" alt="Mike Johnson"
                            onerror="this.parentElement.innerHTML='<i class=\'fas fa-tasks\'></i>'">
                    </div>
                    <h3>Shahzaib</h3>
                    <div class="role">21BCS92</div>
                    <p>Expert in frontend end Developer Work On projects Documentation </p>
                </div>
            </div>
        </div>
    </section>



    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div>
                <h3>UniLinker</h3>
                <p>Mobile Educational App</p>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 UniLinker. Most Userfriendly App.</p>
            </div>
        </div>
    </footer>

    <script>
         document.getElementById("loginBtn").addEventListener("click", function () {
        window.location.href = "login1.php";
    });
        // Mobile Navigation
        const navToggle = document.getElementById('nav-toggle');
        const navMenu = document.getElementById('nav-menu');

        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
            const icon = navToggle.querySelector('i');
            icon.classList.toggle('fa-bars');
            icon.classList.toggle('fa-times');
        });

        // Close menu when clicking on links
        document.querySelectorAll('.nav-menu a').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                const icon = navToggle.querySelector('i');
                icon.classList.add('fa-bars');
                icon.classList.remove('fa-times');
            });
        });

        // Theme Toggle
        const themeBtn = document.getElementById('theme-btn');
        const body = document.body;

        themeBtn.addEventListener('click', () => {
            const isDark = body.getAttribute('data-theme') === 'dark';
            body.setAttribute('data-theme', isDark ? 'light' : 'dark');

            const icon = themeBtn.querySelector('i');
            icon.classList.toggle('fa-moon');
            icon.classList.toggle('fa-sun');

            // Note: localStorage is not used in Claude.ai artifacts
        });

        // Smooth scrolling
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    const offsetTop = target.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Scroll animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.fade-in').forEach(el => {
            observer.observe(el);
        });

        // Dynamic navbar background
        window.addEventListener('scroll', () => {
            const nav = document.querySelector('.nav');
            if (window.scrollY > 100) {
                nav.style.background = 'rgba(255, 255, 255, 0.98)';
                nav.style.boxShadow = '0 2px 20px rgba(0, 0, 0, 0.1)';
            } else {
                nav.style.background = 'rgba(255, 255, 255, 0.95)';
                nav.style.boxShadow = 'none';
            }
        });

        // Additional animation for team cards
        const teamCards = document.querySelectorAll('.team-card');
        teamCards.forEach((card, index) => {
            card.style.animationDelay = `${index * 0.2}s`;
        });
    </script>
</body>f

</html>