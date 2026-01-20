<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="">
    <title>Portfolio Hub - แหล่งรวมเว็บไซต์</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            min-height: 100vh;
            color: #333;
        }

        /* Header */
        .header {
            text-align: center;
            padding: 60px 2rem 40px;
            color: white;
        }

        .header h1 {
            font-size: 2.8rem;
            margin-bottom: 1rem;
            color: white;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }

        .header p {
            font-size: 1.1rem;
            margin-bottom: 1rem;
            opacity: 0.9;
        }

        .header .subtitle {
            font-size: 0.95rem;
            opacity: 0.7;
            font-style: italic;
        }

        /* Main Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        /* Website Grid */
        .websites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            margin: 40px 0;
        }

        .website-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
        }

        .website-card:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .card-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            display: block;
            text-align: center;
        }

        .card-title {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 1rem;
            text-align: center;
        }

        .card-description {
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .card-tech {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .tech-tag {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .card-status {
            text-align: center;
            font-weight: 600;
            padding: 0.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .status-active {
            background: rgba(34, 197, 94, 0.2);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .status-development {
            background: rgba(251, 191, 36, 0.2);
            color: #fbbf24;
            border: 1px solid rgba(251, 191, 36, 0.3);
        }

        .status-planning {
            background: rgba(156, 163, 175, 0.2);
            color: #9ca3af;
            border: 1px solid rgba(156, 163, 175, 0.3);
        }

        .card-button {
            width: 100%;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .card-button:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .card-button:disabled {
            background: rgba(156, 163, 175, 0.5);
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Featured Card (EKG-ECHO) */
        .featured-card {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.1), rgba(16, 185, 129, 0.1));
            border: 2px solid rgba(34, 197, 94, 0.3);
            position: relative;
        }

        /* Coming Soon Cards */
        .coming-soon {
            opacity: 0.7;
        }

        .coming-soon:hover {
            transform: none;
            cursor: default;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: none;
        }

        .featured-card::after {
            content: '⭐ FEATURED';
            position: absolute;
            top: -1px;
            right: 20px;
            background: linear-gradient(45deg, #10b981, #059669);
            color: white;
            padding: 0.3rem 1rem;
            border-radius: 0 0 10px 10px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        /* Developer Credit Section */
        .developer-section {
            margin: 60px auto 40px;
            text-align: center;
            padding: 30px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 15px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .developer-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(45deg, #2563eb, #1d4ed8);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
            transition: transform 0.3s ease;
        }

        .developer-avatar:hover {
            transform: scale(1.05);
        }

        .developer-info h2 {
            color: white;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .developer-info p {
            color: rgba(255, 255, 255, 0.8);
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .developer-skills {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            justify-content: center;
            margin-top: 1.5rem;
        }

        .skill-tag {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .skill-tag:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
        }

        /* Footer */
        .footer {
            text-align: center;
            padding: 40px 2rem;
            color: rgba(255, 255, 255, 0.7);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }



        /* Responsive */
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.5rem;
            }

            .websites-grid {
                grid-template-columns: 1fr;
            }

            .developer-skills {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <h1>ยินดีต้อนรับเข้าสู่เว็บไซต์</h1>
        <p>แหล่งรวบรวมเว็บไซต์ของผู้จัดทำ</p>
    </header>

    <div class="container">
        <!-- Websites Grid -->
        <div class="websites-grid">
            <!-- EKG-ECHO Featured -->
            <div class="website-card featured-card" onclick="openWebsite('ekg-echo', event)">
                <div class="card-icon"
                    style="width:64px;height:64px;margin:0 auto 1rem;display:flex;align-items:center;justify-content:center;background:#2563eb;border-radius:50%;overflow:hidden;">
                    <img src="{{ asset('assets/img/icon-removebg.png') }}" alt=""
                        style="width:100%;height:100%;object-fit:cover;display:block;" />
                </div>
                <h3 class="card-title">EKG-ECHO</h3>
                <p class="card-description">
                    ระบบทะเบียนนัดหมาย EKG-ECHO
                </p>
                <div class="card-tech">
                    <span class="tech-tag">Laravel</span>
                    <span class="tech-tag">PHP</span>
                    <span class="tech-tag">CSS</span>
                    <span class="tech-tag">Javascript</span>
                </div>
                <div class="card-status status-active">🟢 พร้อมใช้งาน</div>
                <button class="card-button">เข้าชมเว็บไซต์</button>
            </div>

            <!-- Portfolio Website -->
            <div class="website-card coming-soon">
                <div class="card-icon">👨‍💻</div>
                <h3 class="card-title">Personal Portfolio</h3>
                <p class="card-description">
                    เว็บไซต์แสดงผลงานและประสบการณ์การทำงาน
                    พร้อมด้วยโปรเจกต์ต่างๆ ที่น่าสนใจ
                </p>
                <div class="card-status status-planning">⚪ เร็วๆ นี้</div>
                <button class="card-button" disabled>Coming Soon</button>
            </div>

            <!-- E-Commerce Platform -->
            <div class="website-card" onclick="openWebsite('pm-search', event)">
                <div class="card-icon"><i class="fa-solid fa-hospital-user" style="color: #fafcff;"></i></div>
                <h3 class="card-title">PM Search</h3>
                <p class="card-description">
                    ค้นหาผู้ใช้ PM ในระบบ
                </p>
                <div class="card-tech">
                    <span class="tech-tag">Laravel</span>
                    <span class="tech-tag">PHP</span>
                    <span class="tech-tag">CSS</span>
                    <span class="tech-tag">Jquery</span>
                </div>

                <div class="card-status status-active">🟢 พร้อมใช้งาน</div>
                <button class="card-button">เข้าชมเว็บไซต์</button>
            </div>

            <!-- Blog Platform -->
            <div class="website-card featured-card" onclick="openWebsite('intranet', event)">
                <div class="card-icon">📝</div>
                <h3 class="card-title">BRH Intranet</h3>
                <p class="card-description">
                    Intranet สำหรับการสื่อสารภายในโรงพยาบาล
                </p>
                <div class="card-status status-active">🟢 พร้อมใช้งาน</div>
                <button class="card-button">เข้าชมเว็บไซต์</button>
            </div>

            <!-- API Documentation -->
            <div class="website-card coming-soon">
                <div class="card-icon">📚</div>
                <h3 class="card-title">API Documentation</h3>
                <p class="card-description">
                    เอกสาร API ที่ครบถ้วนและง่ายต่อการใช้งาน
                    สำหรับนักพัฒนาที่ต้องการเชื่อมต่อ
                </p>
                <div class="card-status status-planning">⚪ เร็วๆ นี้</div>
                <button class="card-button" disabled>Coming Soon</button>
            </div>

            <!-- Coming Soon Project -->
            <div class="website-card coming-soon">
                <div class="card-icon">🤖</div>
                <h3 class="card-title">AI Assistant</h3>
                <p class="card-description">
                    ผู้ช่วยอัจฉริยะด้วย AI ที่จะช่วยในการทำงาน
                    และตอบคำถามต่างๆ แบบอัตโนมัติ
                </p>
                <div class="card-status status-planning">⚪ เร็วๆ นี้</div>
                <button class="card-button" disabled>Coming Soon</button>
            </div>
        </div>

        <!-- Developer Credit Section -->
        <section class="developer-section">
            <div class="developer-avatar" style="overflow:hidden; padding:0;">
                <img src="{{ asset('assets/img/profile.jpg') }}" alt=""
                    style="width:100px; height:100px; object-fit:cover; border-radius:50%; display:block;">
            </div>
            <div class="developer-info">
                <h2>พัฒนาโดย</h2>
                <p>นาย วชิรวิทย์ กุลสุทธิชัย</p>
                <p>Web Developer Beginner</p>
                <p>ผู้สร้างและพัฒนาเว็บไซต์และแอปพลิเคชันทั้งหมดในคอลเลกชันนี้</p>

                <div class="developer-skills">
                    <span class="skill-tag">🐍 Python</span>
                    <span class="skill-tag">⚛️ React</span>
                    <span class="skill-tag">🟢 Node.js</span>
                    <span class="skill-tag">🧠 Machine Learning</span>
                    <span class="skill-tag">☁️ Cloud Computing</span>
                    <span class="skill-tag">🗄️ Database Design</span>
                    <span class="skill-tag">🎨 UI/UX Design</span>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2025 Portfolio Hub. Created with ❤️ and lots of ☕</p>
    </footer>
    <script src="https://kit.fontawesome.com/1b13c5849c.js" crossorigin="anonymous"></script>
    <script>
        function openWebsite(projectName, event) {
            // Add click animation
            event.target.closest('.website-card').style.transform = 'scale(0.98)';

            setTimeout(() => {
                // Reset animation
                event.target.closest('.website-card').style.transform = '';

                // Handle navigation based on project
                if (projectName === 'ekg-echo') {
                    // สำหรับ EKG-ECHO
                    window.location.href = "{{ route('index') }}"
                } else if (projectName === 'pm-search') {
                    // สำหรับ PM Search
                    window.location.href = "{{ route('pm_search') }}";
                } else if (projectName === 'intranet') {
                    window.open("http://192.168.10.10", '_blank');
                } else {
                    // สำหรับโปรเจกต์อื่นๆ ที่ยังไม่เปิดใช้งาน
                    alert('โปรเจกต์นี้ยังไม่เปิดให้เข้าชม');
                }
            }, 150);
        }
    </script>
</body>

</html>
