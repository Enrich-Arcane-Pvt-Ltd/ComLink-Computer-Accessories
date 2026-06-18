<div x-data="{
    images: @js($getState()) || [],
    activeImage: null,
    activeIndex: 0,
    isOpen: false,

    init() {
        if (this.images.length > 0) {
            this.activeImage = this.images[0];
        }
    },

    openGallery(index) {
        this.activeIndex = index;
        this.activeImage = this.images[index];
        this.isOpen = true;
    },

    closeGallery() {
        this.isOpen = false;
    },

    nextImage() {
        if (this.activeIndex < this.images.length - 1) {
            this.activeIndex++;
        } else {
            this.activeIndex = 0;
        }
        this.activeImage = this.images[this.activeIndex];
    },

    prevImage() {
        if (this.activeIndex > 0) {
            this.activeIndex--;
        } else {
            this.activeIndex = this.images.length - 1;
        }
        this.activeImage = this.images[this.activeIndex];
    }
}">
    <div x-show="images.length > 0" @click="openGallery(0)"
        class="relative group cursor-pointer overflow-hidden rounded-xl border border-gray-200 shadow-sm bg-gray-100"
        style="height: 250px;">
        <template x-if="images.length > 0">
            <img :src="'/storage/' + images[0]"
                class="w-full h-full object-contain bg-white group-hover:scale-105 transition-transform duration-500">
        </template>

        <div class="absolute inset-0 bg-black/10 group-hover:bg-black/20 transition flex items-center justify-center">
            <div
                class="bg-white/90 backdrop-blur-md px-5 py-2 rounded-full shadow-lg flex items-center gap-2 transform group-hover:scale-110 transition-transform">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                    class="w-5 h-5 text-gray-700">
                    <path fill-rule="evenodd"
                        d="M1.5 6a2.25 2.25 0 012.25-2.25h16.5A2.25 2.25 0 0122.5 6v12a2.25 2.25 0 01-2.25 2.25H3.75A2.25 2.25 0 011.5 18V6zM3 16.06V18c0 .414.336.75.75.75h16.5A.75.75 0 0021 18v-1.94l-2.69-2.689a1.5 1.5 0 00-2.12 0l-.88.879.97.97a.75.75 0 11-1.06 1.06l-5.16-5.159a1.5 1.5 0 00-2.12 0L3 16.061zm10.125-7.81a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0z"
                        clip-rule="evenodd" />
                </svg>
                <span class="font-bold text-gray-800 text-sm">See All Photos</span>
                <template x-if="images.length > 1">
                    <span class="bg-gray-900 text-white text-xs px-2 py-0.5 rounded-full"
                        x-text="'+' + (images.length - 1)"></span>
                </template>
            </div>
        </div>
    </div>

    <template x-teleport="body">
        <div x-show="isOpen" x-cloak x-transition.opacity.duration.300ms @keydown.escape.window="closeGallery()"
            class="fixed inset-0 z-[99999] flex items-center justify-center bg-black/95 backdrop-blur-sm">
            <div class="absolute inset-0 cursor-pointer" @click="closeGallery()"></div>

            <button @click.stop="closeGallery()"
                class="absolute top-5 right-5 z-50 p-2 bg-white/10 rounded-full text-white hover:bg-white/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <button @click.stop="prevImage()"
                class="absolute left-4 md:left-10 z-50 p-3 bg-white/10 rounded-full text-white hover:bg-white/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <div class="relative z-40 max-w-[90vw] max-h-[85vh] flex flex-col items-center" @click.stop>
                <img :src="'/storage/' + activeImage" class="max-w-full max-h-[80vh] object-contain rounded shadow-2xl">

                <div class="mt-4 bg-white/10 px-4 py-1 rounded-full text-white text-sm border border-white/20">
                    <span x-text="activeIndex + 1"></span> / <span x-text="images.length"></span>
                </div>
            </div>

            <button @click.stop="nextImage()"
                class="absolute right-4 md:right-10 z-50 p-3 bg-white/10 rounded-full text-white hover:bg-white/20 transition">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                    stroke="currentColor" class="w-8 h-8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                </svg>
            </button>

            <div class="absolute bottom-0 w-full p-4 flex justify-center gap-2 overflow-x-auto z-50" @click.stop>
                <template x-for="(image, index) in images" :key="index">
                    <div @click="openGallery(index)"
                        class="h-16 w-16 flex-shrink-0 cursor-pointer rounded-lg border-2 overflow-hidden transition-all duration-200"
                        :class="activeIndex === index ? 'border-yellow-400 opacity-100 scale-110' :
                            'border-transparent opacity-50 hover:opacity-100'">
                        <img :src="'/storage/' + image" class="w-full h-full object-cover">
                    </div>
                </template>
            </div>
        </div>
    </template>
</div>
