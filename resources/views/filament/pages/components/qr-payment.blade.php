<div class="text-center">
    <div class="bg-white p-4 rounded-xl shadow-sm border inline-block">
        <img src="{{ $qrUrl }}" alt="VietQR" class="max-w-[300px] mx-auto rounded-lg">
    </div>
    
    <div class="mt-4 space-y-2">
        <p class="text-gray-600">Amount: <strong class="text-xl text-primary-600">{{ $amount }} đ</strong></p>
        <p class="text-gray-600">Content: <strong class="bg-gray-100 px-2 py-1 rounded select-all">{{ $content }}</strong></p>
    </div>

    <p class="mt-4 text-sm text-gray-400 italic">
        *Please keep the transaction content. System will record automatically.
    </p>
</div>