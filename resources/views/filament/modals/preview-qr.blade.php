<div class="flex flex-col items-center justify-center p-8 bg-gray-50/50 rounded-2xl border border-gray-100 max-w-sm mx-auto">
    <div class="p-5 bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-gray-100 transition duration-300 ease-in-out hover:scale-105 hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)]">
        <div class="inline-block p-1 bg-gradient-to-tr from-gray-50 to-gray-100 rounded-xl">
            {!! $qrCode !!}
        </div>
    </div>
    
    <div class="mt-5 text-center">
        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-50 text-primary-700 mb-2 border border-primary-100">
            ID / Serial Number
        </span>
        <p class="text-sm font-semibold text-gray-700 font-mono tracking-wider break-all bg-gray-100/80 px-3 py-1.5 rounded-lg select-all">
            {{ $label }}
        </p>
    </div>
</div>