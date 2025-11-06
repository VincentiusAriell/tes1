<!-- Tambahkan SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.tailwindcss.com"></script>
<div class="max-w-2xl mx-auto px-4 py-10">

  <?php if (isset($_GET['error'])): ?>
  <script>
    Swal.fire({
      icon: 'error',
      title: 'Gagal Daftar',
      text: '<?php echo htmlspecialchars($_GET['error'], ENT_QUOTES); ?>',
      confirmButtonColor: '#6366f1',
      confirmButtonText: 'OK'
    });
  </script>
  <?php endif; ?>

  <?php if (isset($_GET['success'])): ?>
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Akun kamu berhasil didaftarkan. Silakan login.',
      confirmButtonColor: '#6366f1',
      confirmButtonText: 'Login Sekarang'
    }).then(() => {
      window.location.href = 'login.php';
    });
  </script>
  <?php endif; ?>

  <form action="../app/controllers/register.php" method="POST" class="bg-white p-6 rounded-xl shadow-lg border border-indigo-300 space-y-4">
    <h2 class="text-2xl font-semibold mb-2">Daftar Akun</h2>
    <div>
      <label for="name" class="block text-sm font-medium text-gray-700">Nama lengkap</label>
      <input id="name" name="name" type="text" required class="mt-1 block w-full px-3 py-2 border rounded" />
    </div>

    <div>
      <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
      <input id="email" name="email" type="email" required class="mt-1 block w-full px-3 py-2 border rounded" />
    </div>

    <div>
      <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
      <input id="password" name="password" type="password" required minlength="6" class="mt-1 block w-full px-3 py-2 border rounded" />
      <p class="text-xs text-gray-400 mt-1">Minimal 6 karakter. Password akan disimpan sebagai hash (server-side).</p>
    </div>

    <div class="flex items-center gap-3">
      <button type="submit" class="bg-indigo-400 hover:bg-indigo-500 text-white px-4 py-2 rounded">Daftar</button>
      <a href="login.php" class="text-sm text-indigo-400 hover:text-indigo-500">Sudah punya akun? Login</a>
    </div>
  </form>
</div>

