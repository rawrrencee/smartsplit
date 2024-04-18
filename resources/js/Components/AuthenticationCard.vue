<template>
    <div class="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0 dark:bg-gray-800">
        <div>
            <slot name="logo" />
        </div>

        <div
            class="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg dark:bg-gray-700 dark:shadow-none"
        >
            <slot />
        </div>
    </div>
</template>
