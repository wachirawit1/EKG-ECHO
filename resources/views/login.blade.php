<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>เข้าสู่ระบบ</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="w-full max-w-sm p-6 bg-white rounded-2xl shadow-md">
        <h2 class="text-2xl font-semibold text-center mb-6 text-gray-800">เข้าสู่ระบบ</h2>
        <form action="/login" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm text-gray-700">ชื่อผู้ใช้</label>
                <input type="text" name="username" required
                    class="w-full px-4 py-2 mt-1 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div>
                <label class="block text-sm text-gray-700">รหัสผ่าน</label>
                <input type="password" name="password" required
                    class="w-full px-4 py-2 mt-1 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
            </div>
            <div class="flex items-center justify-between">
                <label class="flex items-center text-sm">
                    <input type="checkbox" class="mr-2" /> จำฉันไว้
                </label>
                <a href="#" class="text-sm text-blue-600 hover:underline">ลืมรหัสผ่าน?</a>
            </div>
            <button type="submit"
                class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition duration-200">
                เข้าสู่ระบบ
            </button>
        </form>
    </div>
</body>

</html>
