<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>เข้าสู่ระบบ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/1b13c5849c.js" crossorigin="anonymous"></script>
    <style>
        .shake {
            animation: shake 0.3s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-3px);
            }

            75% {
                transform: translateX(3px);
            }
        }
    </style>
</head>

<body class="bg-teal-200 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="bg-white p-8 rounded-xl shadow-xl">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-16 h-16  rounded-full mx-auto mb-4 flex items-center justify-center overflow-hidden">
                    <img src="{{ asset('assets/img/icon-removebg.png') }}" alt=""
                        class="w-full h-full object-cover" />
                </div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-1">เข้าสู่ระบบ</h2>
                <p class="text-gray-600 text-sm">ทะเบียนนัด EKG-ECHO</p>
            </div>

            <!-- แสดงข้อความสำเร็จ -->
            @if (session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7">
                            </path>
                        </svg>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            <!-- แสดงข้อผิดพลาด -->
            @if ($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg shake">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                        <div>
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4" id="loginForm">
                @csrf

                <!-- ชื่อผู้ใช้ -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อผู้ใช้</label>
                    <input type="text" name="username" value="{{ old('username') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('username') border-red-500 @enderror"
                        autofocus autocomplete="username" />
                    @error('username')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- รหัสผ่าน -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">รหัสผ่าน</label>
                    <div class="relative">
                        <input type="password" name="password" value="{{ $errors->any() ? old('password') : '' }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                            autocomplete="current-password" id="passwordInput" />
                        <button type="button"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center justify-center h-full text-gray-400 hover:text-gray-600"
                            onclick="togglePassword()" tabindex="-1">
                            <i id="eyeIcon" class="fa-solid fa-eye w-5 h-5"></i>
                        </button>
                    </div>
                </div>

                <!-- จำฉันไว้ และ ลืมรหัสผ่าน -->
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        ลืมรหัสผ่าน? ติดต่อศูนย์คอมฯ 2079, 2081
                    </div>
                </div>

                <!-- ปุ่มเข้าสู่ระบบ -->
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition duration-200">
                    เข้าสู่ระบบ
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-6 text-gray-500 text-sm">
            <p>© 2025 พัฒนาโดย นายวชิรวิทย์ กุลสุทธิชัย </p>
        </div>
    </div>

    <script>
        // Toggle password visibility with FontAwesome icon
        function togglePassword() {
            const passwordInput = document.getElementById('passwordInput');
            const eyeIcon = document.getElementById('eyeIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }

        // Enter key handling
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('loginForm').submit();
            }
        });
    </script>
</body>

</html>
