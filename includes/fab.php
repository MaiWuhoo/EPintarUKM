 <!-- FAB Menu -->
<div x-data="{ open: false }" class="fixed bottom-6 right-6 z-50" x-cloak>
  <!-- Sub buttons -->
  <div x-show="open" class="space-y-3 mb-4" x-transition>
    <a href='../Pengurus/leaderboard.php' title='Leaderboard'
       class='flex items-center justify-center w-12 h-12 bg-pink-500 text-white rounded-full hover:bg-pink-600'>
      <i class='fas fa-ranking-star text-lg'></i>
    </a>
     <a href='../Penguguman/pengumumanlist.php' title='Tambah Pengumuman'
       class='flex items-center justify-center w-12 h-12 bg-yellow-500 text-white rounded-full hover:bg-yellow-600'>
      <i class='fa-solid fa-bullhorn text-lg'></i>
    </a>
    <a href='../aktiviti/aktivitiForm.php' title='Tambah Aktiviti'
       class='flex items-center justify-center w-12 h-12 bg-green-500 text-white rounded-full hover:bg-green-600'>
      <i class='fas fa-calendar-alt text-lg'></i>
    </a>
  </div>

  <!-- Main FAB button -->
  <button @click="open = !open"
          class="w-14 h-14 bg-blue-400 text-white rounded-full shadow-lg hover:bg-blue-700 transition transform hover:rotate-90"
          title="Tambah">
    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
    </svg>
  </button>
</div>