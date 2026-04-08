<?php $this->layout('layout') ?>

<?php $this->start('content') ?>

<div class="max-w-md mx-auto bg-white rounded-xl shadow-md overflow-hidden md:max-w-2xl">
    <div class="md:flex">
        <div class="md:flex-shrink-0">
            <img src="<?php echo BASE_URL; ?>/images/image.jpg" alt="Image" class="w-full h-48 md:h-full md:w-48">
        </div>
        <div class="p-8">
            <div class="uppercase tracking-wide text-sm text-indigo-500 font-semibold"><?php echo $title; ?></div>
            <a href="#" class="block mt-1 text-lg leading-tight font-medium text-black hover:underline"><?php echo $description; ?></a>
            <p class="mt-2 text-gray-600">This is the homepage of our website.</p>
        </div>
    </div>
</div>

<?php $this->end('content') ?>