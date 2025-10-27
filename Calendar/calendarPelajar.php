<div class="w-full lg:w-full bg-white p-4 rounded-xl shadow mt-6 lg:mt-0 max-h-[320px] overflow-hidden"
     x-data="{ ...calendarComponent(), showModal: false }" x-init="init()">

    <!-- Header bulan -->
    <div class="flex items-center justify-between mb-2">
        <button @click="prevMonth" class="text-sm px-2">←</button>
        <h3 class="text-base font-semibold text-gray-700 text-center"
            x-text="new Date(tahun, bulan - 1).toLocaleString('default', { month: 'long', year: 'numeric' })">
        </h3>
        <button @click="nextMonth" class="text-sm px-2">→</button>
    </div>

    <!-- Label hari -->
    <div class="grid grid-cols-7 text-center text-xs text-gray-600 mb-1">
        <div>M</div><div>T</div><div>W</div><div>T</div><div>F</div><div>S</div><div>A</div>
    </div>

    <!-- Grid tarikh -->
    <div class="grid grid-cols-7 text-center text-xs gap-y-1">
        <template x-for="(day, index) in days" :key="index">
            <div class="p-1 cursor-pointer"
                 @click="selected = day.details; if (day.details.length) showModal = true">
                <div class="w-6 h-6 mx-auto flex items-center justify-center rounded-full"
                     :class="{
                         'bg-purple-300 text-black font-bold': day.isToday,
                         'bg-blue-200 text-blue-800 font-semibold': !day.isToday && day.details.length
                     }">
                    <span x-text="day.label"></span>
                </div>
                <template x-if="day.dot">
                    <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mx-auto mt-0.5"></div>
                </template>
            </div>
        </template>
    </div>

    <!-- Modal Popup Aktiviti -->
    <template x-if="showModal">
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white w-[90%] max-w-md max-h-[80vh] p-4 rounded-xl shadow-lg overflow-y-auto">
                <h2 class="text-lg font-semibold text-center mb-3">Senarai Aktiviti</h2>

                <template x-for="(act, i) in selected" :key="i">
                    <div class="bg-blue-50 rounded p-3 mb-3 shadow text-sm">
                        <p class="font-semibold text-base mb-1" x-text="act.nama"></p>
                        <p class="text-gray-700 text-sm">⏰ <span x-text="act.masa"></span></p>
                        <p class="text-gray-700 text-sm">🏛️ <span x-text="act.persatuan"></span></p>
                    </div>
                </template>

                <div class="text-center mt-4">
                    <button @click="showModal = false" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Tutup</button>
                </div>
            </div>
        </div>
    </template>
</div>
